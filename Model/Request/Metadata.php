<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Model\Request;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Class Metadata
 */
class Metadata
{
    /**
     * Build request.
     *
     * @return array
     */
    public static function build()
    {
        $objectManager = ObjectManager::getInstance();
        /** @var ProductMetadataInterface $productMetadata */
        $productMetadata = $objectManager->get(ProductMetadataInterface::class);
        /** @var ModuleListInterface $moduleList */
        $moduleList = $objectManager->get(ModuleListInterface::class);
        $module = $moduleList->getOne('Postpay_Payment');

        return [
            'platform' => [
                'name' => 'magento',
                'version' => $productMetadata->getVersion()
            ],
            'module' => [
                'name' => 'postpay/magento2',
                'version' => $module['setup_version']
            ]
        ];
    }
}
