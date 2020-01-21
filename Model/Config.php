<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Postpay\Postpay\Model
 */
class Config implements ConfigInterface
{
    /**
     * Instructions path in configuration XML
     */
    const XML_INSTRUCTIONS = 'instructions';

    /**
     * Enabled path in configuration XML
     */
    const XML_ACTIVE = 'active';

    /**
     * Title path in configuration XML
     */
    const XML_TITLE = 'title';

    /**
     * Sandbox path in configuration XML
     */
    const XML_SANDBOX = 'sandbox';

    /**
     * Merchant ID path in configuration XML
     */
    const XML_MERCHANT_ID = 'merchant_id';

    /**
     * Secret Key path in configuration XML
     */
    const XML_SECRET_KEY = 'secret_key';

    /**
     * Secret Key path in configuration XML
     */
    const XML_SANDBOX_SECRET_KEY = 'sandbox_secret_key';

    /**
     * Product Widget path in configuration XML
     */
    const XML_PRODUCT_WIDGET = 'product_widget';

    /**
     * Cart Widget path in configuration XML
     */
    const XML_CART_WIDGET = 'cart_widget';

    /**
     * Payment from Applicable Countries path in configuration XML
     */
    const XML_ALLOWSPECIFIC = 'allowspecific';

    /**
     * Payment from Specific Countries path in configuration XML
     */
    const XML_SPECIFICCOUNTRY = 'specificcountry';

    /**
     * Sort Order path in configuration XML
     */
    const XML_SORT_ORDER = 'sort_order';

    /**
     * Debug path in configuration XML
     */
    const XML_DEBUG = 'debug';

    /**
     * Default payment method configuration path pattern
     */
    const DEFAULT_PATH_PATTERN = 'payment/%s/%s';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string|null
     */
    private $methodCode;

    /**
     * @var string|null
     */
    private $pathPattern;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string|null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->methodCode = $methodCode;
        $this->pathPattern = $pathPattern;
    }

    /**
     * Sets method code
     *
     * @param string $methodCode
     * @return void
     */
    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
    }

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @return void
     */
    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        if ($this->methodCode === null || $this->pathPattern === null) {
            return null;
        }

        return $this->scopeConfig->getValue(
            sprintf($this->pathPattern, $this->methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $field
     * @param int|null $storeId
     * @return bool|null
     */
    public function isSetFlag(string $field, ?int $storeId = null): ?bool
    {
        if ($this->methodCode === null || $this->pathPattern === null) {
            return null;
        }

        return $this->scopeConfig->isSetFlag(
            sprintf($this->pathPattern, $this->methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return string
     */
    public function getInstructions(): string
    {
        return (string)$this->getValue(self::XML_INSTRUCTIONS);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->isSetFlag(self::XML_ACTIVE);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return (string)$this->getValue(self::XML_TITLE);
    }

    /**
     * @return bool
     */
    public function getIsSandbox(): bool
    {
        return (bool)$this->isSetFlag(self::XML_SANDBOX);
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return (string)$this->getValue(self::XML_MERCHANT_ID);
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return (string)$this->getValue(self::XML_SECRET_KEY);
    }

    /**
     * @return string
     */
    public function getSandboxSecretKey(): string
    {
        return (string)$this->getValue(self::XML_SANDBOX_SECRET_KEY);
    }

    /**
     * @return bool
     */
    public function isProductWidgetEnabled(): bool
    {
        return (bool)$this->isSetFlag(self::XML_PRODUCT_WIDGET);
    }

    /**
     * @return bool
     */
    public function isCartWidgetEnabled(): bool
    {
        return (bool)$this->isSetFlag(self::XML_CART_WIDGET);
    }

    /**
     * @return bool
     */
    public function isAllowspecific(): bool
    {
        return (bool)$this->isSetFlag(self::XML_ALLOWSPECIFIC);
    }

    /**
     * @return array
     */
    public function getSpecificCountry(): array
    {
        return (array)$this->getValue(self::XML_SPECIFICCOUNTRY);
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return (int)$this->getValue(self::XML_SORT_ORDER);
    }

    /**
     * @inheritDoc
     */
    public function isDebug(): bool
    {
        return (bool)$this->isSetFlag(self::XML_DEBUG);
    }
}