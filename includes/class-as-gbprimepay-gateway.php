<?php
class AS_Gateway_Gbprimepay extends WC_Payment_Gateway_CC
{
    public $capture;
    public $saved_cards;
    public $description1;
    public function __construct()
    {
        $this->id = 'gbprimepay';
        $this->method_title = __('GBPrimePay Credit Card', 'gbprimepay-payment-gateways');
        $this->account_settings = get_option('gbprimepay_account_settings');
        if(gbp_instances('3D_SECURE_PAYMENT')==TRUE){
          if(($this->account_settings['environment'])=='prelive'){
            $this->method_description = sprintf(__('3-D Secure Credit Card Payment Gateway with GBPrimePay'));
          }else{
            $this->method_description = sprintf(__('3-D Secure Credit Card Payment Gateway with GBPrimePay'));
          }
        }else{
          $this->method_description = sprintf(__('Credit Card integration with GBPrimePay.'));
        }
        $this->has_fields = true;
        $this->supports = array(
            'products',
            'tokenization',
            'refunds',
            'add_payment_method'
        );
        // load settings form fields
        $this->init_form_fields();
        // load settings
        $this->init_settings();
        $this->account_settings = get_option('gbprimepay_account_settings');
        $this->payment_settings = get_option('gbprimepay_payment_settings');
        $this->payment_settings_qrcode = get_option('gbprimepay_payment_settings_qrcode');
        $this->payment_settings_qrcredit = get_option('gbprimepay_payment_settings_qrcredit');
        $this->payment_settings_barcode = get_option('gbprimepay_payment_settings_barcode');
        $this->payment_settings_checkout = get_option('gbprimepay_payment_settings_checkout');
        $this->title = $this->get_option('title');
        $this->description1 = $this->get_option('description1');
        $this->saved_cards = 'yes';
        update_option('gbprimepay_payment_settings', $this->settings);
        // Add hooks
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts')); // not yet use this
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action( 'woocommerce_api_'. strtolower( get_class($this) ), array( $this, 'secure_callback_handler' ) );
        // add_action( 'init', 'secure_callback_handler' );
add_filter( 'woocommerce_saved_payment_methods_list', 'wc_get_account_saved_payment_methods_list', 10, 2 );
    }
    public function init_form_fields()
    {
        $this->form_fields = include('settings-formfields-gbprimepay.php');
    }
    public function payment_scripts()
    {
        if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order']) && !is_add_payment_method_page()) {
            return;
        }
        wp_enqueue_script('se_gbprimepay', plugin_dir_url( __DIR__ ) .'assets/js/gbprimepay.js');
    }
    /**
     * Check if this gateway is enabled
     */
    public function is_available()
    {
        if ($this->payment_settings['enabled'] === 'yes') {
            // return AS_Gbprimepay_API::get_credentials('direct');
          if ($this->payment_settings_checkout['enabled'] === 'yes') {
            return false;
          }
        }
        return false;
    }
    public function cover_img( $brand_str ) {
      $brand = explode(" ", $brand_str);
      switch ($brand[0]) {
        case 'Visa':
          $cover = str_replace('Visa ending in ', '<img src="'.plugin_dir_url( __DIR__ ).'assets/images/credit-cards/visa.svg" class="" style="float: right;border: 0;padding: 4px 4px 2px 0;max-height: 1.618em;">', $brand_str);
        break;
        case 'MasterCard':
          $cover = str_replace('MasterCard ending in ', '<img src="'.plugin_dir_url( __DIR__ ).'assets/images/credit-cards/mastercard.svg" class="" style="float: right;border: 0;padding: 4px 4px 2px 0;max-height: 1.618em;">', $brand_str);
        break;
        case 'Amex':
          $cover = str_replace('Amex ending in ', '<img src="'.plugin_dir_url( __DIR__ ).'assets/images/credit-cards/amex.svg" class="" style="float: right;border: 0;padding: 4px 4px 2px 0;max-height: 1.618em;">', $brand_str);
        break;
        case 'JCB':
          $cover = str_replace('JCB ending in ', '<img src="'.plugin_dir_url( __DIR__ ).'assets/images/credit-cards/jcb.svg" class="" style="float: right;border: 0;padding: 4px 4px 2px 0;max-height: 1.618em;">', $brand_str);
        break;
        case 'Unionpay':
          $cover = str_replace('Unionpay ending in ', '<img src="'.plugin_dir_url( __DIR__ ).'assets/images/credit-cards/visa.svg" class="" style="float: right;border: 0;padding: 4px 4px 2px 0;max-height: 1.618em;">', $brand_str);
        break;
        case 'Forbrugsforeningen':
          $cover = str_replace('Forbrugsforeningen ending in ', '<img src="'.plugin_dir_url( __DIR__ ).'assets/images/credit-cards/visa.svg" class="" style="float: right;border: 0;padding: 4px 4px 2px 0;max-height: 1.618em;">', $brand_str);
        break;
        case 'Maestro':
          $cover = str_replace('Maestro ending in ', '<img src="'.plugin_dir_url( __DIR__ ).'assets/images/credit-cards/visa.svg" class="" style="float: right;border: 0;padding: 4px 4px 2px 0;max-height: 1.618em;">', $brand_str);
        break;
        case 'Discover':
          $cover = str_replace('Discover ending in ', '<img src="'.plugin_dir_url( __DIR__ ).'assets/images/credit-cards/visa.svg" class="" style="float: right;border: 0;padding: 4px 4px 2px 0;max-height: 1.618em;">', $brand_str);
        break;
        case 'Dinersclub':
          $cover = str_replace('Dinersclub ending in ', '<img src="'.plugin_dir_url( __DIR__ ).'assets/images/credit-cards/visa.svg" class="" style="float: right;border: 0;padding: 4px 4px 2px 0;max-height: 1.618em;">', $brand_str);
        break;
        case 'Dankort':
          $cover = str_replace('Dankort ending in ', '<img src="'.plugin_dir_url( __DIR__ ).'assets/images/credit-cards/visa.svg" class="" style="float: right;border: 0;padding: 4px 4px 2px 0;max-height: 1.618em;">', $brand_str);
        break;
        default:
          $cover = str_replace('ending in ', '<img src="'.plugin_dir_url( __DIR__ ).'assets/images/credit-cards/visa.svg" class="" style="float: right;border: 0;padding: 4px 4px 2px 0;max-height: 1.618em;">', $brand_str);
        break;
      }
     return $cover;
   }
    public function get_saved_payment_method_option_html( $token ) {
     $html = sprintf(
       '<li class="woocommerce-SavedPaymentMethods-token">
         <input id="wc-%1$s-payment-token-%2$s" type="radio" name="wc-%1$s-payment-token" value="%2$s" style="width:auto;" class="woocommerce-SavedPaymentMethods-tokenInput" %4$s />
         <label for="wc-%1$s-payment-token-%2$s">%3$s</label>
       </li>',
       esc_attr( $this->id ),
       esc_attr( $token->get_id() ),
       $this->cover_img($token->get_display_name()),
       checked( $token->is_default(), true, false )
     );
     return apply_filters( 'woocommerce_payment_gateway_get_saved_payment_method_option_html', $html, $token, $this );
    }
    public function payment_fields()
    {
        $user = wp_get_current_user();
        $total = WC()->cart->total;
        $display_token = $this->supports('tokenization') && is_checkout() && $this->saved_cards;
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
            $pay_button_text = __('Add Card', 'gbprimepay-payment-gateways');
            $total = '';
        } else {
            $pay_button_text = '';
        }
