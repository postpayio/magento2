/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
var config = {
    map: {
        '*': {
            postpayCartWidget: 'Postpay_Postpay/js/cartWidget',
            postpayProductWidget: 'Postpay_Postpay/js/productWidget'
        }
    },
    shim: {
        'Postpay_Postpay/postpay' : {
            'exports': 'Postpay'
        }
    },
    paths: {
        'Postpay_Postpay/postpay': 'https://cdn.jsdelivr.net/gh/Marko-M/postpay-js@a7c481b48f58579ef41b99a711f3be80067a3882/postpay'
    }
};