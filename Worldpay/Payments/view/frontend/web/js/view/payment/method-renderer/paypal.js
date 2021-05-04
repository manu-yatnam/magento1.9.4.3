/*browser:true*/
/*global define*/
define(
     [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'worldpay',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, Component, additionalValidators, wp, setPaymentInformationAction, fullScreenLoader) {
        'use strict';
        var wpConfig = window.checkoutConfig.payment.worldpay_payments;
        return Component.extend({
            defaults: {
                template: 'Worldpay_Payments/form/paypal',
                paymentToken: false
            },
            initObservable: function () {
                this._super()
                    .observe('paymentToken');  
                return this;
            },
            createPayPalToken: function() {
                this.paymentToken(false);
                this.isPlaceOrderActionAllowed(false);
                var self = this;
                // Create virtual form for WPJS
                var form = document.createElement("form");
                var i = document.createElement("input");
                i.setAttribute('type',"hidden");
                i.setAttribute('id',"wp-apm-name");
                i.setAttribute('data-worldpay', 'apm-name');
                i.setAttribute('value', 'paypal');
                form.appendChild(i);

                i = document.createElement("input");
                i.setAttribute('type',"hidden");
                i.setAttribute('id',"wp-country-code");
                i.setAttribute('data-worldpay', 'country-code');
                i.setAttribute('value', 'GB');
                form.appendChild(i);

                Worldpay.reusable = false;
                Worldpay.setClientKey(wpConfig.client_key);
                Worldpay.apm.createToken(form, function(resp, message){

                    if (resp != 200) {
                        self.isPlaceOrderActionAllowed(true);
                        alert(message.error.message);
                        return;
                    }
                    if (message && message.token) {
                        if (!self.paymentToken()) {
                            self.paymentToken(message.token);

                            $.when(setPaymentInformationAction(self.messageContainer, {
                            'method': self.getCode(),
                            'additional_data': {
                                "paymentToken": self.paymentToken()
                            }
                            })).done(function () {
                                $.mage.redirect(wpConfig.redirect_url);
                            }).fail(function () {
                                self.isPlaceOrderActionAllowed(true);
                            }); 
                        }
                      } else {
                        self.isPlaceOrderActionAllowed(true);
                        self.messageContainer.addErrorMessage({
                            message: "Error, please try again"
                        });
                      }
                });
            },
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        "paymentToken": this.paymentToken()
                    }
                };

            }
        });
    }
);