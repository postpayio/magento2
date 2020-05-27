<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Webapi\Exception;
use Magento\Quote\Api\CartRepositoryInterface;
use Postpay\Exceptions\ApiException;
use Postpay\Payment\Model\Adapter\AdapterInterface;
use Postpay\Payment\Model\Method\AbstractPostpayMethod;
use Postpay\Payment\Model\Request\Checkout as CheckoutRequest;

/**
 * Create a Postpay checkout.
 */
class Checkout extends Action
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var AdapterInterface
     */
    private $postpayAdapter;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param AdapterInterface $postpayAdapter
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        AdapterInterface $postpayAdapter
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->postpayAdapter = $postpayAdapter;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        $quote->collectTotals()->reserveOrderId();
        $id = $quote->getReservedOrderId() . '-' . uniqid();
        /** @var \Magento\Quote\Model\Quote\Payment $payment */
        $payment = $quote->getPayment();
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $response = $this->postpayAdapter->checkout(
                CheckoutRequest::build($quote, $id, $payment->getMethodInstance())
            );
        } catch (ApiException $e) {
            $resultJson->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
            return $resultJson->setData(['message' => $e->getMessage()]);
        }
        $payment->setAdditionalInformation(AbstractPostpayMethod::TRANSACTION_ID_KEY, $id);
        $this->quoteRepository->save($quote);

        return $resultJson->setData($response);
    }
}
