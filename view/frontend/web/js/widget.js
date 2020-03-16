define([
    'jquery',
    'postpayjs'
], function ($, postpay) {
    'use strict';

    $.widget('mage.postpayWidget', {
        /** @inheritdoc */
        _create: function () {
            postpay.init(this.options);
        }
    });

    return $.mage.postpayWidget;
});
