<?php

class AS_Gateway_Gbprimepay_Checkout extends WC_Payment_Gateway_eCheck
{
    public $environment;
    public $description2;

    public function __construct()
    {
        $this->id = 'gbprimepay_checkout';
        $this->method_title = __('GBPrimePay Checkout', 'gbprimepay-payment-gateways-checkout');
        $this->method_description = sprintf(__('Pay securely with GBPrimePay Payments'));
        $this->has_fields = true;
        $this->supports = array(
            'products',
            'refunds'
        );

        $this->init_form_fields();

        // load settings
        $this->init_settings();


        $this->account_settings = get_option('gbprimepay_account_settings');
        $this->payment_settings_checkout = get_option('gbprimepay_payment_settings_checkout');
        $this->payment_settings = get_option('gbprimepay_payment_settings');
        $this->payment_settings_installment = get_option('gbprimepay_payment_settings_installment');
        $this->payment_settings_qrcode = get_option('gbprimepay_payment_settings_qrcode');
        $this->payment_settings_qrcredit = get_option('gbprimepay_payment_settings_qrcredit');
        $this->payment_settings_qrwechat = get_option('gbprimepay_payment_settings_qrwechat');
        $this->payment_settings_linepay = get_option('gbprimepay_payment_settings_linepay');
        $this->payment_settings_truewallet = get_option('gbprimepay_payment_settings_truewallet');
        $this->payment_settings_mbanking = get_option('gbprimepay_payment_settings_mbanking');
        $this->payment_settings_atome = get_option('gbprimepay_payment_settings_atome');
        $this->payment_settings_shopeepay = get_option('gbprimepay_payment_settings_shopeepay');
        $this->payment_settings_barcode = get_option('gbprimepay_payment_settings_barcode');

        $this->title = $this->payment_settings_checkout['title'];
        $this->description2 = $this->payment_settings_checkout['description2'];

        $this->environment = $this->account_settings['environment'];
        $this->order_button_text = __( 'Continue to payment', 'gbprimepay-payment-gateways-checkout' );







        update_option('gbprimepay_payment_settings_checkout', $this->settings);

        // Add hooks
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts')); // not yet use this
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action( 'init', array( $this, 'checkout_callback_handler' ) );
        add_action( 'woocommerce_api_'. strtolower( get_class($this) ), array( $this, 'checkout_callback_handler' ) );
        add_action( 'parse_query', array( $this, 'wtnerd_global_vars' ) );
    }
    
    function wtnerd_global_vars() {

        global $wtnerd;
        $wtnerd = array(
    
            'edition'  => 'nick',
            'channel'  => get_query_var('channel'),
            'tag'      => get_query_var('tag'),
    
        );
    
    }

    public function init_form_fields()
    {


        $this->form_fields = include('settings-formfields-gbprimepay-checkout.php');
    }

    /**
     * Check if this gateway is enabled
     */
    public function is_available()
    {
        if ($this->payment_settings_checkout['enabled'] === 'yes') {
            return AS_Gbprimepay_API::get_credentials('checkout');
        }
        return false;
    }

    public function payment_fields()
    {
        $user = wp_get_current_user();
        $total = WC()->cart->total;
        $this->$total = $total;

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
            $pay_button_text = __('Add Card', 'gbprimepay-payment-gateways-checkout');
            $total = '';
        } else {
            $pay_button_text = '';
        }

        echo '<div
			id="gbprimepay-payment-checkout-data"
			data-panel-label="' . esc_attr($pay_button_text) . '"
			data-description="'. esc_attr($this->description2) .'"
			data-email="' . esc_attr($user_email) . '"
			data-bankimg="' . plugin_dir_url( __DIR__ ).'assets/images/' . '"
			data-amount="' . esc_attr($total) . '">';

        if ( $this->description2 ) {
            echo '<p>'.wpautop( wp_kses_post( $this->description2) ).'</p>';
        }

        $this->form();

