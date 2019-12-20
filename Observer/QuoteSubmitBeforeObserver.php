<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Observer;

use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class QuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * SaveOrderBeforeSalesModelQuoteObserver constructor.
     * @param Copy $objectCopyService
     */
    public function __construct(
        Copy $objectCopyService
    ) {
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): QuoteSubmitBeforeObserver
    {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');

        /* @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);

        return $this;
    }
}