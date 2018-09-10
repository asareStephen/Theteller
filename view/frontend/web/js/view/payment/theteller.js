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
                type: 'theteller_payment',
                component: 'Payswitch_Theteller/js/view/payment/method-renderer/theteller-checkout'
            }
        );
        return Component.extend({});
    }
 );