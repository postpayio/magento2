<?php

namespace Postpay\Payment\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Cancel
 */
class Cancel extends Action
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->messageManager->addSuccessMessage(
            __('Postpay checkout has been canceled.')
        );
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('checkout/cart');
    }
}
