<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model;

use Postpay\Exceptions\PostpayException;
use Postpay\Http\Response;
use Postpay\HttpClients\ClientInterface;
use Exception;
use Postpay\HttpClients\Client;
use Postpay\Postpay\Exception\PostpayConfigurationException;

/**
 * Interface PostpayWrapperInterface
 * @package Postpay\Postpay\Model
 */
interface PostpayWrapperInterface
{

    /**
     * Returns the client service.
     *
     * @return Client
     * @throws PostpayConfigurationException If Postpay is not configured correctly in Magento admin
     */
    public function getClient(): Client;

    /**
     * Sets the HTTP client handler.
     *
     * @param ClientInterface $clientHandler
     *
     * @throws Exception
     * @throws PostpayConfigurationException If Postpay is not configured correctly in Magento admin.
     */
    public function setClientHandler(ClientInterface $clientHandler);

    /**
     * Returns the last response returned from API.
     *
     * @return Response|null
     * @throws PostpayConfigurationException
     */
    public function getLastResponse(): ?Response;

    /**
     * Sends a GET request to API and returns the response.
     *
     * @param string $path
     * @param array  $params
     *
     * @return Response
     *
     * @throws PostpayException
     * @throws PostpayConfigurationException If Postpay is not configured correctly in Magento admin.
     */
    public function get(string $path, array $params = []): Response;

    /**
     * Sends a POST request to API and returns the response.
     *
     * @param string $path
     * @param array  $params
     *
     * @return Response
     *
     * @throws PostpayException
     * @throws PostpayConfigurationException If Postpay is not configured correctly in Magento admin.
     */
    public function post(string $path, array $params = []): Response;

    /**
     * Sends a PUT request to API and returns the response.
     *
     * @param string $path
     * @param array  $params
     *
     * @return Response
     *
     * @throws PostpayException
     * @throws PostpayConfigurationException If Postpay is not configured correctly in Magento admin.
     */
    public function put(string $path, array $params = []): Response;

    /**
     * Sends a PATCH request to API and returns the response.
     *
     * @param string $path
     * @param array  $params
     *
     * @return Response
     *
     * @throws PostpayException
     * @throws PostpayConfigurationException If Postpay is not configured correctly in Magento admin.
     */
    public function patch(string $path, array $params = []): Response;

    /**
     * Sends a DELETE request to API and returns the response.
     *
     * @param string $path
     * @param array  $params
     *
     * @return Response
     *
     * @throws PostpayException
     * @throws PostpayConfigurationException If Postpay is not configured correctly in Magento admin.
     */
    public function delete(string $path, array $params = []): Response;

    /**
     * Sends a query to GraphQL API and returns the response.
     *
     * @param string $query
     * @param array  $variables
     *
     * @return Response
     *
     * @throws PostpayException
     * @throws PostpayConfigurationException If Postpay is not configured correctly in Magento admin.
     */
    public function query(string $query, array $variables = []): Response;

    /**
     * Validate Postpay configuration in Magento admin.
     *
     * @return void
     *
     * @throws PostpayConfigurationException If Postpay is not configured correctly in Magento admin.
     */
    public function validateConfiguration();
}