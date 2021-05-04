/*browser:true*/
/*global define*/
define(
     [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'worldpay',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, Component, wp, setPaymentInformationAction, fullScreenLoadern, checkoutData, quote, fullScreenLoader) {
        'use strict';
        var wpConfig = window.checkoutConfig.payment.worldpay_payments;
        return Component.extend({
            defaults: {
                template: 'Worldpay_Payments/form/apm',
                paymentToken: false           
            },
            initObservable: function () {
                this._super()
                    .observe('paymentToken');
                return this;
            },
            createToken: function(element, event, extraInput) {
                this.paymentToken(false);
                this.isPlaceOrderActionAllowed(false);
                var self = this;
                // Create virtual form for WPJS
                var form = document.createElement("form");
                var i = document.createElement("input");
                i.setAttribute('type',"hidden");
                i.setAttribute('id',"wp-apm-name");
                i.setAttribute('data-worldpay', 'apm-name');
                i.setAttribute('value', this.getCode().replace('worldpay_payments_', ''));
                form.appendChild(i);

                i = document.createElement("input");
                i.setAttribute('type',"hidden");
                i.setAttribute('id',"wp-country-code");
                i.setAttribute('data-worldpay', 'country-code');
                i.setAttribute('value', wpConfig.country_code);
                form.appendChild(i);

                i = document.createElement("input");
                i.setAttribute('type',"hidden");
                i.setAttribute('id',"wp-country-code");
                i.setAttribute('data-worldpay', 'language-code');
                i.setAttribute('value', wpConfig.language_code);
                form.appendChild(i);

                Worldpay.reusable = false;

                if (extraInput) {
                    form.appendChild(extraInput);
                }

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
                                fullScreenLoader.startLoader();
                                $.ajax({
                                    type: 'POST',
                                    url: wpConfig.redirect_url,
                                    success: function (response) {
                                        if (response.success) {
                                            $.mage.redirect(response.redirectURL);
                                        } else {
                                            self.messageContainer.addErrorMessage({
                                                message: response.error || "Error, please try again"
                                            });
                                            fullScreenLoader.stopLoader();
                                        }
                                    },
                                    error: function (response) {
                                        fullScreenLoader.stopLoader();
                                        self.messageContainer.addErrorMessage({
                                            message: "Error, please try again"
                                        });
                                    }
                                });
                            }).fail(function () {
                                self.isPlaceOrderActionAllowed(true);
                            });
                        }
                      } else {
                        self.isPlaceOrderActionAllowed(true);
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
            },
            getName: function() {
                return this.item.title;
            }
        });
    }
);
