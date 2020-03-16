<?php

namespace Postpay\Payment\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Postpay\Payment\Model\Payment\Postpay;

/**
 * Class IsActiveObserver
 */
class IsActiveObserver implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $event = $observer->getEvent();
        $methodInstance = $event->getMethodInstance();
        if ($methodInstance instanceof Postpay) {
            /** @var \Magento\Framework\DataObject $result */
            $result = $observer->getEvent()->getResult();
            $result->setData('is_available', false);
        }
    }
}
