<?php

namespace Postpay\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const CODE = 'postpay';

    const KEY_ACTIVE = 'active';
    const KEY_SANDBOX = 'sandbox';
    const KEY_MERCHANT_ID = 'merchant_id';
    const KEY_SECRET_KEY = 'secret_key';
    const KEY_SANDBOX_SECRET_KEY = 'sandbox_secret_key';
    const KEY_PRODUCT_WIDGET = 'product_widget';

    /**
     * Constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param null|string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * Checks if sandbox field is enabled.
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isSandbox($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_SANDBOX, $storeId);
    }

    /**
     * Gets merchant ID.
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getMerchantId($storeId = null)
    {
        return $this->getValue(Config::KEY_MERCHANT_ID, $storeId);
    }

    /**
     * Gets private api key.
     *
     * @return mixed
     *
     * @return void
     */
    public function getSecretKey()
    {
        return $this->isSandbox() ?
            $this->getValue(self::KEY_SANDBOX_SECRET_KEY) :
            $this->getValue(self::KEY_SECRET_KEY);
    }

    /**
     * Gets Payment configuration status.
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_ACTIVE, $storeId);
    }

    /**
     * Checks if product widget is enabled.
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function productWidgetEnabled($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_PRODUCT_WIDGET);
    }
}
