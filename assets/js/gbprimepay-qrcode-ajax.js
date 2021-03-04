var timeOutId = 0;


function check_order_completed() {

	jQuery.ajax({
		url: qrcode_ajax_obj.ajaxurl,
		type: 'post',
		data: {
			'action': 'check_qrcode_order_status',
			'order_id' : jQuery('#gbprimepay-qrcode-order-id').val()
		},
		success:function(response) {
			if (response == 0) {
				setTimeout(check_order_completed, 3000);
			} else if (response == 1) {
				jQuery('#gbprimepay-qrcode-waiting-payment').hide();
				jQuery('#gbprimepay-qrcode-payment-successful').show();

				clearTimeout(timeOutId);

				setTimeout(redirect_to_shop, 7000);
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			console.log(errorThrown);
		}
	});
}

jQuery(document).ready(check_order_completed());
