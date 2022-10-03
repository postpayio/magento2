<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Api;

interface PlaceOrderInterface
{
    /**
     * @param string $maskedCartId
     * @param string $checkoutType
     * @return string
     */
    public function getCheckoutResponse(string $maskedCartId, string $checkoutType);

    
    /**
     * @param string $maskedCartId
     * @return string
     */
    public function capture(string $maskedCartId);
}