        echo '</div>';
    }

    function process_payment( $order_id ) {
      global $woocommerce;
      $order = new WC_Order( $order_id );

        try {
            $postData = $_POST;
            // echo '<pre>';print_r($postData);exit;

            if ($postData['payment_method']=="gbprimepay_checkout") {
              if (!empty($postData['payment_method'])) {





                  $order->add_order_note('Order created and status set to Pending payment.');
                  $order->update_status('pending', __( 'Awaiting GBPrimePay Checkout.', 'gbprimepay-payment-gateways' ));



                  $init_gbp = array();
                  $checkout_select_method = $postData['gbprimepay_checkout-select_method'];
                  $checkout_sort_method = $postData['gbprimepay_checkout-sort_method'];

                    $account_settings = get_option('gbprimepay_account_settings');
                    $payment_settings = get_option('gbprimepay_payment_settings');
                    $payment_settings_checkout = get_option('gbprimepay_payment_settings_checkout');
                    $payment_settings_installment = get_option('gbprimepay_payment_settings_installment');
                    $payment_settings_qrcode = get_option('gbprimepay_payment_settings_qrcode');
                    $payment_settings_qrcredit = get_option('gbprimepay_payment_settings_qrcredit');
                    $payment_settings_qrwechat = get_option('gbprimepay_payment_settings_qrwechat');
                    $payment_settings_linepay = get_option('gbprimepay_payment_settings_linepay');
                    $payment_settings_truewallet = get_option('gbprimepay_payment_settings_truewallet');
                    $payment_settings_mbanking = get_option('gbprimepay_payment_settings_mbanking');
                    $payment_settings_atome = get_option('gbprimepay_payment_settings_atome');
                    $payment_settings_shopeepay = get_option('gbprimepay_payment_settings_shopeepay');
                    $payment_settings_barcode = get_option('gbprimepay_payment_settings_barcode');

                    if ($account_settings['environment'] === 'prelive') {
                        $checkout_url = gbp_instances('URL_CHECKOUT_TEST');
                    } else {
                        $checkout_url = gbp_instances('URL_CHECKOUT_LIVE');
                    }

                    if ($account_settings['environment'] === 'prelive') {
                        $init_gbp['environment']['prelive'] = array(
                            "public_key" => $account_settings['test_public_key'],
                            "secret_key" => $account_settings['test_secret_key'],
                            "token_key" => $account_settings['test_token_key'],
                        ); 
                    } else {
                        $init_gbp['environment']['production'] = array(
                            "public_key" => $account_settings['live_public_key'],
                            "secret_key" => $account_settings['live_secret_key'],
                            "token_key" => $account_settings['live_token_key'],
                        ); 
                    }
                    if ($payment_settings['enabled'] === 'yes') {
                        $init_gbp['init_gateways']['creditcard'] = array(
                            "enabled" => $payment_settings['enabled'],
                            "display" => $payment_settings['title'],
                        ); 
                    }
                    if ($payment_settings_installment['enabled'] === 'yes') {
                        $init_gbp['init_gateways']['installment'] = array(
                            "enabled" => $payment_settings_installment['enabled'],
                            "display" => $payment_settings_installment['title'],
                        ); 
                        $init_gbp['init_gateways']['installment_options'] = array(
                            "kasikorn_installment_term" => $payment_settings_installment['kasikorn_installment_term'],
                            "krungthai_installment_term" => $payment_settings_installment['krungthai_installment_term'],
                            "thanachart_installment_term" => $payment_settings_installment['thanachart_installment_term'],
                            "ayudhya_installment_term" => $payment_settings_installment['ayudhya_installment_term'],
                            "firstchoice_installment_term" => $payment_settings_installment['firstchoice_installment_term'],
                            "scb_installment_term" => $payment_settings_installment['scb_installment_term'],
                            "bbl_installment_term" => $payment_settings_installment['bbl_installment_term'],
                        ); 
                    }
                    if ($payment_settings_qrcode['enabled'] === 'yes') {
                        $init_gbp['init_gateways']['qrcode'] = array(
                            "enabled" => $payment_settings_qrcode['enabled'],
                            "display" => $payment_settings_qrcode['title'],
                        ); 
                    }
                    if ($payment_settings_qrcredit['enabled'] === 'yes') {
                        $init_gbp['init_gateways']['qrcredit'] = array(
                            "enabled" => $payment_settings_qrcredit['enabled'],
                            "display" => $payment_settings_qrcredit['title'],
                        ); 
                    }

                    if ($payment_settings_qrwechat['enabled'] === 'yes') {
                        $init_gbp['init_gateways']['qrwechat'] = array(
                            "enabled" => $payment_settings_qrwechat['enabled'],
                            "display" => $payment_settings_qrwechat['title'],
                        ); 
                    }

                    if ($payment_settings_linepay['enabled'] === 'yes') {
                        $init_gbp['init_gateways']['linepay'] = array(
                            "enabled" => $payment_settings_linepay['enabled'],
                            "display" => $payment_settings_linepay['title'],
                        ); 
                    }

                    if ($payment_settings_truewallet['enabled'] === 'yes') {
                        $init_gbp['init_gateways']['truewallet'] = array(
                            "enabled" => $payment_settings_truewallet['enabled'],
                            "display" => $payment_settings_truewallet['title'],
                        ); 
                    }

                    if ($payment_settings_mbanking['enabled'] === 'yes') {
                        $init_gbp['init_gateways']['mbanking'] = array(
                            "enabled" => $payment_settings_mbanking['enabled'],
                            "display" => $payment_settings_mbanking['title'],
                        ); 
                    }
                    if ($payment_settings_atome['enabled'] === 'yes') {
                        $init_gbp['init_gateways']['atome'] = array(
                            "enabled" => $payment_settings_atome['enabled'],
                            "display" => $payment_settings_atome['title'],
                        ); 
                    }
                    if ($payment_settings_shopeepay['enabled'] === 'yes') {
                        $init_gbp['init_gateways']['shopeepay'] = array(
                            "enabled" => $payment_settings_shopeepay['enabled'],
                            "display" => $payment_settings_shopeepay['title'],
                        ); 
                    }

                    if ($payment_settings_barcode['enabled'] === 'yes') {
                        $init_gbp['init_gateways']['barcode'] = array(
                            "enabled" => $payment_settings_barcode['enabled'],
                            "display" => $payment_settings_barcode['title'],
                        ); 
                    }

                    $amount = $order->get_total();
                    $checkout_amount = number_format((($amount * 100)/100), 2, '.', '');
                    $checkout_detail = 'Charge for order ' . $order->get_order_number();
                    $checkout_first_name = '' . $order->get_billing_first_name();
                    $checkout_last_name = '' . $order->get_billing_last_name();
                    $checkout_customerName = '' . $order->get_billing_first_name(). ' ' .$order->get_billing_last_name();
                    $checkout_customerEmail = '' . $order->get_billing_email();
                    $checkout_customerAddress = '' . str_replace("<br/>", " ", $order->get_formatted_billing_address());
                    $checkout_customerTelephone = '' . $order->get_billing_phone();
                    $checkout_referenceNo = ''.substr(time(), 4, 5).'00'.$order->get_order_number();
                    $checkout_cancelUrl = $order->get_cancel_order_url($order->get_cancel_endpoint());
                    $checkout_failedUrl = $order->get_checkout_payment_url();
                    $checkout_responseUrl = $this->get_return_url($order);
                    $checkout_backgroundUrl = home_url()."/" . 'wc-api/AS_Gateway_Gbprimepay_Checkout/';
                    $checkout_platform = gbp_instances('PLATFORM');
                    $checkout_mode = gbp_instances('MODE');
                    $checkout_status = gbp_instances('STATUS');
                    $checkout_environment = $account_settings['environment'];
                    $checkout_language = AS_Gbprimepay_API::getCurrentLanguage();
                    $checkout_domain = AS_Gbprimepay_API::getDomain();
                    $callgenerateID = AS_Gbprimepay_API::generateID();
                    $checkout_otpCode = 'Y';

                    $account_settings = get_option('gbprimepay_account_settings');
                    if ($account_settings['environment'] === 'prelive') {
                        $url = gbp_instances('URL_MERCHANT_TEST');
                    } else {
                        $url = gbp_instances('URL_MERCHANT_LIVE');
                    }
                    $merchant_data = AS_Gbprimepay_API::sendMerchantCurl("$url", [], 'GET');

                    $product_data = array();
                        $i = 0;
                        foreach ( $order->get_items() as $item_id => $item ) {
                            $product = $item->get_product();
                            $name = $item->get_name();
                            $quantity = $item->get_quantity();
                            $subtotal = $item->get_subtotal();
                            $price = ($item->get_subtotal() / $item->get_quantity());
                            $amount = ($item->get_subtotal() + $item->get_subtotal_tax());
                            $tax = $item->get_subtotal_tax();
                                $image_url = false;
                                if ($product->get_image_id() > 0) {
                                    $image_id  = $product->get_image_id();
                                    $image_url = wp_get_attachment_image_url( $image_id, 'medium', false);
                                }
                                    $product_data['products_items_'.$i] = array(
                                        "items_name" => $name,
                                        "items_images" => $image_url,
                                        "items_price" => $price,
                                        "items_quantity" => $quantity,
                                        "items_subtotal" => $subtotal,
                                        "items_tax" => $tax,
                                        "items_total" => $amount,
                                    ); 
                        $i++;
                        }

                        $payment_data = array(
                            "payment_amount" => $checkout_amount,
                            "payment_referenceNo" => $checkout_referenceNo,
                            "payment_otpCode" => $checkout_otpCode,
                            "payment_detail" => $checkout_detail,
                            "payment_cancelUrl" => $checkout_cancelUrl,
                            "payment_failedUrl" => $checkout_failedUrl,
                            "payment_responseUrl" => $checkout_responseUrl,
                            "payment_backgroundUrl" => $checkout_backgroundUrl,
                            "payment_customerName" => $checkout_customerName,
                            "payment_customerEmail" => $checkout_customerEmail,
                            "payment_customerAddress" => $checkout_customerAddress,
                            "payment_customerTelephone" => $checkout_customerTelephone,
                            "payment_merchantDefined1" => $callgenerateID,
                            "payment_merchantDefined2" => '',
                            "payment_merchantDefined3" => $checkout_referenceNo,
                            "payment_merchantDefined4" => '',
                            "payment_merchantDefined5" => '',
                        ); 
                        $customer_data = array(
                            "customer_first_name" => $checkout_first_name,
                            "customer_last_name" => $checkout_last_name,
                            "customer_name" => $checkout_customerName,
                            "customer_email" => $checkout_customerEmail,
                            "customer_address" => $checkout_customerAddress,
                            "customer_telephone" => $checkout_customerTelephone,
                        ); 

                        $currency_data = AS_Gbprimepay_API::genCurrencyDATA($checkout_language,$merchant_data);

                    $RedirectURL =  add_query_arg(
                                    array(
                                        'page' => rawurlencode($checkout_url),
                                        'serialID' => rawurlencode($callgenerateID)
                                    ), WP_PLUGIN_URL."/" . plugin_basename( dirname(__FILE__) ) . '/redirect/index.php');


                    
      $ret = array();

      $ret['page'] = $checkout_url;
      $ret['serialID'] = $callgenerateID;
      $ret['platform'] = $checkout_platform;
      $ret['mode'] = $checkout_mode;
      $ret['status'] = $checkout_status;
      $ret['method'] = $checkout_select_method;
      $ret['sort'] = json_encode($checkout_sort_method);
      $ret['environment'] = $checkout_environment;
      $ret['language'] = $checkout_language;
      $ret['domain'] = $checkout_domain;
      $ret['init_gbp'] = json_encode($init_gbp);
      $ret['merchant_data'] = json_encode($merchant_data);
      $ret['product_data'] = json_encode($product_data);
      $ret['payment_data'] = json_encode($payment_data);
      $ret['customer_data'] = json_encode($customer_data);
      $ret['currency_data'] = json_encode($currency_data);
      $ret['url_complete'] = $checkout_responseUrl;
      $ret['url_callback'] = $checkout_backgroundUrl;
      $ret['url_cancel'] = $checkout_cancelUrl;
      $ret['url_error'] = $checkout_failedUrl;

  
                                    // json_encode($ret)
                                    
                                    $payload = json_encode($ret);
                                    $file = dirname(__FILE__). '/redirect/payload-'.$callgenerateID.'.json';
                                    file_put_contents($file, $payload); 
                    
                    // $RedirectURL =  WP_PLUGIN_URL."/" . plugin_basename( dirname(__FILE__) ) . '/redirect/checkout.php';
                    // echo $RedirectURL;
                    // exit;


                    // $payload = WC()->session->get('gbprimepay_payload');
                    // echo 'post<pre>';print_r($payload);echo '</pre>';
                    // exit;
                                    return array(
                                      'result' => 'success',
                                      'redirect' => $RedirectURL
                                    );
            }
            }



        } catch (Exception $e) {
            wc_add_notice($e->getMessage(), 'error');
            if ($order->has_status(array('pending', 'failed'))) {
                $this->send_failed_order_email($order_id);
            }

            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }
    }


    public function payment_scripts()
    {
        if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order']) && !is_add_payment_method_page()) {
            return;
        }
        wp_enqueue_script('gbprimepay_checkout', plugin_dir_url( __DIR__ ) .'assets/js/gbprimepay-checkout.js', '', '', true );

    }



  public function checkout_callback_handler() {
      
    $account_settings = get_option('gbprimepay_account_settings');
    if ($account_settings['environment'] === 'prelive') {
        $checkout_url = gbp_instances('URL_CHECKOUT_TEST');
    } else {
        $checkout_url = gbp_instances('URL_CHECKOUT_LIVE');
    }

if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') == 0){
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if(strcasecmp($contentType, 'application/json') == 0){
    
    $raw_post = @file_get_contents( 'php://input' );
	$payload  = json_decode( $raw_post );
    $paymentType = $payload->{'paymentType'};

    $referenceNo = $payload->{'referenceNo'};
    $order_id = substr($payload->{'referenceNo'}, 7);
    $currencyISO = AS_Gbprimepay_API::getCurrencyISObyCode($payload->{'currencyCode'});
    $order = wc_get_order($order_id);
    $ordertxt = '';
    if ($order){$ordertxt = $order->get_id();}

    if($paymentType=='Q'){
    // Qr Code
    if ( isset( $payload->{'resultCode'} ) ) {
        if ($payload->{'resultCode'} == '00') {
                $order->payment_complete($payload->{'gbpReferenceNo'});
                update_post_meta($order_id, 'Gbprimepay Charge ID', $payload->{'merchantDefined1'});
                $order->add_order_note(
                  __( 'GBPrimePay QR Code Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                  __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $payload->{'gbpReferenceNo'} . PHP_EOL .
                  __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($payload->{'amount'}, array('currency' => ''.$currencyISO))
                );

// checkout_afterpay_url
$checkoutmethod = 'qrcode';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $payload->{'merchantDefined1'};
$checkoutID = $payload->{'merchantDefined5'};
$checkoutgbpReferenceNo = $payload->{'gbpReferenceNo'};
$checkoutamount = $payload->{'amount'};
$checkoutdate = $payload->{'date'};
$checkouttime = $payload->{'time'};
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $field, 'POST');


AS_Gbprimepay::log(  'QR Code Return Handler: ' .$url.'\r\n\r\n'. print_r( $checkoutReturn, true ) );
        }else{
            if ($order->has_status(array('pending', 'failed'))) {
                $order->update_status( 'failed', sprintf( __( 'GBPrimePay QR Code Payment failed.', 'gbprimepay-payment-gateways' ) ) );
            }
        }
    AS_Gbprimepay::log(  'QR Code Callback Handler: ' . print_r( $payload, true ) );

}
    }
    if($paymentType=='A'){
    // Atome
    if ( isset( $payload->{'resultCode'} ) ) {
        if ($payload->{'resultCode'} == '00') {
                $order->payment_complete($payload->{'gbpReferenceNo'});
                update_post_meta($order_id, 'Gbprimepay Charge ID', $payload->{'merchantDefined1'});
                $order->add_order_note(
                  __( 'GBPrimePay Atome Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                  __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $payload->{'gbpReferenceNo'} . PHP_EOL .
                  __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($payload->{'amount'}, array('currency' => ''.$currencyISO))
                );

// checkout_afterpay_url
$checkoutmethod = 'atome';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $payload->{'merchantDefined1'};
$checkoutID = $payload->{'merchantDefined5'};
$checkoutgbpReferenceNo = $payload->{'gbpReferenceNo'};
$checkoutamount = $payload->{'amount'};
$checkoutdate = $payload->{'date'};
$checkouttime = $payload->{'time'};
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $field, 'POST');


AS_Gbprimepay::log(  'Atome Return Handler: ' .print_r( $checkoutReturn, true ) );
        }else{
            if ($order->has_status(array('pending', 'failed'))) {
                $order->update_status( 'failed', sprintf( __( 'GBPrimePay Atome Payment failed.', 'gbprimepay-payment-gateways' ) ) );
            }
        }
    AS_Gbprimepay::log(  'Atome Callback Handler: ' . print_r( $payload, true ) );

}

    }
    if($paymentType=='S'){
    // ShopeePay
    if ( isset( $payload->{'resultCode'} ) ) {
        if ($payload->{'resultCode'} == '00') {
                $order->payment_complete($payload->{'gbpReferenceNo'});
                update_post_meta($order_id, 'Gbprimepay Charge ID', $payload->{'merchantDefined1'});
                $order->add_order_note(
                  __( 'GBPrimePay ShopeePay Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                  __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $payload->{'gbpReferenceNo'} . PHP_EOL .
                  __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($payload->{'amount'}, array('currency' => ''.$currencyISO))
                );

// checkout_afterpay_url
$checkoutmethod = 'shopeepay';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $payload->{'merchantDefined1'};
$checkoutID = $payload->{'merchantDefined5'};
$checkoutgbpReferenceNo = $payload->{'gbpReferenceNo'};
$checkoutamount = $payload->{'amount'};
$checkoutdate = $payload->{'date'};
$checkouttime = $payload->{'time'};
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $field, 'POST');


AS_Gbprimepay::log(  'ShopeePay Return Handler: ' .print_r( $checkoutReturn, true ) );
        }else{
            if ($order->has_status(array('pending', 'failed'))) {
                $order->update_status( 'failed', sprintf( __( 'GBPrimePay ShopeePay Payment failed.', 'gbprimepay-payment-gateways' ) ) );
            }
        }
    AS_Gbprimepay::log(  'ShopeePay Callback Handler: ' . print_r( $payload, true ) );

}

    }
    if($paymentType=='V'){
    // Qr Visa
    if ( isset( $payload->{'resultCode'} ) ) {
        if ($payload->{'resultCode'} == '00') {
                $order->payment_complete($payload->{'gbpReferenceNo'});
                update_post_meta($order_id, 'Gbprimepay Charge ID', $payload->{'merchantDefined1'});
                $order->add_order_note(
                  __( 'GBPrimePay QR Visa Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                  __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $payload->{'gbpReferenceNo'} . PHP_EOL .
                  __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($payload->{'amount'}, array('currency' => ''.$currencyISO))
                );

// checkout_afterpay_url
$checkoutmethod = 'qrcredit';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $payload->{'merchantDefined1'};
$checkoutID = $payload->{'merchantDefined5'};
$checkoutgbpReferenceNo = $payload->{'gbpReferenceNo'};
$checkoutamount = $payload->{'amount'};
$checkoutdate = $payload->{'date'};
$checkouttime = $payload->{'time'};
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $field, 'POST');


AS_Gbprimepay::log(  'QR Visa Return Handler: ' .print_r( $checkoutReturn, true ) );
        }else{
            if ($order->has_status(array('pending', 'failed'))) {
                $order->update_status( 'failed', sprintf( __( 'GBPrimePay QR Visa Payment failed.', 'gbprimepay-payment-gateways' ) ) );
            }
        }
    AS_Gbprimepay::log(  'QR Visa Callback Handler: ' . print_r( $payload, true ) );

}

    }
    if($paymentType=='B'){
    // Bill Payment
    if ( isset( $payload->{'resultCode'} ) ) {
        if ($payload->{'resultCode'} == '00') {
                $order->payment_complete($payload->{'gbpReferenceNo'});
                update_post_meta($order_id, 'Gbprimepay Charge ID', $payload->{'merchantDefined1'});
                $order->add_order_note(
                  __( 'GBPrimePay Bill Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                  __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $payload->{'gbpReferenceNo'} . PHP_EOL .
                  __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($payload->{'amount'}, array('currency' => ''.$currencyISO))
                );

// checkout_afterpay_url
$checkoutmethod = 'barcode';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $payload->{'merchantDefined1'};
$checkoutID = $payload->{'merchantDefined5'};
$checkoutgbpReferenceNo = $payload->{'gbpReferenceNo'};
$checkoutamount = $payload->{'amount'};
$checkoutdate = $payload->{'date'};
$checkouttime = $payload->{'time'};
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $field, 'POST');


AS_Gbprimepay::log(  'Bill Payment Return Handler: ' .$url.'\r\n\r\n'. print_r( $checkoutReturn, true ) );

        }else{
            if ($order->has_status(array('pending', 'failed'))) {
                $order->update_status( 'failed', sprintf( __( 'GBPrimePay Bill Payment failed.', 'gbprimepay-payment-gateways' ) ) );
            }
        }
    AS_Gbprimepay::log(  'Bill Payment Callback Handler: ' . print_r( $payload, true ) );

}
    }
    }else{
        $postData = $_POST;
        $referenceNo = $postData['referenceNo'];
        $paymentType = $postData['paymentType'];
        $order_id = substr($postData['referenceNo'], 7);
        $currencyISO = AS_Gbprimepay_API::getCurrencyISObyCode($postData['currencyCode']);
        $order = wc_get_order($order_id);
        $ordertxt = '';
        if ($order){$ordertxt = $order->get_id();}
    if($paymentType=='C'){
    // Credit Card 
                  if ( isset( $postData['resultCode'] ) ) {
                    if ($postData['resultCode'] == '00') {
                            $order->payment_complete($postData['gbpReferenceNo']);
                            update_post_meta($order_id, 'Gbprimepay Charge ID', $postData['merchantDefined1']);
                                    $order->add_order_note(
                                        __( '3-D Secure Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                                        __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $postData['gbpReferenceNo'] . PHP_EOL .
                                        __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amount'], array('currency' => ''.$currencyISO))
                                    );

// checkout_afterpay_url
$checkoutmethod = 'creditcard';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $postData['merchantDefined1'];
$checkoutcardNo = $postData['cardNo'];
$checkoutID = $postData['merchantDefined5'];
$checkoutgbpReferenceNo = $postData['gbpReferenceNo'];
$checkoutamount = $postData['amount'];
$checkoutdate = $postData['date'];
$checkouttime = $postData['time'];
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"cardNo\"\r\n\r\n$checkoutcardNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $field, 'POST');


AS_Gbprimepay::log(  'Secure Callback Return Handler: ' .$url.'\r\n\r\n'. print_r( $checkoutReturn, true ) );

                    }else{
                        if ($order->has_status(array('pending', 'failed'))) {
                            $order->update_status( 'failed', sprintf( __( '3-D Secure Payment failed.', 'gbprimepay-payment-gateways' ) ) );
                        }
                    }

                AS_Gbprimepay::log(  'Secure Callback Handler: ' . print_r( $postData, true ) );

            }
    }
    if($paymentType=='I'){
    // Credit Card Installment 
                if ( isset( $postData['resultCode'] ) ) {
                    if ($postData['resultCode'] == '00') {
                            $order->payment_complete($postData['gbpReferenceNo']);
                            update_post_meta($order_id, 'Gbprimepay Charge ID', $postData['merchantDefined1']);
                            $order->add_order_note(
                              __( 'GBPrimePay Credit Card Installment Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                              __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $postData['gbpReferenceNo'] . PHP_EOL .
                              __( 'Monthly: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amountPerMonth']) .' x '. $postData['payMonth'] . PHP_EOL .
                              __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amount'], array('currency' => ''.$currencyISO))
                            );
// checkout_afterpay_url
$checkoutmethod = 'installment';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $postData['merchantDefined1'];
$checkoutcardNo = $postData['cardNo'];
$checkoutID = $postData['merchantDefined5'];
$checkoutgbpReferenceNo = $postData['gbpReferenceNo'];
$checkoutamount = $postData['amount'];
$checkoutdate = $postData['date'];
$checkouttime = $postData['time'];
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"cardNo\"\r\n\r\n$checkoutcardNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $field, 'POST');


AS_Gbprimepay::log(  'Credit Card Installment Callback Return Handler: ' .$url.'\r\n\r\n'. print_r( $checkoutReturn, true ) );
                    }else{
                        if ($order->has_status(array('pending', 'failed'))) {
                            $order->update_status( 'failed', sprintf( __( 'GBPrimePay Credit Card Installment Payment failed.', 'gbprimepay-payment-gateways' ) ) );
                        }
                    }
                AS_Gbprimepay::log(  'Credit Card Installment Callback Handler: ' . print_r( $postData, true ) );

          }
    }
    if($paymentType=='W'){
    // Qr Wechat
    if ( isset( $postData['resultCode'] ) ) {
        if ($postData['resultCode'] == '00') {
                        $order->payment_complete($postData['gbpReferenceNo']);
                        update_post_meta($order_id, 'Gbprimepay Charge ID', $postData['merchantDefined1']);
                        $order->add_order_note(
                          __( 'GBPrimePay QR Wechat Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                          __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $postData['gbpReferenceNo'] . PHP_EOL .
                          __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amount'], array('currency' => ''.$currencyISO))
                        );

// checkout_afterpay_url
$checkoutmethod = 'qrwechat';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $postData['merchantDefined1'];
$checkoutID = $postData['merchantDefined5'];
$checkoutgbpReferenceNo = $postData['gbpReferenceNo'];
$checkoutamount = $postData['amount'];
$checkoutdate = $postData['date'];
$checkouttime = $postData['time'];
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $field, 'POST');


AS_Gbprimepay::log(  'QR Wechat Return Handler: ' .$url.'\r\n\r\n'. print_r( $checkoutReturn, true ) );
                }else{
                    if ($order->has_status(array('pending', 'failed'))) {
                        $order->update_status( 'failed', sprintf( __( 'GBPrimePay QR Wechat Payment failed.', 'gbprimepay-payment-gateways' ) ) );
                    }
                }
            AS_Gbprimepay::log(  'QR Wechat Callback Handler: ' . print_r( $postData, true ) );

      }
    }
    if($paymentType=='L'){
    // Rabbit Line Pay
    if ( isset( $postData['resultCode'] ) ) {
        if ($postData['resultCode'] == '00') {
                        $order->payment_complete($postData['gbpReferenceNo']);
                        update_post_meta($order_id, 'Gbprimepay Charge ID', $postData['merchantDefined1']);
                        $order->add_order_note(
                          __( 'GBPrimePay Rabbit Line Pay Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                          __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $postData['gbpReferenceNo'] . PHP_EOL .
                          __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amount'], array('currency' => ''.$currencyISO))
                        );

// checkout_afterpay_url
$checkoutmethod = 'linepay';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $postData['merchantDefined1'];
$checkoutID = $postData['merchantDefined5'];
$checkoutgbpReferenceNo = $postData['gbpReferenceNo'];
$checkoutamount = $postData['amount'];
$checkoutdate = $postData['date'];
$checkouttime = $postData['time'];
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $field, 'POST');


AS_Gbprimepay::log(  'Rabbit Line Pay Return Handler: ' .$url.'\r\n\r\n'. print_r( $checkoutReturn, true ) );
                }else{
                    if ($order->has_status(array('pending', 'failed'))) {
                        $order->update_status( 'failed', sprintf( __( 'GBPrimePay Rabbit Line Pay Payment failed.', 'gbprimepay-payment-gateways' ) ) );
                    }
                }
            AS_Gbprimepay::log(  'Rabbit Line Pay Callback Handler: ' . print_r( $postData, true ) );

      }
    }
    if($paymentType=='T'){
    // TrueMoney Wallet
    if ( isset( $postData['resultCode'] ) ) {
        if ($postData['resultCode'] == '00') {
                        $order->payment_complete($postData['gbpReferenceNo']);
                        update_post_meta($order_id, 'Gbprimepay Charge ID', $postData['merchantDefined1']);
                        $order->add_order_note(
                          __( 'GBPrimePay TrueMoney Wallet Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                          __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $postData['gbpReferenceNo'] . PHP_EOL .
                          __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amount'], array('currency' => ''.$currencyISO))
                        );

// checkout_afterpay_url
$checkoutmethod = 'truewallet';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $postData['merchantDefined1'];
$checkoutID = $postData['merchantDefined5'];
$checkoutgbpReferenceNo = $postData['gbpReferenceNo'];
$checkoutamount = $postData['amount'];
$checkoutdate = $postData['date'];
$checkouttime = $postData['time'];
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $field, 'POST');


AS_Gbprimepay::log(  'TrueMoney Wallet Return Handler: ' .$url.'\r\n\r\n'. print_r( $checkoutReturn, true ) );
                }else{
                    if ($order->has_status(array('pending', 'failed'))) {
                        $order->update_status( 'failed', sprintf( __( 'GBPrimePay TrueMoney Wallet Payment failed.', 'gbprimepay-payment-gateways' ) ) );
                    }
                }
            AS_Gbprimepay::log(  'TrueMoney Wallet Callback Handler: ' . print_r( $postData, true ) );

      }
    }
    if($paymentType=='M'){
    // Mobile Banking
    if ( isset( $postData['resultCode'] ) ) {
        if ($postData['resultCode'] == '00') {
                        $order->payment_complete($postData['gbpReferenceNo']);
                        update_post_meta($order_id, 'Gbprimepay Charge ID', $postData['merchantDefined1']);
                        $order->add_order_note(
                          __( 'GBPrimePay Mobile Banking Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                          __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $postData['gbpReferenceNo'] . PHP_EOL .
                          __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amount'], array('currency' => ''.$currencyISO))
                        );

// checkout_afterpay_url
$checkoutmethod = 'mbanking';
$checkoutshoprefNo = $ordertxt;
$checkoutserialID = $postData['merchantDefined1'];
$checkoutID = $postData['merchantDefined5'];
$checkoutgbpReferenceNo = $postData['gbpReferenceNo'];
$checkoutamount = $postData['amount'];
$checkoutdate = $postData['date'];
$checkouttime = $postData['time'];
$url = $checkout_url.'/afterpay/'.$checkoutID;
$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$referenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"method\"\r\n\r\n$checkoutmethod\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"gbpReferenceNo\"\r\n\r\n$checkoutgbpReferenceNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$checkoutamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$checkoutdate\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"time\"\r\n\r\n$checkouttime\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"shoprefNo\"\r\n\r\n$checkoutshoprefNo\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"serialID\"\r\n\r\n$checkoutserialID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"checkoutID\"\r\n\r\n$checkoutID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

$checkoutReturn = AS_Gbprimepay_API::afterpayCheckout("$url", $field, 'POST');


AS_Gbprimepay::log(  'Mobile Banking Return Handler: ' .$url.'\r\n\r\n'. print_r( $checkoutReturn, true ) );
                }else{
                    if ($order->has_status(array('pending', 'failed'))) {
                        $order->update_status( 'failed', sprintf( __( 'GBPrimePay Mobile Banking Payment failed.', 'gbprimepay-payment-gateways' ) ) );
                    }
                }
            AS_Gbprimepay::log(  'Mobile Banking Callback Handler: ' . print_r( $postData, true ) );

      }
    }

    }
}
    



  }



  public function form()
  {


echo '';
$sortGateways = array();
$sortGatewaysTXT = array();
$gateways = WC()->payment_gateways->get_payment_gateway_ids();
echo '<fieldset id="wc-gbprimepay-checkout-form" class="wc-credit-card-form wc-payment-form">';
if( $gateways ) {
    foreach( $gateways as $gateway ) {
        $gbpgateway = array('gbprimepay','gbprimepay_installment','gbprimepay_qrcode','gbprimepay_qrcredit','gbprimepay_qrwechat','gbprimepay_linepay','gbprimepay_truewallet','gbprimepay_mbanking','gbprimepay_atome','gbprimepay_shopeepay','gbprimepay_barcode');
        if(in_array($gateway,$gbpgateway)){
            $echocode = ''."\r\n";
        }
        if ($this->payment_settings_checkout['enabled'] === 'yes') {
            if( $gateway == 'gbprimepay') {
                
                $echocode .= '<div class="form-row form-row-wide" style="width:100%;height:auto;min-height:90px;max-height:90px;padding:0;display:inline-block;text-indent:-9999px;background: url('.plugin_dir_url( __DIR__ ).'assets/images/gbprimepay-logo-pay.png'.');background-repeat:no-repeat;background-size:contain;background-position: center center;"></div>'."\r\n";

                if ($this->payment_settings['enabled'] === 'yes') {
                    $sortGateways[] = 'creditcard';
                    $sortGatewaysTXT[] = 'Credit Card';
                }
      
            }
            if( $gateway == 'gbprimepay_installment') {

                if ($this->payment_settings_installment['enabled'] === 'yes') {
                
                    $all_installment_term = $this->payment_settings_installment['kasikorn_installment_term'].', '.$this->payment_settings_installment['krungthai_installment_term'].', '.$this->payment_settings_installment['thanachart_installment_term'].', '.$this->payment_settings_installment['ayudhya_installment_term'].', '.$this->payment_settings_installment['firstchoice_installment_term'].', '.$this->payment_settings_installment['scb_installment_term'].', '.$this->payment_settings_installment['bbl_installment_term'];
                
                    $all_arrterm_check = explode(',',preg_replace('/\s+/', '', $all_installment_term));
                    $all_arrterm_pass = (array_filter($all_arrterm_check));
                
                    if((WC()->cart->total >= 3000) && ((WC()->cart->total/(min($all_arrterm_pass))) >= 500)){
 
                $sortGateways[] = 'installment';
                $sortGatewaysTXT[] = 'Credit Card Installment';
                
                    }
                
                  }
      
            }
            if( $gateway == 'gbprimepay_qrcode') {
                if ($this->payment_settings_qrcode['enabled'] === 'yes') {                
                  $sortGateways[] = 'qrcode';
                  $sortGatewaysTXT[] = 'QR Code';
                  }
      
            }
            if( $gateway == 'gbprimepay_qrcredit') {
                if ($this->payment_settings_qrcredit['enabled'] === 'yes') {                    
                  $sortGateways[] = 'qrcredit';
                  $sortGatewaysTXT[] = 'QR Visa';
                  }
      
            }
            if( $gateway == 'gbprimepay_qrwechat') {
                if ($this->payment_settings_qrwechat['enabled'] === 'yes') {
                  $sortGateways[] = 'qrwechat';
                  $sortGatewaysTXT[] = 'QR Wechat';
                  }
      
            }
            if( $gateway == 'gbprimepay_linepay') {
                if ($this->payment_settings_linepay['enabled'] === 'yes') {
                  $sortGateways[] = 'linepay';
                  $sortGatewaysTXT[] = 'Rabbit Line Pay';
                  }
      
            }
            if( $gateway == 'gbprimepay_truewallet') {
                if ($this->payment_settings_truewallet['enabled'] === 'yes') {
                  $sortGateways[] = 'truewallet';
                  $sortGatewaysTXT[] = 'TrueMoney Wallet';
                  }
      
            }
            if( $gateway == 'gbprimepay_mbanking') {

                if ($this->payment_settings_mbanking['enabled'] === 'yes') {
                    
                    if((WC()->cart->total >= 20) && ($this->account_settings['environment']=='production')){
 
                $sortGateways[] = 'mbanking';
                $sortGatewaysTXT[] = 'Mobile Banking';
                
                    }
                
                  }
      
            }
            if( $gateway == 'gbprimepay_atome') {

                if ($this->payment_settings_atome['enabled'] === 'yes') {
                    
                    if((WC()->cart->total >= 20) && ($this->account_settings['environment']=='production')){
 
                $sortGateways[] = 'atome';
                $sortGatewaysTXT[] = 'Atome';
                
                    }
                
                  }
      
            }
            if( $gateway == 'gbprimepay_shopeepay') {
                if ($this->payment_settings_shopeepay['enabled'] === 'yes') {
                  $sortGateways[] = 'shopeepay';
                  $sortGatewaysTXT[] = 'ShopeePay';
                  }
      
            }
            if( $gateway == 'gbprimepay_barcode') {
                if ($this->payment_settings_barcode['enabled'] === 'yes') {
                  $sortGateways[] = 'barcode';
                  $sortGatewaysTXT[] = 'Bill Payment';
                  }
      
            }
  
        }


        if(in_array($gateway,$gbpgateway)){
            $echocode .= ''."\r\n";
            echo $echocode;
        }
    }
}













if(isset($sortGateways)){
    $keys = array_keys($sortGateways);
    $res =  '';
    $i = 0;
        foreach($sortGateways as $key => $value) {
            if ($i == 0) {
                $res .=  '<input type="hidden" name="gbprimepay_checkout-select_method" value="'. $value .'">';
            }
            $res .=  '<input type="hidden" name="gbprimepay_checkout-sort_method['. $key .']" value="'. $value .'">';
        $i++;
        }
  }
echo $res;
if(isset($sortGatewaysTXT)){
    $keys = array_keys($sortGatewaysTXT);
    $arrTXT =  '';
        $i = 0;
        $count = count($sortGatewaysTXT);
        $lastElement = end($sortGatewaysTXT);
        foreach($sortGatewaysTXT as $key => $value) {
            if ($i == 0) {
                $arrTXT .=  '';
            }else{
                if(($value == $lastElement) && ($i > 0)) {
                    $arrTXT .=  ' or ';
                }else{
                    $arrTXT .=  ', ';
                }
            }
                $arrTXT .=  ''. $value .'';
        $i++;
        }
  }

echo '<div class="form-row form-row-wide" style="padding:2rem 7% 2rem 7%;"><center><span style="font-size:90%;font-weight: 600;line-height: 1;">Pay Securely by <br></span><span style="font-size:85%;line-height: 1;">'.$arrTXT.' through<br></span> <span style="font-size:90%;font-weight: 600;line-height: 1;">GBPrimePay Payments</span></center></div>';
echo '<div class="clear"></div>';
echo '</fieldset>';



























  }
    public function send_failed_order_email($order_id)
    {
        $emails = WC()->mailer()->get_emails();
        if (!empty($emails) && !empty($order_id)) {
            $emails['WC_Email_Failed_Order']->trigger($order_id);
        }
    }
}
