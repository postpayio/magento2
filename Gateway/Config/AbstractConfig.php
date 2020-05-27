<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Configuration abstract class.
 */
abstract class AbstractConfig extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_SUMMARY_WIDGET = 'summary_widget';

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
     * Get payment configuration status.
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
     * Check if payment summary widget is enabled.
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function summaryWidgetEnabled($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_SUMMARY_WIDGET, $storeId);
    }
}
