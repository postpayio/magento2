<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Block\Widget;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Postpay\Postpay\Model\ConfigInterface;

/**
 * Class Cart
 * @package Postpay\Postpay\Block\Widget
 */
class Cart implements ArgumentInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Cart constructor.
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function isCartWidgetEnabled(): bool
    {
        return $this->config->isCartWidgetEnabled();
    }
}