/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
define([
    'jquery',
    'Postpay_Postpay/postpay'
], function ($, Postpay) {
    'use strict';

    $.widget('mage.postpayCartWidget', {
        /** @inheritdoc */
        _create: function () {
            Postpay.renderCart(this.element[0]);
        }
    });

    return $.mage.postpayCartWidget;
});
