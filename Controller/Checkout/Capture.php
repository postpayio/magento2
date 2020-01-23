<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Controller\Checkout;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Quote\Model\Quote;
use Postpay\Exceptions\PostpayException;
use Postpay\Postpay\Exception\PostpayCheckoutOrderException;
use Postpay\Postpay\Model\CheckoutManagerInterface;
use Postpay\Postpay\Model\ConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class Capture
 * @package Postpay\Postpay\Controller\Checkout
 */
class Capture extends Action
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CheckoutManagerInterface
     */
    private $checkoutManager;

    /**
     * @var LoggerInterface
     */
    private $systemLogger;

    /**
     * Capture constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param Logger $logger
     * @param CheckoutManagerInterface $checkoutManager
     * @param LoggerInterface $systemLogger
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        Logger $logger,
        CheckoutManagerInterface $checkoutManager,
        LoggerInterface $systemLogger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->checkoutManager = $checkoutManager;
        $this->checkoutSession = $checkoutSession;
        $this->systemLogger = $systemLogger;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $status = $this->getRequest()->getParam('status');
        $orderId = $this->getRequest()->getParam('order_id');

        /** @var Quote $quote */
        try {
            if(!$status || !$orderId) {
                throw new PostpayCheckoutOrderException(__('Malformed request.'));
            }

            $quote = $this->checkoutSession->getQuote();

            if($status !== CheckoutManagerInterface::STATUS_APPROVED) {
                $errorMessage = __(
                    'Postpay order was not approved. Status %1. Quote ID %2. Postpay reference %3.',
                    $status,
                    $quote->getId(),
                    $orderId
                );
                throw new PostpayCheckoutOrderException($errorMessage);
            }

            $postpayOrderId = $quote->getPayment()
                ->getAdditionalInformation(ConfigInterface::POSTPAY_ORDER_ID_PAYMENT_INFO_KEY);
            if( $postpayOrderId
                && $postpayOrderId == $orderId
                && $postpayOrderId === $this->checkoutManager->generatePostpayOrderId($quote)
            ) {
                $redirectUrl = $this->checkoutManager->convert($quote);
            } else {
                $errorMessage = __(
                    'Quote mismatch. Quote ID %1. Postpay reference %2.',
                    $quote->getId(),
                    $orderId
                );
                throw new PostpayCheckoutOrderException($errorMessage);
            }
        } catch (PostpayException $e) {
            $this->logger->debug([
                'exception' => $e->getMessage()
            ]);

            $this->systemLogger->critical($e);

            $errorCode = $e->getErrorCode();
            if($errorCode === 'expired') {
                $this->messageManager->addErrorMessage(
                    __('Unable to capture Postpay order.')
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __('Unable to capture Postpay order.')
                );
            }

            $redirectUrl = $this->_url->getUrl(ConfigInterface::CHECKOUT_CANCEL_ROUTE);
        }  catch (Exception $e) {
            $this->logger->debug([
                'exception' => $e->getMessage()
            ]);

            $this->systemLogger->critical($e);

            $this->messageManager->addErrorMessage(
                __('Unable to capture Postpay order.')
            );

            $redirectUrl = $this->_url->getUrl(ConfigInterface::CHECKOUT_CANCEL_ROUTE);
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_url->getUrl($redirectUrl));

        return $resultRedirect;
    }
}