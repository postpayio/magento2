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
use Postpay\Exceptions\PostpayException;
use Postpay\Postpay\Model\CheckoutManagerInterface;
use Postpay\Postpay\Model\ConfigInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Controller\ResultFactory;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

class Create extends Action
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
        /** @var Quote $quote */
        try {
            $quote = $this->checkoutSession->getQuote();

            $postpayOrderId = $quote->getPayment()
                ->getAdditionalInformation(ConfigInterface::POSTPAY_ORDER_ID_PAYMENT_INFO_KEY);
            if( $postpayOrderId
                && ($postpayOrderId === $this->checkoutManager->generatePostpayOrderId($quote))
            ) {
                $redirectUrl = $this->checkoutManager->recover($quote);
            } else {
                $redirectUrl = $this->checkoutManager->create($quote);
            }
        }  catch (PostpayException $e) {
            $this->logger->debug([
                'exception' => $e->getMessage()
            ]);

            $this->systemLogger->critical($e);

            $errorCode = $e->getErrorCode();
            if($errorCode == 'expired') {
                $this->messageManager->addErrorMessage(
                    __('Creating Postpay checkout failed. Please try again.')
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __('Creating Postpay checkout failed. Please try again.')
                );
            }

            $redirectUrl = ConfigInterface::CHECKOUT_CANCEL_ROUTE;
        }  catch (Exception $e) {
            $this->logger->debug([
                'exception' => $e->getMessage()
            ]);

            $this->systemLogger->critical($e);

            $this->messageManager->addErrorMessage(__('Creating Postpay checkout failed. Please try again.'));

            $redirectUrl = ConfigInterface::CHECKOUT_CANCEL_ROUTE;
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($redirectUrl);

        return $resultRedirect;
    }
}