<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model\Ui;

use Postpay\Postpay\Model\ConfigInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;

/**
 * Class ConfigProvider
 * @package Postpay\Postpay\Model\Ui
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * ConfigProvider constructor.
     * @param ConfigInterface $config
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ConfigInterface $config,
        UrlInterface $urlBuilder
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'postpay' => [
                    'instructions' => $this->config->getInstructions(),
                    'createUrl' => $this->urlBuilder->getUrl(ConfigInterface::POSTPAY_CHECKOUT_CREATE_ROUTE)
                ]
            ],
        ];
    }
}