<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
namespace Postpay\Postpay\Model\Payment;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class Postpay
 * @package Postpay\Postpay\Model\Payment
 */
class Postpay extends AbstractMethod
{
    /**
     * @var string
     */
    protected $_code = 'postpay';

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;
}
