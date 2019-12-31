/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'postpay',
                component: 'Postpay_Postpay/js/view/payment/method-renderer/postpay-method'
            }
        );
        return Component.extend({});
    }
);