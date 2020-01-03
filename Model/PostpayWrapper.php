<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model;

use Magento\Framework\Phrase;
use Magento\Payment\Model\Method\Logger;
use Postpay\Http\Response;
use Postpay\HttpClients\Client;
use Postpay\HttpClients\ClientInterface;
use Postpay\Postpay\Exception\PostpayConfigurationException;
use Postpay\PostpayFactory;
use Postpay\Postpay;

/**
 * Class PostpayWrapper
 * @package Postpay\Postpay\Model
 */
class PostpayWrapper implements PostpayWrapperInterface
{
    /**
     * @var Postpay
     */
    private $postpay;

    /**
     * @var PostpayFactory
     */
    private $postpayFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * PostpayWrapper constructor.
     * @param PostpayFactory $postpayFactory
     * @param ConfigInterface $config
     * @param Logger $logger
     * @throws PostpayConfigurationException
     */
    public function __construct(
        PostpayFactory $postpayFactory,
        ConfigInterface $config,
        Logger $logger
    ) {
        $this->postpayFactory = $postpayFactory;
        $this->config = $config;
        $this->logger = $logger;

        $isActive = $this->config->getIsActive();
        $merchantId = $this->config->getMerchantId();
        $secretKey = $this->config->getSecretKey();
        $sandboxSecretKey = $this->config->getSandboxSecretKey();
        $sandbox = $this->config->getIsSandbox();

        if(!$isActive || !$merchantId || !(($secretKey && !$sandbox) || ($sandboxSecretKey && $sandbox)))  {
            return;
        }

        $this->postpay = $this->postpayFactory->create([
            'config' => [
                'merchant_id' => $merchantId,
                'secret_key' => $secretKey ? $secretKey : $sandboxSecretKey,
                'sandbox' => $sandbox,
                'client_handler' => 'guzzle'
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getClient(): Client
    {
        $this->validateConfiguration();

        return $this->postpay->getClient();
    }

    /**
     * @inheritDoc
     */
    public function setClientHandler(ClientInterface $clientHandler)
    {
        $this->validateConfiguration();

        $this->postpay->setClientHandler($clientHandler);
    }

    /**
     * @inheritDoc
     */
    public function getLastResponse(): ?Response
    {
        $this->validateConfiguration();

        return $this->postpay->getLastResponse();
    }

    /**
     * @inheritDoc
     */
    public function get(string $path, array $params = []): Response
    {
        $this->logger->debug([
            'method' => __METHOD__,
            'path' => $path,
            'params' => $params
        ]);

        $this->validateConfiguration();

        $response = $this->postpay->get($path, $params);

        $this->logger->debug([
            'method' => __METHOD__,
            'request' => $response->getRequest()->json(),
            'response' => $response->json()
        ]);

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function post(string $path, array $params = []): Response
    {
        $this->logger->debug([
            'method' => __METHOD__,
            'path' => $path,
            'params' => $params
        ]);

        $this->validateConfiguration();

        $response = $this->postpay->post($path, $params);

        $this->logger->debug([
            'method' => __METHOD__,
            'request' => $response->getRequest()->json(),
            'response' => $response->json()
        ]);

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function put(string $path, array $params = []): Response
    {
        $this->logger->debug([
            'method' => __METHOD__,
            'path' => $path,
            'params' => $params
        ]);

        $this->validateConfiguration();

        $response = $this->postpay->put($path, $params);

        $this->logger->debug([
            'method' => __METHOD__,
            'request' => $response->getRequest()->json(),
            'response' => $response->json()
        ]);

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function patch(string $path, array $params = []): Response
    {
        $this->logger->debug([
            'method' => __METHOD__,
            'path' => $path,
            'params' => $params
        ]);

        $this->validateConfiguration();

        $response = $this->postpay->patch($path, $params);

        $this->logger->debug([
            'method' => __METHOD__,
            'request' => $response->getRequest()->json(),
            'response' => $response->json()
        ]);

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $path, array $params = []): Response
    {
        $this->logger->debug([
            'method' => __METHOD__,
            'path' => $path,
            'params' => $params
        ]);

        $this->validateConfiguration();

        $response = $this->postpay->delete($path, $params);

        $this->logger->debug([
            'method' => __METHOD__,
            'request' => $response->getRequest()->json(),
            'response' => $response->json()
        ]);

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function query(string $query, array $variables = []): Response
    {
        $this->logger->debug([
            'method' => __METHOD__,
            'query' => $query,
            'variables' => $variables
        ]);

        $this->validateConfiguration();

        $response = $this->postpay->query($query, $variables);

        $this->logger->debug([
            'method' => __METHOD__,
            'request' => $response->getRequest()->json(),
            'response' => $response->json()
        ]);

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function validateConfiguration()
    {
        if(is_null($this->postpay)) {
            throw new PostpayConfigurationException(
                new Phrase('Unable to initiate Postpay. Please check Postpay configuration in Magento admin.')
            );
        }
    }
}