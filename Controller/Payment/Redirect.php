<?php

namespace Postpay\Payment\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Api\CartRepositoryInterface;
use Postpay\Exceptions\ApiException;
use Postpay\Payment\Model\Request\Checkout;
use Postpay\Payment\Model\Postpay;
use Postpay\Payment\Model\Adapter\AdapterInterface;

/**
 * Class Redirect
 */
class Redirect extends Action
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
        $quote = $this->checkoutSession->getQuote();
        $quote->collectTotals()->reserveOrderId();
        $id = $quote->getReservedOrderId() . '-' . uniqid();

        try {
            $response = $this->postpayAdapter->checkout(
                Checkout::build($quote, $id)
            );
        } catch (ApiException $e) {
            $this->messageManager->addErrorMessage(
                __('Checkout error. Id: %1. Code: %2.', $id, $e->getErrorCode())
            );
            $this->_redirect('checkout/cart');
            return;
        }
        $payment = $quote->getPayment();
        $payment->setAdditionalInformation(Postpay::TRANSACTION_ID_KEY, $id);

        $this->quoteRepository->save($quote);
        $this->getResponse()->setRedirect($response['redirect_url']);
    }
}
