<?php

namespace Postpay\Payment\Model\Request;

use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote;
use Postpay\Payment\Model\Adapter\ApiAdapter;

/**
 * Class Checkout
 */
class Checkout
{
    /**
     * Build request.
     *
     * @param Quote $quote
     * @param string $id
     *
     * @return array
     */
    public static function build(Quote $quote, $id)
    {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->getShippingAddress();

        if ($quote->getCustomerId()) {
            $customer = Customer::build($quote->getCustomer());
        } else {
            $customer = Guest::build($billing);
        }

        return [
            'order_id' => $id,
            'total_amount' => ApiAdapter::decimal($quote->getBaseGrandTotal()),
            'tax_amount' => ApiAdapter::decimal($shipping->getBaseTaxAmount()),
            'currency' => $quote->getBaseCurrencyCode(),
            'shipping' => Shipping::build($shipping),
            'billing_address' => Address::build($billing),
            'customer' => $customer,
            'items' => array_map(
                'Postpay\Payment\Model\Request\Item::build',
                $quote->getAllVisibleItems()
            ),
            'merchant' => [
                'confirmation_url' => self::getUrl('postpay/payment/capture'),
                'cancel_url' => self::getUrl('postpay/payment/cancel')
            ]
        ];
    }

    public static function getUrl($path)
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->get('Magento\Framework\Url')->getUrl($path);
    }
}