$echocode = ''."\r\n";
$echocode .= '<div style="padding:1.25em 0 0 0;margin-top:-1.25em;display:inline-block;"><img style="float: left;max-height: 2.8125em;" src="'.plugin_dir_url( __DIR__ ).'assets/images/creditcard.png'.'" alt=""></div>'."\r\n";
$echocode .= ''."\r\n";
echo $echocode;
        echo '<div
			id="gbprimepay-payment-data"
			data-panel-label="' . esc_attr($pay_button_text) . '"
			data-description1=""
			data-email="' . esc_attr($user_email) . '"
			data-amount="' . esc_attr($total) . '"
			data-allow-remember-me="' . esc_attr($this->saved_cards ? 'true' : 'false') . '">'; //todo: change this
        if ( $this->description1 ) {
            echo '<p>'.wpautop( wp_kses_post( $this->description1 ) ).'</p>';
        }
        if ($display_token) {
          $this->saved_payment_methods();
        }
        $this->form();
        if ($this->saved_cards === 'yes') {
            $this->save_payment_method_checkbox();
        }
        echo '</div>';
    }
    /**
     * @param AS_Gbprimepay_User_Account $gbprimepayUser
     * @param $params
     * @return mixed
     */
    public function get_card_account($gbprimepayUser, $params = null)
    {
        $expiry = preg_replace('/\s+/', '', $params['gbprimepay-card-expiry']);
        $explode = explode('/', $expiry);
        if (!isset($explode[0])) {
           $explode[0] = null;
         }
        if (!isset($explode[1])) {
          $explode[1] = null;
        }
        $params['gbprimepay-card-number'] = preg_replace('/\s+/', '', $params['gbprimepay-card-number']);
        $is_save_action = isset($params['wc-gbprimepay-new-payment-method']) ? $params['wc-gbprimepay-new-payment-method'] : '';
        if($is_save_action==true){
          $customer_rememberCard='true';
        }else{
          $customer_rememberCard='false';
        }
        $customer_full_name = $gbprimepayUser->get_gbprimepay_full_name();
        $body = array(
            'user_id' => $gbprimepayUser->get_gbprimepay_user_id(),
            'number' => $params['gbprimepay-card-number'],
            'expiry_month' => $explode[0],
            'expiry_year' => $explode[1],
            'cvv' => $params['gbprimepay-card-cvc'],
            'is_save' => $customer_rememberCard,
            'full_name' => $customer_full_name
        );
        $gbprimepayApiObj = new AS_Gbprimepay_API();
        $createCardResponse = $gbprimepayApiObj->createCardAccount($body);
            AS_Gbprimepay::log(  'createUser Request: ' . print_r( $createCardResponse, true ) );
        return $createCardResponse;
    }
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);
        try {
            $postData = $_POST;
            $gbprimepayUser = new AS_Gbprimepay_User_Account(get_current_user_id(), $order); // get gbprimepay user obj
            $otpResponseUrl = $this->get_return_url($order);
            $otpBackgroundUrl = home_url()."/" . 'wc-api/AS_Gateway_Gbprimepay/';
            $otpRememberCard='';
            $otpTokenNEW = $postData['wc-gbprimepay-payment-token'];
            $otpis_save_action = isset($postData['wc-gbprimepay-new-payment-method']) ? $postData['wc-gbprimepay-new-payment-method'] : '';
            if ($otpTokenNEW === 'new') {
              if($otpis_save_action==true){
                $otpRememberCard='RememberCard';
              }
            }
                            WC()->session->set('gbprimepay_otpurl', array(
                                                             'ResponseUrl' => $otpResponseUrl,
                                                             'BackgroundUrl' => $otpBackgroundUrl,
                                                             'TokenRememberCard' => $otpRememberCard,
                                                              )
                            );
            if ($postData['wc-gbprimepay-payment-token'] === 'new') {
                $cardAccount = $this->get_card_account($gbprimepayUser, $postData); // gbprimepay card account
                if ($cardAccount) { // card account created
                    $gbprimepayApiObj = new AS_Gbprimepay_API();
                    $account_settings = get_option('gbprimepay_account_settings');
                    if($account_settings['environment']=='production'){
                            if(gbp_instances('3D_SECURE_PAYMENT')==TRUE){
                                  // 3-D Secure Payment
                                  $makePaymentResponse = $gbprimepayApiObj->createSecureCharge($cardAccount['id'], $order);
                            }else{
                                  // GBPrimePay Payment
                                  $makePaymentResponse = $gbprimepayApiObj->createCharge($cardAccount['id'], $order);
                            }
                    }else{
                            if(gbp_instances('3D_SECURE_PAYMENT')==TRUE){
                                  // 3-D Secure Payment
                                  $makePaymentResponse = $gbprimepayApiObj->createOtpCharge($cardAccount['id'], $order);
                            }else{
                                  // GBPrimePay Payment
                                  $makePaymentResponse = $gbprimepayApiObj->createCharge($cardAccount['id'], $order);
                            }
                    }
                    if (!$makePaymentResponse) {
                        throw new Exception(__('Something went wrong while creating charge.'));
                    }
                        $is_response_action = isset($makePaymentResponse['RedirectURL']) ? $makePaymentResponse['RedirectURL'] : '';
                        if($is_response_action==true){
                          $responseRedirectURL='true';
                        }else{
                          $responseRedirectURL='false';
                        }
                        if ($responseRedirectURL=='true') {
                                  // 3-D Secure Payment
                                  $gbprimepay_otpcharge = WC()->session->get('gbprimepay_otpcharge');
                                  if ($gbprimepay_otpcharge['resultCode'] == '00') {
                                  // RememberCard
                                  $otp_customer_rememberCard = $gbprimepay_otpcharge['merchantDefined3'];
                                  if ($otp_customer_rememberCard == 'RememberCard') {
                                          $gbprimepayApiObj = new AS_Gbprimepay_API();
                                          $getCardResponse = $gbprimepayApiObj->getCardAccount($cardAccount['id']);
                                          if (class_exists('WC_Payment_Token_CC') && $this->saved_cards) {
                                                    if ($otp_customer_rememberCard == 'RememberCard') {
                                                            $token = new WC_Payment_Token_CC();
                                                            $token->set_token($cardAccount['id']);
                                                            $token->set_gateway_id('gbprimepay_wait');
                                                            $token->set_card_type($getCardResponse['card']['type']);
                                                            $token->set_last4(substr($getCardResponse['card']['number'], -4));
                                                            $token->set_expiry_month($getCardResponse['card']['expiry_month']);
                                                            $token->set_expiry_year('20' . $getCardResponse['card']['expiry_year']);
                                                            $token->set_user_id(get_current_user_id());
                                                            $token->save();
                                                    }
                                          }
                                  }
                                      $order->update_status('on-hold', __( 'Awaiting 3-D Secure Payment', 'gbprimepay-payment-gateways' ));
                                      // Remove cart.
                                      WC()->cart->empty_cart();
                                  }
                                    return array(
                                        'result' => 'success',
                                        'redirect' => $is_response_action,
                                    );
                        }else{
                          // GBPrimePay Payment
                          if ($makePaymentResponse['resultCode'] == '00') {
                              $gbprimepayApiObj = new AS_Gbprimepay_API();
                              $getCardResponse = $gbprimepayApiObj->getCardAccount($cardAccount['id']);
                              $is_save_action = isset($postData['wc-gbprimepay-new-payment-method']) ? $postData['wc-gbprimepay-new-payment-method'] : '';
                              if($is_save_action==true){
                                $customer_rememberCard='true';
                              }else{
                                $customer_rememberCard='false';
                              }
                              if (class_exists('WC_Payment_Token_CC') && $this->saved_cards) {
                                        if ($customer_rememberCard == 'true') {
                                                $token = new WC_Payment_Token_CC();
                                                $token->set_token($cardAccount['id']);
                                                $token->set_gateway_id('gbprimepay');
                                                $token->set_card_type($getCardResponse['card']['type']);
                                                $token->set_last4(substr($getCardResponse['card']['number'], -4));
                                                $token->set_expiry_month($getCardResponse['card']['expiry_month']);
                                                $token->set_expiry_year('20' . $getCardResponse['card']['expiry_year']);
                                                $token->set_user_id(get_current_user_id());
                                                $token->save();
                                        }
                              }
                              $order->payment_complete($makePaymentResponse['gbpReferenceNo']);
                              update_post_meta($order_id, 'Gbprimepay Charge ID', $makePaymentResponse['merchantDefined1']);
                              // Remove cart.
                              WC()->cart->empty_cart();
                              // Return thank you page redirect.
                              return array(
                                  'result' => 'success',
                                  'redirect' => $this->get_return_url($order),
                              );
                          }
                        }
                }
            } else {
                $tokenId = $postData['wc-gbprimepay-payment-token'];
                $token = WC_Payment_Tokens::get($tokenId);
                if (!$token || $token->get_user_id() !== get_current_user_id()) {
                    WC()->session->set('refresh_totals', true);
                    throw new Exception(__('Invalid payment method. Please input a new card number.', 'gbprimepay-payment-gateways'));
                }
                $cardAccountId = $token->get_token();
                $gbprimepayApiObj = new AS_Gbprimepay_API();
                $account_settings = get_option('gbprimepay_account_settings');
                if($account_settings['environment']=='production'){
                        if(gbp_instances('3D_SECURE_PAYMENT')==TRUE){
                              // 3-D Secure Payment
                              $makePaymentResponse = $gbprimepayApiObj->createSecureCharge($cardAccountId, $order);
                        }else{
                              // GBPrimePay Payment
                              $makePaymentResponse = $gbprimepayApiObj->createCharge($cardAccountId, $order);
                        }
                }else{
                        if(gbp_instances('3D_SECURE_PAYMENT')==TRUE){
                              // 3-D Secure Payment
                              $makePaymentResponse = $gbprimepayApiObj->createOtpCharge($cardAccountId, $order);
                        }else{
                              // GBPrimePay Payment
                              $makePaymentResponse = $gbprimepayApiObj->createCharge($cardAccountId, $order);
                        }
                }
                $is_response_action = isset($makePaymentResponse['RedirectURL']) ? $makePaymentResponse['RedirectURL'] : '';
                if($is_response_action==true){
                  $responseRedirectURL='true';
                }else{
                  $responseRedirectURL='false';
                }
                if ($responseRedirectURL=='true') {
                            // 3-D Secure Payment
                            $gbprimepay_otpcharge = WC()->session->get('gbprimepay_otpcharge');
                            if ($gbprimepay_otpcharge['resultCode'] == '00') {
                                $order->update_status('on-hold', __( 'Awaiting 3-D Secure Payment', 'gbprimepay-payment-gateways' ));
                                // Remove cart.
                                WC()->cart->empty_cart();
                            }
                              return array(
                                  'result' => 'success',
                                  'redirect' => $is_response_action,
                              );
                }else{
                  if ($makePaymentResponse) {
                      if ($makePaymentResponse['resultCode'] == '00') {
                          $order->payment_complete($makePaymentResponse['gbpReferenceNo']);
                          update_post_meta($order_id, 'Gbprimepay Charge ID', $makePaymentResponse['merchantDefined1']);
                          // Remove cart.
                          WC()->cart->empty_cart();
                          // Return thank you page redirect.
                          return array(
                              'result' => 'success',
                              'redirect' => $this->get_return_url($order),
                          );
                      }
                  } else {
                      throw new Exception(__('Something went wrong while capturing the payment.'));
                  }
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
    public function update_token($tokenId) {
      global $wpdb;
      $wpdb->update(
      $wpdb->prefix . 'woocommerce_payment_tokens',
      array( 'gateway_id' => 'gbprimepay' ),
      array(
      'token' => $tokenId,
      )
      );
    }
    public function delete_token($del_tokenId) {
      global $wpdb;
      $del_tokenNumber = $wpdb->get_var(
        $wpdb->prepare(
          "SELECT token_id FROM {$wpdb->prefix}woocommerce_payment_tokens WHERE token = %d",
          $del_tokenId
        )
      );
      $wpdb->delete( $wpdb->prefix . 'woocommerce_payment_tokenmeta', array( 'payment_token_id' => $del_tokenNumber ), array( '%d' ) );
      $wpdb->delete( $wpdb->prefix . 'woocommerce_payment_tokens', array( 'token_id' => $del_tokenNumber ), array( '%d' ) );
    }
    public function secure_callback_handler() {
                $postData = $_POST;
                $referenceNo = $postData['referenceNo'];
                $order_id = substr($postData['referenceNo'], 7);
                $order = wc_get_order($order_id);
                  if ( isset( $postData['resultCode'] ) ) {
                            if ($postData['resultCode'] == '00') {
                              // Update RememberCard
                              $otp_customer_rememberCard = $postData['merchantDefined3'];
                              if ($otp_customer_rememberCard == 'RememberCard') {
                                      $tokenId = $postData['merchantDefined5'];
                                      $this->update_token($tokenId);
                              }
                                    $order->payment_complete($postData['gbpReferenceNo']);
                                    update_post_meta($order_id, 'Gbprimepay Charge ID', $postData['merchantDefined1']);
                            				$order->add_order_note(
                            					__( '3-D Secure Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                            					__( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $postData['gbpReferenceNo'] . PHP_EOL .
                            					__( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amount'])
                            				);
                            }else{
                              // Delete RememberCard
                              $otp_customer_rememberCard = $postData['merchantDefined3'];
                              if ($otp_customer_rememberCard == 'RememberCard') {
                                  // Del token
                                  $del_tokenId = $postData['merchantDefined5'];
                                  $this->delete_token($del_tokenId);
                              }
                                    $order->update_status( 'failed', sprintf( __( '3-D Secure Payment failed.', 'gbprimepay-payment-gateways' ) ) );
                            }
                        AS_Gbprimepay::log(  'Secure Callback Handler: ' . print_r( $postData, true ) );
                	}
      }
    public function add_payment_method()
    {
          if (empty($_POST['gbprimepay-card-number']) ||
              empty($_POST['gbprimepay-card-expiry']) ||
              !is_user_logged_in()
          ) {
              wc_add_notice(__('There was a problem adding the card.', 'gbprimepay-payment-gateways'), 'error');
              return;
          }
          $gbprimepayUser = new AS_Gbprimepay_User_Account(get_current_user_id());
          $getCardResponse = $this->get_card_account($gbprimepayUser, $_POST);
                                    $cardnumber = preg_replace('/[^0-9]/', '', $getCardResponse['card']['number']);
                                    $digit = (int) mb_substr($cardnumber, 0, 2);
                                    if ( in_array( $digit, array(51, 52, 53, 54, 55, 22, 23, 24, 25, 26, 27) ) ) {
                                        $cardtype = 'mastercard';
                                    } else if ( in_array( $digit, array(35) ) ) {
                                        $cardtype = 'jcb';
                                    } else if ( in_array( $digit, array(34, 37) ) ) {
                                        $cardtype = 'amex';
                                    } else {
                                        $cardtype = 'visa';
                                    }
                                    // echo '<pre>';
                                    // print_r($getCardResponse);
                                    // echo '</pre><br>';
                                    // echo $cardtype;
                                    // exit;
                    if ($getCardResponse['card']['token']) {
                            $token = new WC_Payment_Token_CC();
                            $token->set_token($getCardResponse['card']['token']);
                            $token->set_gateway_id('gbprimepay');
                            $token->set_card_type($cardtype);
                            $token->set_last4(substr($getCardResponse['card']['number'], -4));
                            $token->set_expiry_month($getCardResponse['card']['expirationMonth']);
                            $token->set_expiry_year('20' . $getCardResponse['card']['expirationYear']);
                            $token->set_user_id(get_current_user_id());
                            $token->save();
                                return array(
                                    'result' => 'success',
                                    'redirect' => wc_get_endpoint_url('payment-methods'),
                                );
                    }else{
                                return array(
                                    'result' => 'fail',
                                    'redirect' => wc_get_endpoint_url('payment-methods'),
                                );
                    }
    }
    public function form()
    {
        wp_enqueue_script('wc-credit-card-form');
        $fields = array();
        $cvc_field = '<p class="form-row form-row-last">
			<label for="' . esc_attr($this->id) . '-card-cvc">' . esc_html__('Card code', 'woocommerce') . ' <span class="required">*</span></label>
			<input id="' . esc_attr($this->id) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc" name="' . esc_attr($this->id) . '-card-cvc" inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" maxlength="4" placeholder="' . esc_attr__('CVC', 'woocommerce') . '" ' . $this->field_name('card-cvc') . ' style="width:100px" />
		</p>';
        $default_fields = array(
            'card-number-field' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr($this->id) . '-card-number">' . esc_html__('Card number', 'woocommerce') . ' <span class="required">*</span></label>
				<input id="' . esc_attr($this->id) . '-card-number" name="' . esc_attr($this->id) . '-card-number" class="input-text wc-credit-card-form-card-number" inputmode="numeric" autocomplete="cc-number" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" ' . $this->field_name('card-number') . ' />
			</p>',
            'card-expiry-field' => '<p class="form-row form-row-first">
				<label for="' . esc_attr($this->id) . '-card-expiry">' . esc_html__('Expiry (MM/YY)', 'woocommerce') . ' <span class="required">*</span></label>
				<input id="' . esc_attr($this->id) . '-card-expiry" name="' . esc_attr($this->id) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" inputmode="numeric" autocomplete="cc-exp" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="' . esc_attr__('MM / YY', 'woocommerce') . '" ' . $this->field_name('card-expiry') . ' />
			</p>',
        );
        if (!$this->supports('credit_card_form_cvc_on_saved_method')) {
            $default_fields['card-cvc-field'] = $cvc_field;
        }
        $default_fields['card-token'] = '<input id="' . esc_attr($this->id) . '-card-token" style="display: none;" class="input-text"' . $this->field_name('card-token') . ' />';
        $fields = wp_parse_args($fields, apply_filters('woocommerce_credit_card_form_fields', $default_fields, $this->id));
        ?>
        <fieldset id="wc-<?php echo esc_attr($this->id); ?>-cc-form" class='wc-credit-card-form wc-payment-form'>
            <?php do_action('woocommerce_credit_card_form_start', $this->id); ?>
            <?php
            foreach ($fields as $field) {
                echo $field;
            }
            ?>
            <?php do_action('woocommerce_credit_card_form_end', $this->id); ?>
            <div class="clear"></div>
        </fieldset>
        <?php
        if ($this->supports('credit_card_form_cvc_on_saved_method')) {
            echo '<fieldset>' . $cvc_field . '</fieldset>';
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
