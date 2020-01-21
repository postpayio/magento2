/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
define([
    'jquery',
    'Postpay_Postpay/postpay'
], function ($, Postpay) {
    'use strict';

    $.widget('mage.postpayProductWidget', {
        /** @inheritdoc */
        _create: function () {
            Postpay.renderProduct(this.element[0]);
        }
    });

    return $.mage.postpayProductWidget;
});
