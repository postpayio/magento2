<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
namespace Postpay\Postpay\Model\Payment;

use Exception;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order;
use Postpay\Postpay\Model\ConfigInterface;
use Postpay\Postpay\Model\PostpayWrapperInterface;
use Postpay\Postpay\Exception\PostpayCheckoutApiException;
use Postpay\Postpay\Model\CheckoutManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Postpay
 * @package Postpay\Postpay\Model\Payment
 */
class Postpay extends AbstractMethod
{
    /**
     * @var string
     */
    protected $_code = 'postpay';

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var PostpayWrapperInterface
     */
    private $postpayWrapper;

    /**
     * @var CheckoutManagerInterface
     */
    private $checkoutManager;

    /**
     * @var LoggerInterface
     */
    private $systemLogger;

    /**
     * Postpay constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param PostpayWrapperInterface $postpayWrapper
     * @param CheckoutManagerInterface $checkoutManager
     * @param LoggerInterface $systemLogger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param DirectoryHelper|null $directory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        PostpayWrapperInterface $postpayWrapper,
        CheckoutManagerInterface $checkoutManager,
        LoggerInterface $systemLogger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
        $this->postpayWrapper = $postpayWrapper;
        $this->checkoutManager = $checkoutManager;
        $this->systemLogger = $systemLogger;
    }

    /**
     * Capture payment
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws Exception
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function capture(InfoInterface $payment, $amount)
    {
        /** @var Order $order */
        $order = $payment->getOrder();

        $postpayOrderId = $order->getData(ConfigInterface::POSTPAY_ORDER_ID_ATTRIBUTE);

        try {
            $response = $this->postpayWrapper->post("/orders/$postpayOrderId/capture");

            if(!in_array($response->getStatusCode(), [200, 201, 202])) {
                $errorMessage = __(
                    'Request for capturing Postpay order through Postpay API was not successful. Postpay reference %1.',
                    $postpayOrderId
                );

                throw new PostpayCheckoutApiException($errorMessage);
            }

            $decodedResponse = $response->json();
            if(!$decodedResponse) {
                $errorMessage = __(
                    'Unable to decode Postpay API response to request for capturing Postpay order. Postpay reference %1.',
                    $postpayOrderId
                );

                throw new PostpayCheckoutApiException($errorMessage);
            }

            $decodedResponse = $response->json();
            if( !isset($decodedResponse['status'])
                || $decodedResponse['status'] !== CheckoutManagerInterface::STATUS_CAPTURED
            ) {
                $errorMessage = __(
                    'Capturing Postpay order through Postpay API was not successful. Postpay reference %1. Decoded response %2.',
                    $postpayOrderId,
                    $decodedResponse
                );

                throw new PostpayCheckoutApiException($errorMessage);
            }

            $payment->setTransactionId($postpayOrderId);
            $payment->setIsTransactionClosed(0);
        } catch (Exception $e) {
            $this->debugData([
                'transaction_id' => $postpayOrderId,
                'exception' => $e->getMessage()
            ]);

            $this->systemLogger->critical($e);

            throw $e;
        }

        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws Exception
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $postpayOrderId = $payment->getParentTransactionId();

        /** @var Order $order */
        $order = $payment->getOrder();

        try {
            $postpayRefundId = $this->checkoutManager->generatePostpayRefundId($order, $amount);

            $response = $this->postpayWrapper->post("/orders/$postpayOrderId/refunds", [
                'refund_id' => $postpayRefundId,
                'amount' => $amount
            ]);

            if(!in_array($response->getStatusCode(), [200, 201, 202])) {
                $errorMessage = __(
                    'Request for refunding Postpay order through Postpay API was not successful. Postpay reference %1.',
                    $postpayOrderId
                );

                throw new PostpayCheckoutApiException($errorMessage);
            }

            $decodedResponse = $response->json();
            if(!$decodedResponse) {
                $errorMessage = __(
                    'Unable to decode Postpay API response to request for refunding Postpay order. Postpay reference %1.',
                    $postpayOrderId
                );

                throw new PostpayCheckoutApiException($errorMessage);
            }

            $decodedResponse = $response->json();
            if(!isset($decodedResponse['amount'])) {
                $errorMessage = __(
                    'Refunding Postpay order through Postpay API was not successful. Postpay reference %1.',
                    $postpayOrderId
                );

                throw new PostpayCheckoutApiException($errorMessage);
            }

            $payment->setTransactionId($postpayRefundId);
            $payment->setParentTransactionId($postpayOrderId);
            $payment->setIsTransactionClosed(1);
        } catch (Exception $e) {
            $this->debugData([
                'transaction_id' => $postpayOrderId,
                'exception' => $e->getMessage()
            ]);

            $this->systemLogger->critical($e);

            throw $e;
        }

        return $this;
    }
}
