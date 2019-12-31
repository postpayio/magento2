<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */

namespace Postpay\Postpay\Model\Ui;

use Postpay\Postpay\Model\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;

/**
 * Class ConfigProvider
 * @package Postpay\Postpay\Model\Ui
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * ConfigProvider constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config,
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
                    'startUrl' => $this->urlBuilder->getUrl()
                ]
            ],
        ];
    }
}
