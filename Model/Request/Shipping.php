<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Model\Request;

use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Postpay\Payment\Model\Adapter\ApiAdapter;

/**
 * Class Shipping
 */
class Shipping
{
    /**
     * Build request.
     *
     * @param QuoteAddress $address
     *
     * @return array
     */
    public static function build(QuoteAddress $address)
    {
        return [
            'id' => $address->getShippingMethod(),
            'name' => $address->getShippingDescription(),
            'amount' => ApiAdapter::decimal($address->getBaseShippingAmount()),
            'address' => Address::build($address)
        ];
    }
}
