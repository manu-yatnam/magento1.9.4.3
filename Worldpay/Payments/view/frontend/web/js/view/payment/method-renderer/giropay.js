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
                template: 'Worldpay_Payments/form/giropay',
                paymentToken: false,
                swiftCode: ''
            },
            initObservable: function () {
                this._super()
                    .observe('paymentToken')
                    .observe('swiftCode');
                return this;
            },
            createGiropayToken: function() {
                var i = document.createElement("input");
                i.setAttribute('type',"hidden");
                i.setAttribute('id',"wp-swift-code");
                i.setAttribute('data-worldpay-apm', 'swiftCode');
                i.setAttribute('value', this.swiftCode());
                this.createToken(false, false, i);
            }
        });
    }
);
