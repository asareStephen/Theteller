define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/set-billing-address',
        'Payswitch_Theteller/js/action/set-payment-method',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function ($, Component, setBillingAddressAction, setPaymentMethodAction, selectPaymentMethodAction, additionalValidators) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Payswitch_Theteller/payment/theteller'
            },

            continueToTheteller: function () {
                if (this.validate() && additionalValidators.validate()) {
                    this.selectPaymentMethod();
                    var setBillingInfo = setBillingAddressAction();
                    setBillingInfo.done(function() {
                        setPaymentMethodAction();
                    });
                    return false;
                }
            }
        });
    }
);