<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model;

/**
 * Interface Config
 * @package Postpay\Postpay\Model
 */
Interface ConfigInterface
{
    /**
     * @return string
     */
    public function getInstructions(): string;

    /**
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return bool
     */
    public function getIsSandbox(): bool;

    /**
     * @return string
     */
    public function getMerchantId(): string;

    /**
     * @return string
     */
    public function getSecretKey(): string;

    /**
     * @return string
     */
    public function getSandboxSecretKey(): string;

    /**
     * @return bool
     */
    public function getIsProductWidget(): bool;

    /**
     * @return bool
     */
    public function getIsCartWidget(): bool;

    /**
     * @return string
     */
    public function getOrderStatus(): string;

    /**
     * @return bool
     */
    public function getIsAllowspecific(): bool;

    /**
     * @return array
     */
    public function getSpecificCountry(): array;

    /**
     * @return int
     */
    public function getSortOrder(): int;
}