<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Model\Adapter;

use DateTime;
use Magento\Payment\Model\Method\Logger;
use Postpay\Exceptions\ApiException;
use Postpay\Payment\Gateway\Config\Config;
use Postpay\PostpayFactory;
use Postpay\Serializers\Decimal;
use Postpay\Serializers\Date;
use Psr\Log\LoggerInterface;

/**
 * Class ApiAdapter
 */
class ApiAdapter
{
    /**
     * @var \Postpay\Payment
     */
    protected $client;

    /**
     * @var PostpayFactory
     */
    protected $postpayFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Logger
     */
    protected $customLogger;

    /**
     * Constructor.
     *
     * @param PostpayFactory $postpayFactory
     * @param Config $config
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     */
    public function __construct(
        PostpayFactory $postpayFactory,
        Config $config,
        LoggerInterface $logger,
        Logger $customLogger
    ) {
        $this->postpayFactory = $postpayFactory;
        $this->config = $config;
        $this->logger = $logger;
        $this->customLogger = $customLogger;
        $this->client = $this->postpayFactory->create([
            'config' => [
                'merchant_id' => $this->config->getMerchantId(),
                'secret_key' => $this->config->getSecretKey(),
                'sandbox' => $this->config->isSandbox(),
                'client_handler' => 'guzzle'
            ]
        ]);
    }

    /**
     * Send a request and return the response.
     *
     * @param string $method
     * @param string $path
     * @param array $params
     *
     * @return array
     *
     * @throws ApiException
     */
    public function request($method, $path, array $params = [])
    {
        try {
            /** @var \Postpay\Http\Response $response */
            $response = $this->client->request($method, $path, $params);
        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage());
            $response = $e->getResponse();
            throw $e;
        } finally {
            $this->customLogger->debug([
                'path' => $path,
                'request' => $params,
                'response' => $response->json()
            ]);
        }
        return $response->json();
    }

    /**
     * Convert float to JSON serializable instance.
     *
     * @param float $value
     *
     * @return Decimal
     */
    public static function decimal($value)
    {
        return Decimal::fromFloat($value);
    }

    /**
     * Convert date to JSON serializable instance.
     *
     * @param string $value
     *
     * @return Date
     */
    public static function date($value)
    {
        return Date::fromDate(new DateTime($value));
    }

    /**
     * Convert datetime to JSON serializable instance.
     *
     * @param string $value
     *
     * @return Date
     */
    public static function datetime($value)
    {
        return Date::fromDateTime(new DateTime($value));
    }
}
