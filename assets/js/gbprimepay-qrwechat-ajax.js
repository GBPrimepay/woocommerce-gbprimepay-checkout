var timeOutId = 0;


function check_order_completed() {

	jQuery.ajax({
		url: qrwechat_ajax_obj.ajaxurl,
		type: 'post',
		data: {
			'action': 'check_qrwechat_order_status',
			'order_id' : jQuery('#gbprimepay-qrwechat-order-id').val()
		},
		success:function(response) {
			if (response == 0) {
				setTimeout(check_order_completed, 3000);
			} else if (response == 1) {
				jQuery('#gbprimepay-qrwechat-waiting-payment').hide();
				jQuery('#gbprimepay-qrwechat-payment-successful').show();

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
