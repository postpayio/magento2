<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Model\Request;

use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Class Address
 */
class Address
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
            'first_name' => $address->getFirstname(),
            'last_name' => $address->getLastname(),
            'phone' => $address->getTelephone(),
            'line1' => $address->getStreetLine(1),
            'line2' => $address->getStreetLine(2) ?: '',
            'city' => $address->getCity(),
            'state' => $address->getRegionCode() ?: '',
            'country' => $address->getCountryId(),
            'postal_code' => $address->getPostcode()
        ];
    }
}
