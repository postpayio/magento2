<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Model\Request;

use Magento\Customer\Model\Data\Address;

/**
 * Class CustomerAddress
 */
class CustomerAddress
{
    /**
     * Build request.
     *
     * @param Address $address
     *
     * @return array
     */
    public static function build(Address $address)
    {
        $street = $address->getStreet();

        return [
            'first_name' => $address->getFirstname(),
            'last_name' => $address->getLastname(),
            'phone' => $address->getTelephone(),
            'line1' => $street[0],
            'line2' => isset($street[1]) ? $street[1]: '',
            'city' => $address->getCity(),
            'state' => $address->getRegion()->getRegionCode() ?: 'TODO',
            'country' => $address->getCountryId(),
            'postal_code' => $address->getPostcode()
        ];
    }
}
