<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Model\Request;

use Magento\Catalog\Helper\Image;
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
        /** @var Image $imageHelper */
        $imageHelper = $objectManager->get(Image::class);

        return [
            'reference' => $item->getId(),
            'name' => $item->getName(),
            'description' => $product->getDescription() ?: 'TODO',
            'url' => $product->getProductUrl(),
            'image_url' => $imageHelper->init($product, 'product_base_image')->getUrl(),
            'unit_price' => ApiAdapter::decimal($item->getBasePrice()),
            'qty' => $item->getQty()
        ];
    }
}
