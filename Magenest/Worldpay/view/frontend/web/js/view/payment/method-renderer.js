define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ], function (
        $,
        Component,
        rendererList
    ) {
        'use strict';

        window.WorldpayMagentoVersion = '1.0.0';
        var defaultComponent = 'Magenest_Worldpay/js/view/payment/method-renderer/card';
        var apmComponent = 'Magenest_Worldpay/js/view/payment/method-renderer/apm';
        var giropayComponent = 'Magenest_Worldpay/js/view/payment/method-renderer/giropay';
        var idealComponent = 'Magenest_Worldpay/js/view/payment/method-renderer/ideal';

        var methods = [
            {type: 'worldpay_payments_card', component: defaultComponent},
            {type: 'worldpay_payments_paypal', component: apmComponent},
            {type: 'worldpay_payments_giropay', component: giropayComponent},
            {type: 'worldpay_payments_alipay', component: apmComponent},
            {type: 'worldpay_payments_mistercash', component: apmComponent},
            {type: 'worldpay_payments_przelewy24', component: apmComponent},
            {type: 'worldpay_payments_paysafecard', component: apmComponent},
            {type: 'worldpay_payments_postepay', component: apmComponent},
            {type: 'worldpay_payments_qiwi', component: apmComponent},
            {type: 'worldpay_payments_sofort', component: apmComponent},
            {type: 'worldpay_payments_yandex', component: apmComponent},
            {type: 'worldpay_payments_ideal', component: idealComponent}
        ];

        $.each(methods, function (k, method) {
            rendererList.push(method);
        });

        return Component.extend({});
    }
);