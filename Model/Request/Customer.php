<?php

namespace Postpay\Payment\Model\Request;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Postpay\Payment\Model\Adapter\ApiAdapter;

/**
 * Class Customer
 */
class Customer
{
    /**
     * Build request.
     *
     * @param CustomerInterface $customer
     *
     * @return array
     */
    public static function build(CustomerInterface $customer)
    {
        switch ($customer->getGender()) {
            case 1:
                $gender = 'male';
                break;
            case 2:
                $gender = 'female';
                break;
            case 3:
            default:
                $gender = 'other';
        }

        $data = [
            'email' => $customer->getEmail(),
            'id' => $customer->getId(),
            'first_name' => $customer->getFirstname(),
            'last_name' => $customer->getLastname(),
            'gender' => $gender,
            'account' => 'existing'
        ];

        if ($dateOfBirth = $customer->getDob()) {
            $data['date_of_birth'] = ApiAdapter::date($dateOfBirth);
        }

        if ($createdAt = $customer->getcreatedAt()) {
            $data['date_joined'] = ApiAdapter::datetime($createdAt);
        }

        if ($defaultAddressId = $customer->getDefaultShipping()) {
            $objectManager = ObjectManager::getInstance();
            $addressRepository = $objectManager->get(
                'Magento\Customer\Api\AddressRepositoryInterface'
            );
            /** @var \Magento\Customer\Model\Data\Address $defaultAddress */
            $defaultAddress = $addressRepository->getById($defaultAddressId);
            $data['default_address'] = CustomerAddress::build($defaultAddress);
        }
        return $data;
    }
}
