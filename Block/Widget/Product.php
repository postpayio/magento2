<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Block\Widget;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Postpay\Payment\Gateway\Config\Config;
use Postpay\Payment\Model\Adapter\ApiAdapter;

/**
 * Class Product
 */
class Product extends AbstractProduct
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        Context $context,
        Config $config,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->priceCurrency = $priceCurrency;
    }

    public function isSandbox()
    {
        return $this->config->isSandbox();
    }

    public function getMerchantId()
    {
        return $this->config->getMerchantId();
    }

    public function getCurrencyCode()
    {
        return $this->priceCurrency->getCurrency()->getCurrencyCode();
    }

    public function getFinalPrice()
    {
        return ApiAdapter::decimal($this->getProduct()->getFinalPrice());
    }
}
