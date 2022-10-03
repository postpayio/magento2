/**
 * Copyright © Postpay. All rights reserved.
 * See LICENSE for license details.
 */
define(['jquery'], function($){
        "use strict";
        return function     (config,element) {
            let currency = config.currency;
            let merchantid = config.merchantid;
            let cart = config.cart;
            let cid =  config.cid;

            let url = "https://checkout-dev.postpay.io/one/?currency="+currency+"&merchantid="+merchantid+"&cart="+cart;
            if (cid != null) {
                url += "&cid=" + cid;
            }
            window.open(url, '_self');
        }
 });