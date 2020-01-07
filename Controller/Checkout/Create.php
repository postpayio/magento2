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
use Postpay\Postpay\Model\CheckoutManagerInterface;
use Postpay\Postpay\Model\ConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Controller\ResultFactory;

class Create extends Action
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CheckoutManagerInterface
     */
    private $checkoutManager;

    /**
     * Create constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param CheckoutManagerInterface $checkoutManager
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        LoggerInterface $logger,
        CheckoutManagerInterface $checkoutManager
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->checkoutManager = $checkoutManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Quote $quote */
        try {
            $quote = $this->checkoutSession->getQuote();

            $postpayOrderId = $quote->getData(ConfigInterface::POSTPAY_ORDER_ID_ATTRIBUTE);
            if( $postpayOrderId
                && ($postpayOrderId === $this->checkoutManager->generatePostpayOrderId($quote))
            ) {
                $redirectUrl = $this->checkoutManager->recover($quote);
            } else {
                $redirectUrl = $this->checkoutManager->create($quote);
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('Creating Postpay checkout failed. Please try again.'));

            $redirectUrl = ConfigInterface::CHECKOUT_CANCEL_ROUTE;
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($redirectUrl);

        return $resultRedirect;
    }
}