<?php
/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
namespace Postpay\Payment\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
 
class One extends Action
{
    /**
     * The PageFactory to render with.
     *
     * @var PageFactory
     */
    protected $_resultsPageFactory;
 
    /**
     * Set the Context and Result Page Factory from DI.
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->_resultsPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
 
    /**
     * Show the One redirection Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
        return $this->_resultsPageFactory->create();
    }
}