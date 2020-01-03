<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultInterface;
use Postpay\Postpay\Model\ConfigInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Cancel
 * @package Postpay\Postpay\Controller\Checkout
 */
class Cancel extends Action
{
    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_url->getUrl(ConfigInterface::CHECKOUT_CANCEL_ROUTE));

        return $resultRedirect;
    }
}