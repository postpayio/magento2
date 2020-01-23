<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Model\Order;
use Postpay\Exceptions\PostpayException;
use Postpay\Postpay\Exception\PostpayCheckoutApiException;
use Postpay\Postpay\Exception\PostpayCheckoutCartException;
use Postpay\Postpay\Exception\PostpayCheckoutOrderException;
use Postpay\Postpay\Exception\PostpayConfigurationException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteManagement;

/**
 * Class CheckoutManager
 * @package Postpay\Postpay\Model
 */
class CheckoutManager implements CheckoutManagerInterface
{
    /** @var Encryptor */
    private $encryptor;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var PostpayWrapperInterface
     */
    private $postpayWrapper;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * CheckoutManager constructor.
     * @param Encryptor $encryptor
     * @param ImageHelper $imageHelper
     * @param PostpayWrapperInterface $postpayWrapper
     * @param UrlInterface $url
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteManagement $quoteManagement
     * @param CustomerSession $customerSession
     * @param CheckoutHelper $checkoutHelper
     */
    public function __construct(
        Encryptor $encryptor,
        ImageHelper $imageHelper,
        PostpayWrapperInterface $postpayWrapper,
        UrlInterface $url,
        CartRepositoryInterface $cartRepository,
        QuoteManagement $quoteManagement,
        CustomerSession $customerSession,
        CheckoutHelper $checkoutHelper
    ) {
        $this->encryptor = $encryptor;
        $this->imageHelper = $imageHelper;
        $this->postpayWrapper = $postpayWrapper;
        $this->url = $url;
        $this->cartRepository = $cartRepository;
        $this->quoteManagement = $quoteManagement;
        $this->customerSession = $customerSession;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * @param Quote $quote
     * @return string
     * @throws CouldNotSaveException
     * @throws PostpayCheckoutOrderException
     */
    public function convert(Quote $quote): string
    {
        $orderId = $this->quoteManagement->placeOrder($quote->getId());

        if (!$orderId) {
            $errorMessage = __(
                'Failed to convert quote ID %1 to order.',
                $quote->getId()
            );
            throw new PostpayCheckoutOrderException($errorMessage);
        }

        return $this->url->getUrl(ConfigInterface::CHECKOUT_SUCCESS_ROUTE);
    }

    /**
     * @param Quote $quote
     * @return string
     * @throws PostpayCheckoutApiException
     * @throws PostpayCheckoutCartException
     * @throws PostpayException
     * @throws PostpayConfigurationException
     * @throws LocalizedException
     */
    public function create(Quote $quote): string
    {
        /** @var Item[] $quoteItems */
        $quoteItems = $quote->getAllVisibleItems();

        if(!$quoteItems) {
            $errorMessage = __(
                'Unable to create Postpay checkout since quote does not contain any items. Quote ID %1.',
                $quote->getId()
            );
            throw new PostpayCheckoutCartException($errorMessage);
        }

        if (!$quote->getCheckoutMethod()) {
            if ($this->customerSession->isLoggedIn()) {
                $quote->setCheckoutMethod(Onepage::METHOD_CUSTOMER);
            } else {
                if ($this->checkoutHelper->isAllowedGuestCheckout($quote)) {
                    $quote->setCheckoutMethod(Onepage::METHOD_GUEST);
                } else {
                    $quote->setCheckoutMethod(Onepage::METHOD_REGISTER);
                }
            }
        }

        $postpayOrderId = $this->generatePostpayOrderId($quote);

        /** @var Address $billingAddress */
        $billingAddress = $quote->getBillingAddress();

        $billingStreet = $billingAddress->getStreet();
        $billingState = $billingAddress->getRegionCode();
        if(!$billingState) {
            $billingState = $billingAddress->getCity();
        }

        $billingAddressEntity = [
            'first_name' => $billingAddress->getFirstname(),
            'last_name' => $billingAddress->getLastname(),
            'phone' => $billingAddress->getTelephone(),
            'line1' => $billingStreet[0],
            'city' => $billingAddress->getCity(),
            'state' => $billingState,
            'country' => $billingAddress->getCountryId(),
            'postal_code' => $billingAddress->getPostcode()
        ];

        if(isset($billingStreet[1])) {
            $billingAddressEntity['line2'] = $billingStreet[1];
        }

        /** @var Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();

        $shippingStreet = $shippingAddress->getStreet();
        $shippingState = $shippingAddress->getRegionCode();
        if(!$shippingState) {
            $shippingState = $shippingAddress->getCity();
        }
        $shippingAddressEntity = [
            'first_name' => $shippingAddress->getFirstname(),
            'last_name' => $shippingAddress->getLastname(),
            'phone' => $shippingAddress->getTelephone(),
            'line1' => $shippingStreet[0],
            'city' => $shippingAddress->getCity(),
            'state' => $shippingState,
            'country' => $shippingAddress->getCountryId(),
            'postal_code' => $shippingAddress->getPostcode()
        ];

        if(isset($shippingStreet[1])) {
            $shippingAddressEntity['line2'] = $shippingStreet[1];
        }

        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $this->cartRepository->save($quote);

        $shippingEntity = [
            'id' => $shippingAddress->getShippingMethod(),
            'name' => $shippingAddress->getShippingDescription(),
            'amount' => $this->formatDecimal($shippingAddress->getBaseShippingAmount()),
            'address' => $shippingAddressEntity,
        ];

        /** @var Customer $customer */
        $customer = $quote->getCustomer();
        $customerEntity = [
            'email' => $customer->getEmail() ?? $billingAddress->getEmail()
        ];

        $itemsEntity = [];

        $discountCode = null;
        $discountName = null;
        $discountAmount = 0;
        /** @var Item $quoteItem */
        foreach ($quoteItems as $quoteItem) {
            $itemsEntityItem = [];
            $quoteItemProduct = $quoteItem->getProduct();
            $itemsEntityItem['reference'] = $quoteItem->getSku();
            $itemsEntityItem['name'] = $quoteItem->getName();
            $itemsEntityItem['url'] = $quoteItemProduct->getProductUrl();
            $itemsEntityItem['image_url'] = $this->imageHelper
                ->init($quoteItemProduct, 'image')
                ->getUrl();
            $itemsEntityItem['unit_price'] = $this->formatDecimal($quoteItem->getBaseOriginalPrice());
            $itemsEntityItem['qty'] = $this->formatDecimal($quoteItem->getQty());
            $itemsEntity[] = $itemsEntityItem;

            $discountAmount += $quoteItem->getBaseDiscountAmount();
            $discountCode = $discountName = $quote->getCouponCode();
        }

        $merchantEntity = [
            /*
             * URL that the customer is sent to if they successfully complete the checkout process. The order_id and
             * status=APPROVED will be sent to this URL as a HTTP query parameter in order to capture the order.
             */
            'confirmation_url' => $this->url->getUrl(ConfigInterface::POSTPAY_CHECKOUT_CAPTURE_ROUTE),

            /**
             * URL that the customer is sent to if the payment process is cancelled. A status will be sent to this URL
             * as a HTTP query parameter, options are CANCELLED, DENIED.
             */
            'cancel_url' => $this->url->getUrl(ConfigInterface::POSTPAY_CHECKOUT_CANCEL_ROUTE),
        ];

        $payload = [
            /*
             * Unique order ID.
             */
            'order_id' => $postpayOrderId,
            'email' => $billingAddress->getEmail(),
            'total_amount' => $this->formatDecimal($quote->getBaseGrandTotal()),
            'tax_amount' => $this->formatDecimal($shippingAddress->getBaseTaxAmount()),
            'currency' => $quote->getBaseCurrencyCode(),
            'shipping' => $shippingEntity,
            'billing_address' => $billingAddressEntity,
            'customer' => $customerEntity,
            'items' => $itemsEntity,
            'merchant' => $merchantEntity
        ];

        if($discountAmount) {
            $discountEntity[] = [
                'code' => $discountCode,
                'name' => $discountName,
                'amount' => $this->formatDecimal($discountAmount)
            ];
            $payload['discounts'] = $discountEntity;
        }

        $response = $this->postpayWrapper->post('/checkouts', $payload);

        if(!in_array($response->getStatusCode(), [200, 201, 202])) {
            $errorMessage = __(
                'Postpay API request for creating checkout was not successful. Status code: %1. Quote ID %2.',
                $response->getStatusCode(),
                $quote->getId()
            );

            throw new PostpayCheckoutApiException($errorMessage);
        }

        $decodedResponse = $response->json();
        if(!$decodedResponse || !isset($decodedResponse['redirect_url'])) {
            $errorMessage = __(
                'Malformed Postpay API response while creating checkout. Quote ID %1.',
                $quote->getId()
            );

            throw new PostpayCheckoutApiException($errorMessage);
        }

        $postpayRedirectUrl = $decodedResponse['redirect_url'];

        /** @var Payment $payment */
        $payment = $quote->getPayment();
        $payment->setAdditionalInformation(ConfigInterface::POSTPAY_ORDER_ID_PAYMENT_INFO_KEY, $postpayOrderId);
        $payment->setAdditionalInformation(ConfigInterface::POSTPAY_REDIRECT_URL_PAYMENT_INFO_KEY, $postpayRedirectUrl);

        $this->cartRepository->save($quote);

        return $postpayRedirectUrl;
    }

    /**
     * @param Quote $quote
     * @return string
     */
    public function recover(Quote $quote): string
    {
        /** @var Payment $payment */
        $payment = $quote->getPayment();
        return (string)$payment->getAdditionalInformation(Config::POSTPAY_REDIRECT_URL_PAYMENT_INFO_KEY);
    }

    /**
     * Generate unique ID for cart using quote ID, IDs of all the items with their Qty and row total.
     *
     * @param Quote $quote
     * @return string
     */
    public function generatePostpayOrderId(Quote $quote): string
    {
        $quoteItems = $quote->getItems();

        $postpayOrderIdQuoteItems = '';
        foreach ($quoteItems as $quoteItem) {
            $postpayOrderIdQuoteItems .= sprintf(
                '%d_%.4f',
                $quoteItem->getItemId(),
                $quoteItem->getBaseRowTotalInclTax()
            );
        }

        $postpayOrderId = sprintf('postpay_order_id_%d_%s', $quote->getId(), $postpayOrderIdQuoteItems);

        return  $this->encryptor->hash($postpayOrderId);
    }

    /**
     * Generate unique ID for order using Postpay order ID and total amount left to refund on particular order.
     *
     * @param Order $order
     * @param float $amount
     * @return string
     */
    public function generatePostpayRefundId(Order $order, float $amount): string
    {
        $postpayOrderId = $order->getPayment()
            ->getAdditionalInformation(ConfigInterface::POSTPAY_ORDER_ID_PAYMENT_INFO_KEY);

        $postpayRefundId = sprintf(
            'postpay_refund_id_%s_%.4f',
            $postpayOrderId,
            ((float)$order->getBaseTotalPaid()-$order->getBaseTotalRefunded())
        );

        return  $this->encryptor->hash($postpayRefundId);
    }

    /**
     * @param $amount
     * @return float
     */
    public function formatDecimal($amount): float
    {
        return floatval(sprintf('%.4F', $amount));
    }
}