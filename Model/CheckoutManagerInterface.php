<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model;

use Magento\Quote\Model\Quote;

/**
 * Interface CheckoutManagerInterface
 * @package Postpay\Postpay\Model
 */
Interface CheckoutManagerInterface
{
    const STATUS_APPROVED = 'APPROVED';

    const STATUS_DENIED = 'DENIED';

    const STATUS_CANCELLED = 'CANCELLED';

    const STATUS_CAPTURED = 'captured';

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
}