<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Gateway\Config;

/**
 * Payment gateway configuration.
 */
class Config extends AbstractConfig
{
    const CODE = 'postpay';

    const KEY_MERCHANT_ID = 'merchant_id';
    const KEY_SECRET_KEY = 'secret_key';
    const KEY_SANDBOX_SECRET_KEY = 'sandbox_secret_key';
    const KEY_SANDBOX = 'sandbox';
    const KEY_THEME = 'theme';
    const KEY_IN_CONTEXT = 'in_context';

    /**
     * Get merchant ID.
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
     * Get private api key.
     *
     * @return string
     */
    public function getSecretKey($storeId = null)
    {
        return $this->isSandbox($storeId) ?
            $this->getValue(self::KEY_SANDBOX_SECRET_KEY, $storeId) :
            $this->getValue(self::KEY_SECRET_KEY, $storeId);
    }

    /**
     * Check if sandbox field is enabled.
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
     * Check if payment method is available.
     *
     * @return bool
     */
    public function isAvailable($storeId = null)
    {
        return (bool) $this->getMerchantId($storeId) && $this->getSecretKey($storeId);
    }

    /**
     * Check if product widget is enabled.
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function getTheme($storeId = null)
    {
        return $this->getValue(self::KEY_THEME, $storeId);
    }

    /**
     * Check if in-context checkout is enabled.
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function inContext($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_IN_CONTEXT, $storeId);
    }

    /**
     * Get Ui parameters.
     *
     * @return array
     */
    public function getUiParams($storeId = null)
    {
        return [
            'merchantId' => $this->getMerchantId($storeId),
            'sandbox' => $this->isSandbox($storeId),
            'theme' => $this->getTheme($storeId)
        ];
    }
}
