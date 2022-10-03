<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Model\Request;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use Postpay\Payment\Model\Adapter\ApiAdapter;

/**
 * Add checkout information to checkout request.
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
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function build(Quote $quote, $id, MethodInterface $method, $type="default")
    {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->getShippingAddress();

        if ($quote->getCustomerId()) {
            $customer = Customer::build($quote->getCustomer());
        } else {
            $customer = Guest::build($billing);
        }
        $items = array_map(
            'Postpay\Payment\Model\Request\Item::build',
            $quote->getAllVisibleItems()
        );

        return [
            'order_id' => $id,
            'total_amount' => ApiAdapter::decimal($quote->getBaseGrandTotal()),
            'tax_amount' => ApiAdapter::decimal($shipping->getBaseTaxAmount()),
            'currency' => $quote->getBaseCurrencyCode(),
            'shipping' => $quote->isVirtual() ? null : Shipping::build($shipping),
            'billing_address' => Address::build($billing),
            'customer' => $customer,
            'items' => $items,
            'merchant' => [
                'confirmation_url' => $type === "default" ? self::getUrl('postpay/payment/capture') : self::getUrl('sales/guest/form'),
                'cancel_url' => $type === "default" ? self::getUrl('postpay/payment/cancel') : reset($items)['url'],
            ],
            'metadata' => Metadata::build($method),
            'num_instalments' => $method::NUM_INSTALMENTS,
            'checkout_type' => $type
        ];
    }

    /**
     * Get absolute url.
     *
     * @param string $path
     *
     * @return string
     */
    public static function getUrl($path)
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->get(UrlInterface::class)->getUrl($path);
    }
}
