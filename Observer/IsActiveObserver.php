<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Postpay\Payment\Gateway\Config\Config;
use Postpay\Payment\Model\Postpay;

/**
 * Check payment method availability.
 */
class IsActiveObserver implements ObserverInterface
{
    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $methodInstance = $event->getMethodInstance();

        if ($methodInstance instanceof Postpay && !$this->config->isAvailable()) {
            /** @var \Magento\Framework\DataObject $result */
            $result = $observer->getEvent()->getResult();
            $result->setData('is_available', false);
        }
    }
}
