<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteRepository;
use Postpay\Exceptions\PostpayException;
use Postpay\Postpay\Exception\PostpayCheckoutApiException;
use Postpay\Postpay\Exception\PostpayCheckoutCartException;
use Postpay\Postpay\Exception\PostpayConfigurationException;
use Psr\Log\LoggerInterface;

/**
 * Class CheckoutManager
 * @package Postpay\Postpay\Model
 */
class CheckoutManager implements CheckoutManagerInterface
{
    /** @var Encryptor */
    private $encryptor;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var PostpayWrapperInterface
     */
    private $postpayWrapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * CheckoutManager constructor.
     * @param Encryptor $encryptor
     * @param QuoteRepository $quoteRepository
     * @param ImageHelper $imageHelper
     * @param PostpayWrapperInterface $postpayWrapper
     * @param LoggerInterface $logger
     * @param UrlInterface $url
     */
    public function __construct(
        Encryptor $encryptor,
        QuoteRepository $quoteRepository,
        ImageHelper $imageHelper,
        PostpayWrapperInterface $postpayWrapper,
        LoggerInterface $logger,
        UrlInterface $url
    ) {
        $this->encryptor = $encryptor;
        $this->quoteRepository = $quoteRepository;
        $this->imageHelper = $imageHelper;
        $this->postpayWrapper = $postpayWrapper;
        $this->logger = $logger;
        $this->url = $url;
    }

    /**
     * @param Quote $quote
     * @return string
     * @throws PostpayCheckoutApiException
     * @throws PostpayCheckoutCartException
     * @throws PostpayException
     * @throws PostpayConfigurationException
     */
    public function createCheckout(Quote $quote): string
    {
        /** @var Item[] $quoteItems */
        $quoteItems = $quote->getAllVisibleItems();

        if(!$quoteItems) {
            $errorMessage = __('Unable to create Postpay checkout since quote does not contain any items.');
            throw new PostpayCheckoutCartException(sprintf('%s: %s', $errorMessage, $quote->getId()));
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

        $shippingEntity = [
            'id' => $shippingAddress->getShippingMethod(),
            'name' => $shippingAddress->getShippingDescription(),
            'amount' => $shippingAddress->getShippingInclTax(),
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
            $itemsEntityItem['unit_price'] = $quoteItem->getBasePriceInclTax();
            $itemsEntityItem['qty'] = $quoteItem->getQty();
            $itemsEntity[] = $itemsEntityItem;

            $discountAmount += $quoteItem->getBaseDiscountAmount();
            $discountCode = $discountName = $quote->getCouponCode();
        }

        $merchantEntity = [
            /*
             * URL that the customer is sent to if they successfully complete the checkout process. The order_id and
             * status=APPROVED will be sent to this URL as a HTTP query parameter in order to capture the order.
             */
            'confirmation_url' => $this->url->getUrl(ConfigInterface::POSTPAY_CHECKOUT_CONFIRMATION_ROUTE),

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
            'total_amount' => $quote->getBaseGrandTotal(),
            'tax_amount' => $shippingAddress->getBaseTaxAmount(),
            'currency' => $quote->getBaseCurrencyCode(),
            'shipping' => $shippingEntity,
            'billing_address' => $billingAddressEntity,
            'customer' => $customerEntity,
            'items' => $itemsEntity,
            'merchant' => $merchantEntity
        ];

        if($discountAmount) {
            $discountEntity = [
                'code' => $discountCode,
                'name' => $discountName,
                'amount' => $discountAmount
            ];
            $payload['discounts'] = $discountEntity;
        }

        $response = $this->postpayWrapper->post('/checkouts', $payload);

        if(!in_array($response->getStatusCode(), [200, 201, 202])) {
            $errorMessage = __('Postpay API request was not successful.');

            throw new PostpayCheckoutApiException(sprintf('%s: %s', $errorMessage, $response->json()));
        }

        $decodedResponse = $response->json();

        if(!$decodedResponse || !isset($decodedResponse['redirect_url'])) {
            $errorMessage = __('Unable to decode Postpay API response.');

            throw new PostpayCheckoutApiException(sprintf('%s: %s', $errorMessage, $response->json()));
        }

        $postpayRedirectUrl = $decodedResponse['redirect_url'];

        $quote->setData(ConfigInterface::POSTPAY_ORDER_ID_ATTRIBUTE, $postpayOrderId);
        $quote->setData(ConfigInterface::POSTPAY_REDIRECT_URL_ATTRIBUTE, $postpayRedirectUrl);
        $this->quoteRepository->save($quote);

        return $postpayRedirectUrl;
    }

    /**
     * @param Quote $quote
     * @return string
     */
    public function recoverCheckout(Quote $quote): string
    {
        return $quote->getData(Config::POSTPAY_REDIRECT_URL_ATTRIBUTE);
    }

    /**
     * Generate unique ID for cart (out of quote ID and IDs of all the items with their Qty and row total)
     *
     * @param Quote $quote
     * @return string
     */
    private function generatePostpayOrderId(Quote $quote): string
    {
        $quoteItems = $quote->getItems();

        // We generate unique ID for this cart (quote ID and IDs of all the items with their Qty
        $postpayOrderId = sprintf('postpay_%d', $quote->getId());
        foreach ($quoteItems as $quoteItem) {
            $postpayOrderId .= sprintf(
                '_%d_%.4f',
                $quoteItem->getItemId(),
                $quoteItem->getBaseRowTotalInclTax()
            );
        }

        return  $this->encryptor->hash($postpayOrderId);
    }
}