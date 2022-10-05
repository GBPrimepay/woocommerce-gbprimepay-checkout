jQuery(function($) {
    'use strict';

    var bankcode = $('#gbprimepay_checkout-publicKey').val();
    var term = $('#gbprimepay_checkout-term').val();
    // onject to handle GB Prime Pay
    var se_gbprimepay_checkout_form = {
        init: function() {
            if ($('form.woocommerce-checkout').length) {
                this.form = $('form.woocommerce-checkout');
            }
            $('form.woocommerce-checkout').on('submit', this.onSubmit);
        },

        isGbprimepayDefault: function() {

        },

        block: function() {
            se_gbprimepay_checkout_form.form.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        },

        isGbprimepayChosen: function() {
            return $( '#payment_method_gbprimepay_checkout' ).is( ':checked' );
        },

        unblock: function() {
            se_gbprimepay_checkout_form.form.unblock();
        },

        onSubmit: function(e) {
            if (se_gbprimepay_checkout_form.isGbprimepayChosen()) {
                e.preventDefault();
                // se_gbprimepay_checkout_form.block(); // block it !!!!!!

                var bankcode = $('#gbprimepay_checkout-publicKey').val().replace(/ /g,'');
                var term = $('#gbprimepay_checkout-term').val();

            }
        },


    };





    window.addEventListener('load', function(){
      se_gbprimepay_checkout_form.isGbprimepayDefault();
    });

     
});
