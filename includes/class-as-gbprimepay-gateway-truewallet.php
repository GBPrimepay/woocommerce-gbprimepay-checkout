<?php
class AS_Gateway_Gbprimepay_Truewallet extends WC_Payment_Gateway_eCheck
{
    public $environment;
    public $description2;
    public function __construct()
    {
        $this->id = 'gbprimepay_truewallet';
        $this->method_title = __('GBPrimePay TrueMoney Wallet', 'gbprimepay-payment-gateways-truewallet');
        $this->method_description = sprintf(__('TrueMoney Wallet integration with GBPrimePay'));
        $this->has_fields = true;
        $this->supports = array(
            'products',
            'refunds'
        );
        $this->init_form_fields();
        // load settings
        $this->init_settings();
        $this->settings['enabled'] = AS_Gbprimepay_API::_can_enabled($this->settings['enabled']);
        $this->account_settings = get_option('gbprimepay_account_settings');
        $this->payment_settings = get_option('gbprimepay_payment_settings');
        $this->payment_settings_truewallet = get_option('gbprimepay_payment_settings_truewallet');
        $this->payment_settings_checkout = get_option('gbprimepay_payment_settings_checkout');
        $this->title = $this->payment_settings_truewallet['title'];
        $this->description2 = $this->payment_settings_truewallet['description2'];
        $this->environment = $this->account_settings['environment'];
        update_option('gbprimepay_payment_settings_truewallet', $this->settings);
        // Add hooks
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts')); // not yet use this
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action( 'init', array( $this, 'truewallet_callback_handler' ) );
        add_action( 'woocommerce_api_'. strtolower( get_class($this) ), array( $this, 'truewallet_callback_handler' ) );
    }
    public function init_form_fields()
    {
        $this->form_fields = include('settings-formfields-gbprimepay-truewallet.php');
    }
    /**
     * Check if this gateway is enabled
     */
    public function is_available()
    {
        if ($this->payment_settings_truewallet['enabled'] === 'yes') {
            // return AS_Gbprimepay_API::get_credentials('truewallet');
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
            $pay_button_text = __('Add Card', 'gbprimepay-payment-gateways-truewallet');
            $total = '';
        } else {
            $pay_button_text = '';
        }
        echo '<div
			id="gbprimepay-payment-truewallet-data"
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
            if ($postData['payment_method']=="gbprimepay_truewallet") {
              if (!empty($postData['gbprimepay_truewallet-bankcode']) && (!empty($postData['gbprimepay_truewallet-term']))) {
                  $order->add_order_note('Order created and status set to Pending payment.');
                  $order->update_status('pending', __( 'Awaiting TrueMoney Wallet integration with GBPrimePay.', 'gbprimepay-payment-gateways' ));
                    $account_settings = get_option('gbprimepay_account_settings');
                    if ($account_settings['environment'] === 'prelive') {
                        $truewallet_url = gbp_instances('URL_TRUEWALLET_TEST');
                        $truewallet_publicKey = $account_settings['test_public_key'];
                        $truewallet_secret_key = $account_settings['test_secret_key'];
                    } else {
                        $truewallet_url = gbp_instances('URL_TRUEWALLET_LIVE');
                        $truewallet_publicKey = $account_settings['live_public_key'];
                        $truewallet_secret_key = $account_settings['live_secret_key'];
                    }
                    $amount = $order->get_total();
                    $truewallet_amount = number_format((($amount * 100)/100), 2, '.', '');
                    $truewallet_bankCode = $postData['gbprimepay_truewallet-bankcode'];
                    $truewallet_term = $postData['gbprimepay_truewallet-term'];
                    $truewallet_detail = 'Charge for order ' . $order->get_order_number();
                    $truewallet_customerName = '' . $order->get_billing_first_name(). ' ' .$order->get_billing_last_name();
                    $truewallet_customerEmail = '' . $order->get_billing_email();
                    $truewallet_customerAddress = '' . str_replace("<br/>", " ", $order->get_formatted_billing_address());
                    $truewallet_customerTelephone = '' . $order->get_billing_phone();
                    $truewallet_referenceNo = ''.substr(time(), 4, 5).'00'.$order->get_order_number();
                    $truewallet_responseUrl = $this->get_return_url($order);
                    $truewallet_backgroundUrl = home_url()."/" . 'wc-api/AS_Gateway_Gbprimepay_Truewallet/';
                    $callgenerateID = AS_Gbprimepay_API::generateID();
                    $RedirectURL =  add_query_arg(
                                    array(
                                        'page' => rawurlencode($truewallet_url),
                                        'publicKey' => rawurlencode($truewallet_publicKey),
                                        'referenceNo' => rawurlencode($truewallet_referenceNo),
                                        'responseUrl' => rawurlencode($truewallet_responseUrl),
                                        'backgroundUrl' => rawurlencode($truewallet_backgroundUrl),
                                        'detail' => rawurlencode($truewallet_detail),
                                        'customerName' => rawurlencode($truewallet_customerName),
                                        'customerEmail' => rawurlencode($truewallet_customerEmail),
                                        'customerAddress' => rawurlencode($truewallet_customerAddress),
                                        'customerTelephone' => rawurlencode($truewallet_customerTelephone),
                                        'amount' => rawurlencode($truewallet_amount),
                                        'bankCode' => rawurlencode($truewallet_bankCode),
                                        'term' => rawurlencode($truewallet_term),
                                        'merchantDefined1' => rawurlencode($callgenerateID),
                                        'merchantDefined2' => rawurlencode(''),
                                        'merchantDefined3' => rawurlencode($truewallet_referenceNo),
                                        'merchantDefined4' => rawurlencode(''),
                                        'merchantDefined5' => rawurlencode(''),
                                        'secret_key' => rawurlencode($truewallet_secret_key)
                                    ), WP_PLUGIN_URL."/" . plugin_basename( dirname(__FILE__) ) . '/redirect/pay.php');
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
        wp_enqueue_script('gbprimepay_truewallet', plugin_dir_url( __DIR__ ) .'assets/js/gbprimepay-truewallet.js', '', '', true );
    }
  public function truewallet_callback_handler() {
              $postData = $_POST;
              $referenceNo = $postData['referenceNo'];
              $order_id = substr($postData['referenceNo'], 7);
              $order = wc_get_order($order_id);
                if ( isset( $postData['resultCode'] ) ) {
                          if ($postData['resultCode'] == '00') {
                                  $order->payment_complete($postData['gbpReferenceNo']);
                                  update_post_meta($order_id, 'Gbprimepay Charge ID', $postData['merchantDefined1']);
                                  $order->add_order_note(
                                    __( 'GBPrimePay TrueMoney Wallet Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                                    __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $postData['gbpReferenceNo'] . PHP_EOL .
                                    __( 'Monthly: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amountPerMonth']) .' x '. $postData['payMonth'] . PHP_EOL .
                                    __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amount'])
                                  );
                          }else{
                                  $order->update_status( 'failed', sprintf( __( 'GBPrimePay TrueMoney Wallet Payment failed.', 'gbprimepay-payment-gateways' ) ) );
                          }
                      AS_Gbprimepay::log(  'TrueMoney Wallet Callback Handler: ' . print_r( $postData, true ) );
                }
  }
  public function form()
  {
    echo '<style>
      #wc-gbprimepay-truewallet-form select { margin: .75rem auto; text-indent: 1px;width:100% !important;}
      #wc-gbprimepay-truewallet-form option {
          padding: 0px 2px 1px;
      }
      #wc-gbprimepay-truewallet-form .container { margin: 150px auto; }
      #wc-gbprimepay-truewallet-form select.form-control {
          padding: 6px 10px;
      }
      #wc-gbprimepay-truewallet-form option {
          padding: 12px 12px;
      }
      #wc-gbprimepay-truewallet-form select.form-control {
          padding: 12px 12px;
      }
      </style>';
      $check_kasikorn_truewallet_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_truewallet['kasikorn_truewallet_term'],'kasikorn');
      $check_krungthai_truewallet_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_truewallet['krungthai_truewallet_term'],'krungthai');
      $check_thanachart_truewallet_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_truewallet['thanachart_truewallet_term'],'thanachart');
      $check_ayudhya_truewallet_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_truewallet['ayudhya_truewallet_term'],'ayudhya');
      $check_firstchoice_truewallet_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_truewallet['firstchoice_truewallet_term'],'firstchoice');
      $check_scb_truewallet_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_truewallet['scb_truewallet_term'],'scb');
      $check_bbl_truewallet_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_truewallet['bbl_truewallet_term'],'bbl');
     echo '<fieldset id="wc-gbprimepay-truewallet-form" class="wc-credit-card-form wc-payment-form">
                             <p class="form-row form-row-wide">';
      echo '<select style="display:none;" id="' . esc_attr($this->id) . '-CCTruewalletToSelect" data-bankcode="#' . esc_attr($this->id) . '-bankcode" data-term="#' . esc_attr($this->id) . '-term">
            <option value=""></option>';
            $gen_kasikorn_truewallet_term = AS_Gbprimepay_API::gen_term_regex($check_kasikorn_truewallet_term,'kasikorn',esc_attr(WC()->cart->total));
            echo $gen_kasikorn_truewallet_term;
            $gen_krungthai_truewallet_term = AS_Gbprimepay_API::gen_term_regex($check_krungthai_truewallet_term,'krungthai',esc_attr(WC()->cart->total));
            echo $gen_krungthai_truewallet_term;
            $gen_thanachart_truewallet_term = AS_Gbprimepay_API::gen_term_regex($check_thanachart_truewallet_term,'thanachart',esc_attr(WC()->cart->total));
            echo $gen_thanachart_truewallet_term;
            $gen_ayudhya_truewallet_term = AS_Gbprimepay_API::gen_term_regex($check_ayudhya_truewallet_term,'ayudhya',esc_attr(WC()->cart->total));
            echo $gen_ayudhya_truewallet_term;
            $gen_firstchoice_truewallet_term = AS_Gbprimepay_API::gen_term_regex($check_firstchoice_truewallet_term,'firstchoice',esc_attr(WC()->cart->total));
            echo $gen_firstchoice_truewallet_term;
            $gen_scb_truewallet_term = AS_Gbprimepay_API::gen_term_regex($check_scb_truewallet_term,'scb',esc_attr(WC()->cart->total));
            echo $gen_scb_truewallet_term;
            $gen_bbl_truewallet_term = AS_Gbprimepay_API::gen_term_regex($check_bbl_truewallet_term,'bbl',esc_attr(WC()->cart->total));
            echo $gen_bbl_truewallet_term;
                echo '</select>
        </p><p class="form-row form-row-wide">
        <label>Issuers Bank&nbsp;<span class="required">*</span></label>
        <select style="display:block;" id="' . esc_attr($this->id) . '-bankcode" name="' . esc_attr($this->id) . '-bankcode" class="form-control">
            <option value="" data-keep="true">Card issuer bank..</option>
        </select>
        <label>Terms&nbsp;<span class="required">*</span></label>
        <select style="display:block;" id="' . esc_attr($this->id) . '-term" name="' . esc_attr($this->id) . '-term" class="form-control">
            <option value="" data-keep="true">The number of monthly truewallets..</option>
        </select>
      <div id="' . esc_attr($this->id) . '-info" name="' . esc_attr($this->id) . '-info" class="form-control" style="margin:30px 0 40px 0;"></div>';
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
