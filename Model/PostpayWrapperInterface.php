<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model;

use Postpay\Postpay;

/**
 * Interface PostpayWrapperInterface
 * @package Postpay\Postpay\Model
 */
interface PostpayWrapperInterface
{
    /**
     * @return Postpay
     */
    public function getPostpay(): Postpay;
}