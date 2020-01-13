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
use Postpay\Postpay\Exception\PostpayCheckoutOrderException;
use Postpay\Postpay\Model\CheckoutManagerInterface;
use Postpay\Postpay\Model\ConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class Cancel
 * @package Postpay\Postpay\Controller\Checkout
 */
class Cancel extends Action
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
     * Create constructor.
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

        /** @var Quote $quote */
        try {
            if(!$status) {
                throw new PostpayCheckoutOrderException(__('Malformed request.'));
            }

            $quote = $this->checkoutSession->getQuote();

            switch ($status) {
                case CheckoutManagerInterface::STATUS_CANCELLED:
                    $errorMessage = __(
                        'Postpay order cancelled. Quote ID %1.',
                        $quote->getId()
                    );
                    break;
                case CheckoutManagerInterface::STATUS_DENIED:
                    $errorMessage = __(
                        'Postpay order denied. Quote ID %1.',
                        $quote->getId()
                    );
                    break;
                default:
                    $errorMessage = __(
                        'Postpay order was not approved. Status %1. Quote ID %2.',
                        $status,
                        $quote->getId()
                    );
                    break;
            }
            throw new PostpayCheckoutOrderException($errorMessage);
        } catch (Exception $e) {
            $this->logger->debug([
                'exception' => $e->getMessage()
            ]);

            $this->systemLogger->critical($e);

            $this->messageManager->addErrorMessage(__('Unable to capture Postpay order.'));

            $redirectUrl = ConfigInterface::CHECKOUT_CANCEL_ROUTE;
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_url->getUrl($redirectUrl));

        return $resultRedirect;
    }
}