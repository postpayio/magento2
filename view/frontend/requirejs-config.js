/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
var config = {
    map: {
        '*': {
            postpayWidget: 'Postpay_Payment/js/widget'
        }
    },
    shim: {
        postpayjs : {
            'exports': 'postpay'
        }
    },
    paths: {
        postpayjs: 'https://cdn.postpay.io/v1/js/postpay'
    }
};
