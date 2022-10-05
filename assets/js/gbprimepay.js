jQuery(function($) {
    'use strict';

    // window.console.log('gbprimepay');
    // onject to handle GB Prime Pay
    var se_gbprimepay_form = {
        init: function() {
            if ($('form.woocommerce-checkout').length) {
                this.form = $('form.woocommerce-checkout');
            }
            $('form.woocommerce-checkout').on('submit', this.onSubmit);
        },

        isGbprimepayDefault: function() {
            if ($('#wc-gbprimepay-payment-token-new').length) {
                $('#wc-gbprimepay-payment-token-new').attr('checked', true).trigger('change');
            }
        },

        block: function() {
            se_gbprimepay_form.form.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        },

        isGbprimepayChosen: function() {
            return $( '#payment_method_gbprimepay' ).is( ':checked' );
        },

        unblock: function() {
            se_gbprimepay_form.form.unblock();
        },

        onSubmit: function(e) {
            if (se_gbprimepay_form.isGbprimepayChosen()) {
                e.preventDefault();
                // se_gbprimepay_form.block(); // block it !!!!!!

                var card = $('#gbprimepay-card-number').val().replace(/ /g,'');
                var expires = $('#gbprimepay-card-expiry').val();
                var cvc =  $('#gbprimepay-card-cvc').val();
                // window.console.log('gbprimepay-form-submit');

            }
        }
    };

    se_gbprimepay_form.isGbprimepayDefault();
});
