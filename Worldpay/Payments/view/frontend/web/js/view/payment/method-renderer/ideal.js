/*browser:true*/
/*global define*/
define(
     [
        'Worldpay_Payments/js/view/payment/method-renderer/apm'
    ],
    function (Apm) {
        'use strict';
        return Apm.extend({
            defaults: {
                template: 'Worldpay_Payments/form/ideal',
                paymentToken: false,
                shopperBankCode: ''
            },
            initObservable: function () {
                this._super()
                    .observe('paymentToken')
                    .observe('shopperBankCode');
                return this;
            },
            createIdealToken: function() {
                var i = document.createElement("input");
                i.setAttribute('type',"hidden");
                i.setAttribute('id',"wp-shopperbank-code");
                i.setAttribute('data-worldpay-apm', 'shopperBankCode');
                i.setAttribute('value', this.shopperBankCode());
                this.createToken(false, false, i);
            }
        });
    }
);
