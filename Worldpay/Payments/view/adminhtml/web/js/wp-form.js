define([
	"jquery",
    "worldpay"
], function($, worldpay){
    "use strict";
    $.widget('mage.worldpayForm', {
    	 options: {
            clientKey: false
        },
        prepare : function(event, method) {
            if (method === 'worldpay_payments_card') {
                this.preparePayment();
            }
        },
        prepareCVC: function(token) {
            var self = this;
            $('#wp_iframe').html('');
            $('#wp_cvc_container').html('');
            Worldpay.useTemplateForm({
                'clientKey': this.options.clientKey,
                'form':'co-payment-form',
                'token':token,
                'paymentSection': $('#wp_cvc_container'),
                'display':'inline',
                'type':'cvc',
                'saveButton': false,
                'dimensions': {
                    width: 220,
                    height: 220
                },
                'validationError': function() {
                    $('body').trigger('processStop');
                },
                'beforeSubmit': function() {
                    return !$("#wp_new_card").is(":checked");             
                },
                'callback': function(obj) {
                  if ($("#wp_new_card").is(":checked")) return;
                  if (obj && obj.cvc) {
                    document.getElementById('wp_token').value = token;
                    order._realSubmit();
                  } else {
                    $('body').trigger('processStop');
                    alert("Error, please try again");
                  }
                  return false;
                }
            });
        },
        preparePayment: function() {
            $('#edit_form').off('submitOrder').on('submitOrder', this.submitAdminOrder.bind(this));
            var resuable = true;//$('#wp_save_card').is(':checked');
            var self = this;
            $('#wp_iframe').html('');
            $('#wp_cvc_container').html('');
            Worldpay.useTemplateForm({
                'clientKey': this.options.clientKey,
                'form':'co-payment-form',
                'paymentSection': $('#wp_iframe'),
                'display':'inline',
                'reusable': resuable,
                'saveButton': false,
                'validationError': function() {
                	$('body').trigger('processStop');
                },
                'beforeSubmit': function() {
                    return !$("#wp_new_card").is(":visible") || $("#wp_new_card").is(":checked");  
                },
                'callback': function(obj) {
                    if ($("#wp_new_card").is(":visible") && !$("#wp_new_card").is(":checked")) return;
                    if (obj && obj.token) {
                        document.getElementById('wp_token').value = obj.token;
                        order._realSubmit();
                    } else {
                     $('body').trigger('processStop');
                        alert("Error, please try again");
                    }
                    return false;
                }
            });
        },
        submitAdminOrder: function(event) {
            event.stopPropagation();
            Worldpay.submitTemplateForm();
            return false;
        },
        _create: function() {
            var self = this;
            $('#edit_form').on('changePaymentMethod', this.prepare.bind(this));
            $('.saved_tokens').on('change', function() {
                if ($("#wp_new_card").is(":checked")) {
                    $('#wp_iframe_container').show();
                    $('#wp_cvc_field').hide();
                    self.preparePayment();
                } else {
                    $('#wp_iframe_container').hide();
                    $('#wp_cvc_field').show();
                    $(this).parent().after($('#wp_cvc_container'));
                    self.prepareCVC($(this).val() + "");
                }
            });
            $('#edit_form').trigger(
                'changePaymentMethod',
                [
                    $('#edit_form').find(':radio[name="payment[method]"]:checked').val()
                ]
            );
            // $('#wp_save_card').on('change', function() {
            //  self.preparePayment();
            // });
            if ($('.saved_tokens').length) {
                $('.saved_tokens').first().click();
            }
        }
    });

    return $.mage.worldpayForm;
});
