<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Model\Api;

use Postpay\Payment\Api\PlaceOrderInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Postpay\Exceptions\ApiException;
use Postpay\Payment\Model\Adapter\AdapterInterface;
use Postpay\Payment\Model\Method\AbstractPostpayMethod;
use Postpay\Payment\Model\Request\Checkout as CheckoutRequest;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Checkout
 */
class PlaceOrder implements PlaceOrderInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var AdapterInterface
     */
    private $postpayAdapter;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var CartManagementInterface
     */
    private $quoteManagement;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Constructor.
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param AdapterInterface $postpayAdapter
     * @param StoreManagerInterface $storeManager
     * @param GetCartForUser $getCartForUser
     * @param CartManagementInterface $quoteManagement
     * @param OrderFactory $orderFactory
     * @param CustomerFactory $customerFactory
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        AdapterInterface $postpayAdapter,
        StoreManagerInterface $storeManager,
        GetCartForUser $getCartForUser,
        CartManagementInterface $quoteManagement,
        OrderFactory $orderFactory,
        CustomerFactory $customerFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->postpayAdapter = $postpayAdapter;
        $this->storeManager = $storeManager;
        $this->getCartForUser = $getCartForUser;
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->customerFactory = $customerFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get checkout response(REST API to call Postpay checkout API)
     *
     */
    public function getCheckoutResponse(string $maskedCartId, string $checkoutType) : mixed
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $quote = $this->getCartForUser->execute($maskedCartId, null, $storeId);
        $quote->collectTotals()->reserveOrderId();
        $id = $quote->getReservedOrderId() . '-' . uniqid();
        $payment = $quote->getPayment();
        try {
            $response = $this->postpayAdapter->checkout(
                CheckoutRequest::build($quote, $id, $payment->getMethodInstance(), $checkoutType)
            );
        } catch (ApiException $e) {
            return json_encode(["error" => $e->getMessage()]);
        }
        $payment->setAdditionalInformation(AbstractPostpayMethod::TRANSACTION_ID_KEY, $id);
        $this->quoteRepository->save($quote);

        return json_encode($response);
    }

    
    /**
     * Get capture response(REST API to call Postpay capture API and Place Order)
     *
     */
    public function capture(string $maskedCartId) : mixed
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        //  null replace by customer id
        $quote = $this->getCartForUser->execute($maskedCartId, null, $storeId);
        $payment = $quote->getPayment();
        $id = $payment->getAdditionalInformation(AbstractPostpayMethod::TRANSACTION_ID_KEY);

        if ($id) {
            $quote->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
            try {
                $orderId = $this->quoteManagement->placeOrder($quote->getId());
                $order = $this->orderFactory->create()->load($orderId);
                $customer= $this->customerFactory->create();
                $customer->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
                $customer->loadByEmail($order->getCustomerEmail());
                if ($order->getId() && $customer->getId()) {
                    $order->setCustomerId($customer->getId());
                    $order->setCustomerIsGuest(0);
                    $this->orderRepository->save($order);
                }
                return json_encode(["status" => "success"]);
            } catch (ApiException $e) {
                return json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
        }
    }
}