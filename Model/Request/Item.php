<?php

namespace Postpay\Payment\Model\Request;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Postpay\Payment\Model\Adapter\ApiAdapter;

/**
 * Class Item
 */
class Item
{
    /**
     * Build request.
     *
     * @param QuoteItem $item
     *
     * @return array
     */
    public static function build(QuoteItem $item)
    {
        $product = $item->getProduct();
        $objectManager = ObjectManager::getInstance();
        $imageHelper = $objectManager->get('\Magento\Catalog\Helper\Image');

        return [
            'reference' => $item->getId(),
            'name' => $item->getName(),
            'description' => $product->getDescription() ?: 'TODO',
            'url' => $product->getProductUrl(),
            'image_url' => $imageHelper->init($product, 'image')->getUrl(),
            'unit_price' => ApiAdapter::decimal($item->getBasePrice()),
            'qty' => $item->getQty()
        ];
    }
}
