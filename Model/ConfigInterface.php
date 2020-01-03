<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model;


use Magento\Payment\Gateway\ConfigInterface as GatewayConfigInterface;

/**
 * Interface Config
 * @package Postpay\Postpay\Model
 */
Interface ConfigInterface extends GatewayConfigInterface
{
    /**
     * Postpay payment method code
     */
    const CODE = 'postpay';

    /**
     * Postpay order ID attribute code
     */
    const POSTPAY_ORDER_ID_ATTRIBUTE = 'postpay_order_id';

    /**
     * Postpay order ID attribute code
     */
    const POSTPAY_REDIRECT_URL_ATTRIBUTE = 'postpay_redirect_url';

    /**
     * Magento route for creating Postpay checkout
     */
    const POSTPAY_CHECKOUT_CREATE_ROUTE = 'postpay/checkout/create';

    /**
     * Magento route for capturing Postpay checkout
     */
    const POSTPAY_CHECKOUT_CONFIRMATION_ROUTE = 'postpay/checkout/confirmation';

    /**
     * Magento route for canceling Postpay checkout
     */
    const POSTPAY_CHECKOUT_CANCEL_ROUTE = 'postpay/checkout/cancel';

    /**
     * Magento route for successful Postpay checkout
     */
    const CHECKOUT_SUCCESS_ROUTE = 'checkout/onepage/success';

    /**
     * Magento route for canceled Postpay checkout
     */
    const CHECKOUT_CANCEL_ROUTE = 'checkout/cart';

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|null $storeId
     *
     * @return bool|null
     */
    public function isSetFlag(string $field, ?int $storeId = null): ?bool;

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

    /**
     * @return bool
     */
    public function getIsDebug(): bool;
}