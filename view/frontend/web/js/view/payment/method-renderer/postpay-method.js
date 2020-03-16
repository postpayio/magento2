define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Postpay_Postpay/js/action/set-payment-method',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Customer/js/customer-data'
], function (
    $,
    Component,
    setPaymentMethodAction,
    additionalValidators,
    customerData
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Postpay_Payment/payment/postpay'
        },
        continueToPostpay: function () {
            if (additionalValidators.validate()) {
                //update payment method information if additional data was changed
                this.selectPaymentMethod();
                var that = this;
                setPaymentMethodAction(this.messageContainer).done(
                    function () {
                        customerData.invalidate(['cart']);
                        $.mage.redirect(
                            window.checkoutConfig.payment[that.item.method].createUrl
                        );
                    }
                );

                return false;
            }
        }
    });
});
