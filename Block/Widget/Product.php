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
use Magento\Customer\Model\Session;

/**
 * Product widget block.
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

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Config $config
     * @param PriceCurrencyInterface $priceCurrency
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        PriceCurrencyInterface $priceCurrency,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
        $this->priceCurrency = $priceCurrency;
        $this->customerSession = $customerSession;
    }

    /**
     * Get Ui parameters.
     *
     * @return array
     */
    public function getUiParams()
    {
        return $this->config->getUiParams();
    }

    /**
     * Get currency code.
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->priceCurrency->getCurrency()->getCurrencyCode();
    }

    /**
     * Get product final price.
     *
     * @return int
     */
    public function getFinalPrice()
    {
        return ApiAdapter::decimal($this->getProduct()->getFinalPrice());
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        if ($this->config->isActive() && $this->config->isAvailable()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Get MerchantId
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->config->getMerchantId();
    }

    /**
     * Get MaskedCartId
     *
     * @return string
     */
    public function getMaskedCartId()
    {
        return $this->getRequest()->getParam('cart');
    }

    /**
     * Get userId if loggedin
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->customerSession->getCustomer()->getId() ?: false;
    }

    /**
     * Check if express checkout(one) is enabled
     *
     * @return string
     */
    public function isOneEnabled()
    {
        return $this->config->isOneEnabled();
    }
}
