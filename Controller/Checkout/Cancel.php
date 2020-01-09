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
                        'Postpay order cancelled. Quote ID %s.',
                        $quote->getId()
                    );
                    break;
                case CheckoutManagerInterface::STATUS_DENIED:
                    $errorMessage = __(
                        'Postpay order denied. Quote ID %s.',
                        $quote->getId()
                    );
                    break;
                default:
                    $errorMessage = __(
                        'Postpay order was not approved. Status %s. Quote ID %s. Postpay reference %s.',
                        $status,
                        $quote->getId()
                    );
                    break;
            }
            throw new PostpayCheckoutOrderException($errorMessage);
        } catch (Exception $e) {
            $this->logger->critical($e);

            $this->messageManager->addErrorMessage(__('Unable to capture Postpay order.'));

            $redirectUrl = ConfigInterface::CHECKOUT_CANCEL_ROUTE;
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_url->getUrl($redirectUrl));

        return $resultRedirect;
    }
}