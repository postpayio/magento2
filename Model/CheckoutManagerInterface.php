<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model;

use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Interface CheckoutManagerInterface
 * @package Postpay\Postpay\Model
 */
Interface CheckoutManagerInterface
{
    /**
     * Approved Postpay status
     */
    const STATUS_APPROVED = 'APPROVED';

    /**
     * Denied Postpay status
     */
    const STATUS_DENIED = 'DENIED';

    /**
     * Cancelled Postpay status
     */
    const STATUS_CANCELLED = 'CANCELLED';

    /**
     * Captured Postpay status
     */
    const STATUS_CAPTURED = 'captured';

    /**
     * Payment additional data payment reference key
     */
    const PAYMENT_REFERENCE_KEY = 'postpay_payment_reference';

    /**
     * @param Quote $quote
     * @return string
     */
    public function convert(Quote $quote): string;

    /**
     * @param Quote $quote
     * @return string
     */
    public function create(Quote $quote): string;

    /**
     * @param Quote $quote
     * @return string
     */
    public function recover(Quote $quote): string;

    /**
     * @param Quote $quote
     * @return string
     */
    public function generatePostpayOrderId(Quote $quote): string;

    /**
     * @param Order $order
     * @param float $amount
     * @return string
     */
    public function generatePostpayRefundId(Order $order, float $amount): string;
}