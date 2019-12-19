<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model;

use Magento\Backend\App\ConfigInterface as AppConfigInterface;

/**
 * Class Config
 * @package Postpay\Postpay\Model
 */
class Config implements ConfigInterface
{
    /**
     * Instructions path in configuration XML
     */
    const XML_INSTRUCTIONS = 'payment/postpay/instructions';

    /**
     * Enabled path in configuration XML
     */
    const XML_ACTIVE = 'payment/postpay/active';

    /**
     * Title path in configuration XML
     */
    const XML_TITLE = 'payment/postpay/title';

    /**
     * Sandbox path in configuration XML
     */
    const XML_SANDBOX = 'payment/postpay/sandbox';

    /**
     * Merchant ID path in configuration XML
     */
    const XML_MERCHANT_ID = 'payment/postpay/merchant_id';

    /**
     * Secret Key path in configuration XML
     */
    const XML_SECRET_KEY = 'payment/postpay/secret_key';

    /**
     * Secret Key path in configuration XML
     */
    const XML_SANDBOX_SECRET_KEY = 'payment/postpay/sandbox_secret_key';

    /**
     * Product Widget path in configuration XML
     */
    const XML_PRODUCT_WIDGET = 'payment/postpay/product_widget';

    /**
     * Cart Widget path in configuration XML
     */
    const XML_CART_WIDGET = 'payment/postpay/cart_widget';

    /**
     * New Order Status path in configuration XML
     */
    const XML_ORDER_STATUS = 'payment/postpay/order_status';

    /**
     * Payment from Applicable Countries path in configuration XML
     */
    const XML_ALLOWSPECIFIC = 'payment/postpay/allowspecific';

    /**
     * Payment from Specific Countries path in configuration XML
     */
    const XML_SPECIFICCOUNTRY = 'payment/postpay/specificcountry';

    /**
     * Sort Order path in configuration XML
     */
    const XML_SORT_ORDER = 'payment/postpay/sort_order';

    /**
     * @var AppConfigInterface
     */
    private $config;

    /**
     * Config constructor.
     * @param AppConfigInterface $config
     */
    public function __construct(
        AppConfigInterface $config
    )
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getInstructions(): string
    {
        return (string)$this->config->getValue(self::XML_INSTRUCTIONS);
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return (bool)$this->config->isSetFlag(self::XML_ACTIVE);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return (string)$this->config->getValue(self::XML_TITLE);
    }

    /**
     * @return bool
     */
    public function getIsSandbox(): bool
    {
        return (bool)$this->config->isSetFlag(self::XML_SANDBOX);
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return (string)$this->config->getValue(self::XML_MERCHANT_ID);
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return (string)$this->config->getValue(self::XML_SECRET_KEY);
    }

    /**
     * @return string
     */
    public function getSandboxSecretKey(): string
    {
        return (string)$this->config->getValue(self::XML_SANDBOX_SECRET_KEY);
    }

    /**
     * @return bool
     */
    public function getIsProductWidget(): bool
    {
        return (bool)$this->config->isSetFlag(self::XML_PRODUCT_WIDGET);
    }

    /**
     * @return bool
     */
    public function getIsCartWidget(): bool
    {
        return (bool)$this->config->isSetFlag(self::XML_CART_WIDGET);
    }

    /**
     * @return string
     */
    public function getOrderStatus(): string
    {
        return (string)$this->config->getValue(self::XML_ORDER_STATUS);
    }

    /**
     * @return bool
     */
    public function getIsAllowspecific(): bool
    {
        return (bool)$this->config->isSetFlag(self::XML_ALLOWSPECIFIC);
    }

    /**
     * @return array
     */
    public function getSpecificCountry(): array
    {
        return (array)$this->config->getValue(self::XML_SPECIFICCOUNTRY);
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return (int)$this->config->getValue(self::XML_SORT_ORDER);
    }
}