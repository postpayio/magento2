<?php

namespace Postpay\Payment\Model\Request;

use Magento\Quote\Model\Quote\Address;

/**
 * Class Guest
 */
class Guest
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
        return [
            'email' => $address->getEmail(),
            'first_name' => $address->getFirstname(),
            'last_name' => $address->getLastname(),
            'account' => 'guest'
        ];
    }
}
