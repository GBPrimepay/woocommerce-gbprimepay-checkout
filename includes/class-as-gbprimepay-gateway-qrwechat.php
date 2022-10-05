<?php
class AS_Gateway_Gbprimepay_Qrwechat extends WC_Payment_Gateway_eCheck
{
    public $environment;
    public $description2;
    public function __construct()
    {
        $this->id = 'gbprimepay_qrwechat';
        $this->method_title = __('GBPrimePay QR Wechat', 'gbprimepay-payment-gateways-qrwechat');
        $this->method_description = sprintf(__('QR Wechat integration with GBPrimePay'));
        $this->has_fields = true;
        $this->init_form_fields();
        // load settings
        $this->init_settings();
        $this->settings['enabled'] = AS_Gbprimepay_API::_can_enabled($this->settings['enabled']);
        $this->account_settings = get_option('gbprimepay_account_settings');
        $this->payment_settings = get_option('gbprimepay_payment_settings');
        $this->payment_settings_qrwechat = get_option('gbprimepay_payment_settings_qrwechat');
        $this->payment_settings_checkout = get_option('gbprimepay_payment_settings_checkout');
        $this->title = $this->payment_settings_qrwechat['title'];
        $this->description2 = $this->payment_settings_qrwechat['description2'];
        $this->environment = $this->account_settings['environment'];
    		$this->order_button_text = __( 'Continue to payment', 'gbprimepay-payment-gateways-qrwechat' );
        // AS_Gbprimepay_API::set_user_credentials($this->username, $this->password, $this->environment);
        update_option('gbprimepay_payment_settings_qrwechat', $this->settings);
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        // add_action( 'init', array( $this, 'my_check_order_status' ) );
        add_action( 'init', array( $this, 'qrwechat_callback_handler' ) );
        add_action( 'woocommerce_api_'. strtolower( get_class($this) ), array( $this, 'qrwechat_callback_handler' ) );
    }
    public function init_form_fields()
    {
        $this->form_fields = include('settings-formfields-gbprimepay-qrwechat.php');
    }
    /**
     * Check if this gateway is enabled
     */
    public function is_available()
    {
        if ($this->payment_settings_qrwechat['enabled'] === 'yes') {
            // return AS_Gbprimepay_API::get_credentials('qrwechat');
          if ($this->payment_settings_checkout['enabled'] === 'yes') {
            return false;
          }
        }
        return false;
    }
    public function payment_fields()
    {
        $user = wp_get_current_user();
        $total = WC()->cart->total;
        // if paying from order, we need to get total from order not cart.
        if (isset($_GET['pay_for_order']) && !empty($_GET['key'])) {
            $order = wc_get_order(wc_get_order_id_by_order_key(wc_clean($_GET['key'])));
            $total = $order->get_total();
        }
        if ($user->ID) {
            $user_email = get_user_meta($user->ID, 'billing_email', true);
            $user_email = $user_email ? $user_email : $user->user_email;
        } else {
            $user_email = '';
        }
        if (is_add_payment_method_page()) {
            $pay_button_text = __('Add Card', 'gbprimepay-payment-gateways-qrwechat');
            $total = '';
        } else {
            $pay_button_text = '';
        }
        $echocode = ''."\r\n";
        $echocode .= '<div style="padding:1.25em 0 0 0;margin-top:-1.25em;display:inline-block;"><img style="float: left;max-height: 2.8125em;" src="'.plugin_dir_url( __DIR__ ).'assets/images/qrwechat.png'.'" alt=""></div>'."\r\n";
        $echocode .= ''."\r\n";
        echo $echocode;
        echo '<div
			id="gbprimepay-payment-data"
			data-panel-label="' . esc_attr($pay_button_text) . '"
			data-description="'. esc_attr($this->description2) .'"
			data-email="' . esc_attr($user_email) . '"
			data-amount="' . esc_attr($total) . '">';
        if ( $this->description2 ) {
            echo '<p>'.wpautop( wp_kses_post( $this->description2) ).'</p>';
        }
        echo '</div>';
    }
    function process_payment( $order_id ) {
      global $woocommerce;
      $order = new WC_Order( $order_id );
      $order->add_order_note('Order created and status set to Pending payment.');
      $order->update_status('pending', __( 'Awaiting QR Wechat integration with GBPrimePay.', 'gbprimepay-payment-gateways' ));
      $redirect = add_query_arg(array('order_id' => $order->get_id(), 'key' => $order->get_order_key()), get_permalink(get_option('qrwechat_post_id')));
      return array(
        'result' => 'success',
        'redirect' => $redirect
      );
    }
    public function request_payment($order_id) {
      $order = wc_get_order($order_id);
      $callgetMerchantId = AS_Gbprimepay_API::getMerchantId();
      $callgenerateID = AS_Gbprimepay_API::generateID();
      $amount = $order->get_total();
      $itemamount = number_format((($amount * 100)/100), 2, '.', '');
      $itemdetail = 'Charge for order ' . $order->get_order_number();
      // $itemReferenceId = '00000'.$order->get_order_number();
      $itemReferenceId = ''.substr(time(), 4, 5).'00'.$order->get_order_number();
      $itemcustomerEmail = $order->get_billing_email();
      $customer_full_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
      $gbprimepayUser = new AS_Gbprimepay_User_Account(get_current_user_id(), $order); // get gbprimepay user obj
      $getgbprimepay_customer_id = $gbprimepayUser->get_gbprimepay_user_id();
      $account_settings = get_option('gbprimepay_account_settings');
      $return_url_qrwechat = $this->get_return_url($order);
      if($account_settings['environment']=='production'){
        $url = gbp_instances('URL_QRWECHAT_LIVE');
        $itempublicKey = $account_settings['live_public_key'];
        $itemsecret_key = $account_settings['live_secret_key'];
      }else{
        $url = gbp_instances('URL_QRWECHAT_TEST');
        $itempublicKey = $account_settings['test_public_key'];
        $itemsecret_key = $account_settings['test_secret_key'];
      }
      $itemresponseurl = $this->get_return_url($order);
      $itembackgroundurl = home_url()."/" . 'wc-api/AS_Gateway_Gbprimepay_Qrwechat/';
      $itemcustomerEmail = $order->get_billing_email();
      $itemcustomerAddress = '' . str_replace("<br/>", " ", $order->get_formatted_billing_address());
      $itemcustomerTelephone = '' . $order->get_billing_phone();
      // CryptoJS.SHA256
      $genChecksum = $itemamount . $itemReferenceId . $itemdetail;
      $itemchecksum = hash_hmac("sha256",$genChecksum,$itemsecret_key, false);
      $itemchecksumhex = base64_encode(hex2bin($itemchecksum));
      $field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"publicKey\"\r\n\r\n$itempublicKey\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$itemamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$itemReferenceId\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"detail\"\r\n\r\n$itemdetail\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"backgroundUrl\"\r\n\r\n$itembackgroundurl\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customerName\"\r\n\r\n$customer_full_name\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customerEmail\"\r\n\r\n$itemcustomerEmail\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customerAddress\"\r\n\r\n$itemcustomerAddress\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customerTelephone\"\r\n\r\n$itemcustomerTelephone\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined1\"\r\n\r\n$callgenerateID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined2\"\r\n\r\n$getgbprimepay_customer_id\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined3\"\r\n\r\n$itemReferenceId\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined4\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined5\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checksum\"\r\n\r\n$itemchecksumhex\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";
            AS_Gbprimepay::log(  'generateqrwechat Request: ' . print_r( $field, true ) );
      $qrwechatResponse = AS_Gbprimepay_API::sendQRCurl("$url", $field, 'POST');
      AS_Gbprimepay::log(  'qrwechat Response: ' . print_r( $qrwechatResponse, true ) );
        if (($qrwechatResponse=="Incomplete information") || ($qrwechatResponse=="Incorrect Checksum")) {
        }else{
          wp_enqueue_script(
            'gbprimepay-qrwechat-ajax-script',
            plugin_dir_url( __DIR__ ) . 'assets/js/gbprimepay-qrwechat-ajax.js',
            array('jquery')
          );
          wp_localize_script(
            'gbprimepay-qrwechat-ajax-script',
            'qrwechat_ajax_obj',
            array('ajaxurl' => admin_url('admin-ajax.php'))
          );
              ob_start();
              echo '<input type="hidden" id="gbprimepay-qrwechat-order-id" value="' . $order_id . '">';
              echo '<div class="qrwechat_display" id="gbprimepay-qrwechat-waiting-payment" style="display:block;">';
              echo '<img src="' . $qrwechatResponse . '"  style="padding:0px 0px 120px 0px;windth:100%;" class="aligncenter size-full" />';
              echo '</div>';
              echo '<div class="qrwechat_display" id="gbprimepay-qrwechat-payment-successful" style="display:none;">';
              echo $this->display_payment_success_message($return_url_qrwechat);
              echo '</div>';
              ob_end_flush();
              ob_flush();
              flush();
        }
    }
 	public function display_payment_success_message($return_url_qrwechat) {
 		return "
 			<center>
        <br><br>
        <img src='" . plugin_dir_url( __DIR__ ) .'assets/images/checked.png' . "'  style='padding:0px 0px 0px 0px;windth:100%;'>
 				<h3>GBPrimePay QR Wechat Payment Successful!</h3>
 				<img src='" . plugin_dir_url( __DIR__ ) .'assets/images/gbprimepay-logo-pay.png' . "' style='padding:0px 0px 0px 0px;windth:100%;'>
 				<br><br><br>
 				Pay with QR Wechat Payment has been received and \"Order is Now Complete\".
 				<br><br><br>
 				Redirecting...
 				<br><br><br><br><br><br>
 				<script>function redirect_to_shop() { window.location.href = '" . $return_url_qrwechat . "'; }</script>
 			</center>";
 	}
  public function qrwechat_callback_handler() {
    $postData = $_POST;
    $referenceNo = $postData['referenceNo'];
    $order_id = substr($postData['referenceNo'], 7);
              $order = wc_get_order($order_id);
                if ( isset( $postData['resultCode'] ) ) {
                  if ($postData['resultCode'] == '00') {
                                  $order->payment_complete($postData['gbpReferenceNo']);
                                  update_post_meta($order_id, 'Gbprimepay Charge ID', $postData['merchantDefined1']);
                                  $order->add_order_note(
                                    __( 'GBPrimePay QR Wechat Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                                    __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $postData['gbpReferenceNo'] . PHP_EOL .
                                    __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amount'])
                                  );
                          }else{
                                  $order->update_status( 'failed', sprintf( __( 'GBPrimePay QR Wechat Payment failed.', 'gbprimepay-payment-gateways' ) ) );
                          }
                      AS_Gbprimepay::log(  'QR Wechat Callback Handler: ' . print_r( $postData, true ) );
                }
  }
    public function send_failed_order_email($order_id)
    {
        $emails = WC()->mailer()->get_emails();
        if (!empty($emails) && !empty($order_id)) {
            $emails['WC_Email_Failed_Order']->trigger($order_id);
        }
    }
}
