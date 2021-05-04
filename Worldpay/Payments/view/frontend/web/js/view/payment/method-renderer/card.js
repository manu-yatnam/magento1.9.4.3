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
        var self;
        var currentCallback = false;
        return Component.extend({
            defaults: {
                template: 'Worldpay_Payments/form/card',
                cvc: '',
                iframeElement: false,
                paymentToken: false,
                canSaveCard: false,
                saveCard: true,
                useSavedCard: true,
                savedCards: [],
                threeDSMode: false,
                threeDSOn: false,
                cvcElement: false
            },
            initObservable: function () {
                self = this;
                this._super()
                    .observe('paymentToken')
                    .observe('useSavedCard')
                    .observe('cvc')
                    .observe('threeDSOn')
                    .observe('saveCard');
                this.initWorldpay();    

                this.saveCard.subscribe(function() {
                    self.initWorldpay();
                });

                if (this.savedCards.length && this.canSaveCard) {
                    this.paymentToken(this.savedCards[0].token);
                }

                return this;
            },
            showCVC: function(data) {
              return data.token === this.paymentToken();
            },
            initWorldpay: function(element) {
                this.canSaveCard = wpConfig.save_card || false;
                this.savedCards = wpConfig.saved_cards || [];
                this.ajaxGenerateOrderUrl = wpConfig.ajax_generate_order_url || '';
                this.threeDSMode = wpConfig.threeds_enabled || false;

                if (!this.savedCards.length || this.canSaveCard == false) {
                    this.useSavedCard(false);
                }
                if (element) {
                    this.iframeElement = element;
                }
                if (!this.iframeElement) {
                    return;
                }
                var iframe = $(this.iframeElement);
                $('#token_container').remove();
                currentCallback = iframe;
                Worldpay.useTemplateForm({
                    'clientKey': wpConfig.client_key,
                    'form':'co-payment-form',
                    'paymentSection': iframe,
                    'display':'inline',
                    'reusable': this.saveCard(),
                    'saveButton': false,
                    'callback': function(obj) {
                        if (currentCallback != iframe) return;
                        if (obj && obj.token) {
                            if (!self.paymentToken()) {
                                self.paymentToken(obj.token);
                                // if 3ds
                                if (wpConfig.threeds_enabled) {
                                    self.get3DSiFrame();
                                } else {
                                    self.placeOrder();
                                }
                            }
                        } else {
                            self.messageContainer.addErrorMessage({
                                message: "Error, please try again"
                            });
                        }
                    }
                });
            },
            createToken: function() {
                if (this.useSavedCard()) {
                    Worldpay.submitTemplateForm();
                } else {
                    this.paymentToken(false);
                    Worldpay.submitTemplateForm();
                }
            },
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        "paymentToken": this.paymentToken(),
                        "saveCard": this.saveCard()
                    }
                };
            },
            get3DSiFrame: function () {
                $.when(setPaymentInformationAction(self.messageContainer, {
                'method': self.getCode(),
                'additional_data': {
                    "paymentToken": self.paymentToken(),
                    "saveCard": self.saveCard()
                }
                })).done(function () {
                    fullScreenLoader.startLoader();
                    $.ajax({
                        type: 'POST',
                        url: self.ajaxGenerateOrderUrl,
                        data: {
                            token: self.paymentToken()
                        },
                        success: function (response) {
                            if (response.success) {
                                if (response.skipThreeDS) {
                                    window.location = response.redirectURL;
                                } else {
                                    self.show3DS(response.url, response.redirectURL, response.oneTime3DsToken);
                                }
                            } else {
                                self.messageContainer.addErrorMessage({
                                    message: response.error || "Error, please try again"
                                });
                            }
                            fullScreenLoader.stopLoader();
                        },
                        error: function (response) {
                            fullScreenLoader.stopLoader();
                            self.messageContainer.addErrorMessage({
                                message: "Error, please try again"
                            });
                            //REMOVE BELOW
                            self.isPaymentProcessing.reject(response);
                        }
                    });
                }).fail(function () {});
            },
            show3DS: function (url, redirectUrl, threedsToken) {
                window.magento2 = {t:this};
                this.threeDSOn(true);
                var iframeDiv = document.createElement('div');
                iframeDiv.id = 'worldpay-threeDsFrame';

                var iframe = document.createElement('iframe');
                iframe.style.width = "100%";
                iframe.style.height = "700px";
                iframe.setAttribute('frameBorder', '0');

                var html = '<form id="submitForm" method="post" action="'+ url +'"><input type="hidden" name="PaReq" value="'+ threedsToken +'"/><input type="hidden" id="termUrl" name="TermUrl" value="'+ redirectUrl +'"/></form>';

                iframeDiv.appendChild(iframe);
                document.getElementById('wp_threeds_zone').appendChild(iframeDiv);

                var MyIFrameDoc = (iframe.contentWindow || iframe.contentDocument);
                if (MyIFrameDoc.document) MyIFrameDoc = MyIFrameDoc.document;

                MyIFrameDoc.write(html);


                MyIFrameDoc.getElementById('submitForm').submit();
                // Show 3ds iframe
                // on redirect goto redirect
            },
            initWorldpayCVC: function(element) {
                if (element) {
                    self.cvcElement = element;
                }
                if (!self.cvcElement) {
                    return;
                }
                var cvcEl = $(self.cvcElement);
                if (self.paymentToken()) {
                    $('#token_container').remove();
                    currentCallback = cvcEl;
                    Worldpay.useTemplateForm({
                        'clientKey': wpConfig.client_key,
                        'form':'co-payment-form',
                        'paymentSection': cvcEl,
                        'display':'inline',
                        'type':'cvc',
                        'token': self.paymentToken(),
                        'saveButton': false,
                        'callback': function(obj) {
                            if (currentCallback != cvcEl) return;
                            if (obj && obj.cvc) {
                                if (wpConfig.threeds_enabled) {
                                    self.get3DSiFrame();
                                } else {
                                    self.placeOrder();
                                }
                            } else {
                                self.messageContainer.addErrorMessage({
                                    message: "Error, please try again"
                                });
                            }
                        }
                    });
                }
            },
        });
    }
);