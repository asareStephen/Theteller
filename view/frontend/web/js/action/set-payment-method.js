define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Payswitch_Theteller/js/form/form-builder',
        'Payswitch_Theteller/js/form/direct'
    ],
    function ($, quote, customerData, urlBuilder, storage, errorProcessor, customer, fullScreenLoader, formBuilder, direct) {
        'use strict';

        return function (messageContainer) {

            var serviceUrl,
                email,
                form;

            if (!customer.isLoggedIn()) {
                email = quote.guestEmail;
            } else {
                email = customer.customerData.email;
            }

            var initInline = function () {
                $('#ipay_lightbox_iframe').css('visibility', 'hidden');
                $('#ipay_lightbox').show();
            };

            serviceUrl = window.checkoutConfig.payment.theteller_payment.redirectUrl+'?email='+email;
            //var rooturl = window.checkoutConfig.payment.theteller_payment.redirectUrl;
            //alert(rooturl);
            //return false;
            //serviceUrl = "http://localhost:807/melcom/theteller/standard/redirect?email="+ email;
            fullScreenLoader.startLoader();
            
            $.ajax({
                url: serviceUrl,
                type: 'post',
                context: this,
                data: {isAjax: 1},
                dataType: 'json',
                success: function (response) {
                    if ($.type(response) === 'object' && !$.isEmptyObject(response)) {
                        $('#ipay_payment_form').remove();
                        form = formBuilder.build(
                            {
                                action: response.url,
                                fields: response.fields
                            }
                        );
                        if (response.inline === "1") {
                            initInline();
                            formBuilder.makeInline(form);
                        }
                        customerData.invalidate(['cart']);
                        form.submit();
                    } else {
                        fullScreenLoader.stopLoader();
                        alert({
                            content: $.mage.__('Sorry, something went wrong. Please try again.')
                        });
                    }
                },
                error: function (response) {
                    fullScreenLoader.stopLoader();
                    alert({
                        content: $.mage.__('Sorry, something went wrong. Please try again later.')
                    });
                }
            });
        };
    }
);