<?php
class AS_Gateway_Gbprimepay_Installment extends WC_Payment_Gateway_eCheck
{
    public $environment;
    public $description2;
    public function __construct()
    {
        $this->id = 'gbprimepay_installment';
        $this->method_title = __('GBPrimePay Credit Card Installment', 'gbprimepay-payment-gateways-installment');
        $this->method_description = sprintf(__('Credit Card Installment integration with GBPrimePay'));
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
        $this->payment_settings_installment = get_option('gbprimepay_payment_settings_installment');
        $this->payment_settings_checkout = get_option('gbprimepay_payment_settings_checkout');
        $this->title = $this->payment_settings_installment['title'];
        $this->description2 = $this->payment_settings_installment['description2'];
        $this->environment = $this->account_settings['environment'];
        update_option('gbprimepay_payment_settings_installment', $this->settings);
        // Add hooks
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts')); // not yet use this
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action( 'init', array( $this, 'installment_callback_handler' ) );
        add_action( 'woocommerce_api_'. strtolower( get_class($this) ), array( $this, 'installment_callback_handler' ) );
    }
    public function init_form_fields()
    {
        $this->form_fields = include('settings-formfields-gbprimepay-installment.php');
    }
    /**
     * Check if this gateway is enabled
     */
    public function is_available()
    {
        if ($this->payment_settings_installment['enabled'] === 'yes') {
          $all_installment_term = $this->payment_settings_installment['kasikorn_installment_term'].', '.$this->payment_settings_installment['krungthai_installment_term'].', '.$this->payment_settings_installment['thanachart_installment_term'].', '.$this->payment_settings_installment['ayudhya_installment_term'].', '.$this->payment_settings_installment['firstchoice_installment_term'].', '.$this->payment_settings_installment['scb_installment_term'].', '.$this->payment_settings_installment['bbl_installment_term'];
          $all_arrterm_check = explode(',',preg_replace('/\s+/', '', $all_installment_term));
          $all_arrterm_pass = (array_filter($all_arrterm_check));
          $_wc_cart_total = !empty(WC()->cart->total) ? WC()->cart->total : null;
          if(($_wc_cart_total >= 3000) && (($_wc_cart_total/(min($all_arrterm_pass))) >= 500)){
            // return true;
          if ($this->payment_settings_checkout['enabled'] === 'yes') {
            return false;
          }
          }else{
            return false;
          }
        }
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
            $pay_button_text = __('Add Card', 'gbprimepay-payment-gateways-installment');
            $total = '';
        } else {
            $pay_button_text = '';
        }
        echo '<div
			id="gbprimepay-payment-installment-data"
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
            if ($postData['payment_method']=="gbprimepay_installment") {
              if (!empty($postData['gbprimepay_installment-bankcode']) && (!empty($postData['gbprimepay_installment-term']))) {
                  $order->add_order_note('Order created and status set to Pending payment.');
                  $order->update_status('pending', __( 'Awaiting Credit Card Installment integration with GBPrimePay.', 'gbprimepay-payment-gateways' ));
                    $account_settings = get_option('gbprimepay_account_settings');
                    if ($account_settings['environment'] === 'prelive') {
                        $installment_url = gbp_instances('URL_INSTALLMENT_TEST');
                        $installment_publicKey = $account_settings['test_public_key'];
                        $installment_secret_key = $account_settings['test_secret_key'];
                    } else {
                        $installment_url = gbp_instances('URL_INSTALLMENT_LIVE');
                        $installment_publicKey = $account_settings['live_public_key'];
                        $installment_secret_key = $account_settings['live_secret_key'];
                    }
                    $amount = $order->get_total();
                    $installment_amount = number_format((($amount * 100)/100), 2, '.', '');
                    $installment_bankCode = $postData['gbprimepay_installment-bankcode'];
                    $installment_term = $postData['gbprimepay_installment-term'];
                    $installment_detail = 'Charge for order ' . $order->get_order_number();
                    $installment_customerName = '' . $order->get_billing_first_name(). ' ' .$order->get_billing_last_name();
                    $installment_customerEmail = '' . $order->get_billing_email();
                    $installment_customerAddress = '' . str_replace("<br/>", " ", $order->get_formatted_billing_address());
                    $installment_customerTelephone = '' . $order->get_billing_phone();
                    $installment_referenceNo = ''.substr(time(), 4, 5).'00'.$order->get_order_number();
                    $installment_responseUrl = $this->get_return_url($order);
                    $installment_backgroundUrl = home_url()."/" . 'wc-api/AS_Gateway_Gbprimepay_Installment/';
                    $callgenerateID = AS_Gbprimepay_API::generateID();
                    $RedirectURL =  add_query_arg(
                                    array(
                                        'page' => rawurlencode($installment_url),
                                        'publicKey' => rawurlencode($installment_publicKey),
                                        'referenceNo' => rawurlencode($installment_referenceNo),
                                        'responseUrl' => rawurlencode($installment_responseUrl),
                                        'backgroundUrl' => rawurlencode($installment_backgroundUrl),
                                        'detail' => rawurlencode($installment_detail),
                                        'customerName' => rawurlencode($installment_customerName),
                                        'customerEmail' => rawurlencode($installment_customerEmail),
                                        'customerAddress' => rawurlencode($installment_customerAddress),
                                        'customerTelephone' => rawurlencode($installment_customerTelephone),
                                        'amount' => rawurlencode($installment_amount),
                                        'bankCode' => rawurlencode($installment_bankCode),
                                        'term' => rawurlencode($installment_term),
                                        'merchantDefined1' => rawurlencode($callgenerateID),
                                        'merchantDefined2' => rawurlencode(''),
                                        'merchantDefined3' => rawurlencode($installment_referenceNo),
                                        'merchantDefined4' => rawurlencode(''),
                                        'merchantDefined5' => rawurlencode(''),
                                        'secret_key' => rawurlencode($installment_secret_key)
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
        wp_enqueue_script('gbprimepay_installment', plugin_dir_url( __DIR__ ) .'assets/js/gbprimepay-installment.js', '', '', true );
    }
  public function installment_callback_handler() {
              $postData = $_POST;
              $referenceNo = $postData['referenceNo'];
              $order_id = substr($postData['referenceNo'], 7);
              $order = wc_get_order($order_id);
                if ( isset( $postData['resultCode'] ) ) {
                          if ($postData['resultCode'] == '00') {
                                  $order->payment_complete($postData['gbpReferenceNo']);
                                  update_post_meta($order_id, 'Gbprimepay Charge ID', $postData['merchantDefined1']);
                                  $order->add_order_note(
                                    __( 'GBPrimePay Credit Card Installment Payment Authorized.', 'gbprimepay-payment-gateways' ) . PHP_EOL .
                                    __( 'Transaction ID: ', 'gbprimepay-payment-gateways' ) . $postData['gbpReferenceNo'] . PHP_EOL .
                                    __( 'Monthly: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amountPerMonth']) .' x '. $postData['payMonth'] . PHP_EOL .
                                    __( 'Payment Amount: ', 'gbprimepay-payment-gateways' ) . wc_price($postData['amount'])
                                  );
                          }else{
                                  $order->update_status( 'failed', sprintf( __( 'GBPrimePay Credit Card Installment Payment failed.', 'gbprimepay-payment-gateways' ) ) );
                          }
                      AS_Gbprimepay::log(  'Credit Card Installment Callback Handler: ' . print_r( $postData, true ) );
                }
  }
  public function form()
  {
    echo '<style>
      #wc-gbprimepay-installment-form select { margin: .75rem auto; text-indent: 1px;width:100% !important;}
      #wc-gbprimepay-installment-form option {
          padding: 0px 2px 1px;
      }
      #wc-gbprimepay-installment-form .container { margin: 150px auto; }
      #wc-gbprimepay-installment-form select.form-control {
          padding: 6px 10px;
      }
      #wc-gbprimepay-installment-form option {
          padding: 12px 12px;
      }
      #wc-gbprimepay-installment-form select.form-control {
          padding: 12px 12px;
      }
      </style>';
      $check_kasikorn_installment_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_installment['kasikorn_installment_term'],'kasikorn');
      $check_krungthai_installment_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_installment['krungthai_installment_term'],'krungthai');
      $check_thanachart_installment_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_installment['thanachart_installment_term'],'thanachart');
      $check_ayudhya_installment_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_installment['ayudhya_installment_term'],'ayudhya');
      $check_firstchoice_installment_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_installment['firstchoice_installment_term'],'firstchoice');
      $check_scb_installment_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_installment['scb_installment_term'],'scb');
      $check_bbl_installment_term = AS_Gbprimepay_API::check_term_regex($this->payment_settings_installment['bbl_installment_term'],'bbl');
     echo '<fieldset id="wc-gbprimepay-installment-form" class="wc-credit-card-form wc-payment-form">
                             <p class="form-row form-row-wide">';
      echo '<select style="display:none;" id="' . esc_attr($this->id) . '-CCInstallmentToSelect" data-bankcode="#' . esc_attr($this->id) . '-bankcode" data-term="#' . esc_attr($this->id) . '-term">
            <option value=""></option>';
            $gen_kasikorn_installment_term = AS_Gbprimepay_API::gen_term_regex($check_kasikorn_installment_term,'kasikorn',esc_attr(WC()->cart->total));
            echo $gen_kasikorn_installment_term;
            $gen_krungthai_installment_term = AS_Gbprimepay_API::gen_term_regex($check_krungthai_installment_term,'krungthai',esc_attr(WC()->cart->total));
            echo $gen_krungthai_installment_term;
            $gen_thanachart_installment_term = AS_Gbprimepay_API::gen_term_regex($check_thanachart_installment_term,'thanachart',esc_attr(WC()->cart->total));
            echo $gen_thanachart_installment_term;
            $gen_ayudhya_installment_term = AS_Gbprimepay_API::gen_term_regex($check_ayudhya_installment_term,'ayudhya',esc_attr(WC()->cart->total));
            echo $gen_ayudhya_installment_term;
            $gen_firstchoice_installment_term = AS_Gbprimepay_API::gen_term_regex($check_firstchoice_installment_term,'firstchoice',esc_attr(WC()->cart->total));
            echo $gen_firstchoice_installment_term;
            $gen_scb_installment_term = AS_Gbprimepay_API::gen_term_regex($check_scb_installment_term,'scb',esc_attr(WC()->cart->total));
            echo $gen_scb_installment_term;
            $gen_bbl_installment_term = AS_Gbprimepay_API::gen_term_regex($check_bbl_installment_term,'bbl',esc_attr(WC()->cart->total));
            echo $gen_bbl_installment_term;
                echo '</select>
        </p><p class="form-row form-row-wide">
        <label>Issuers Bank&nbsp;<span class="required">*</span></label>
        <select style="display:block;" id="' . esc_attr($this->id) . '-bankcode" name="' . esc_attr($this->id) . '-bankcode" class="form-control">
            <option value="" data-keep="true">Card issuer bank..</option>
        </select>
        <label>Terms&nbsp;<span class="required">*</span></label>
        <select style="display:block;" id="' . esc_attr($this->id) . '-term" name="' . esc_attr($this->id) . '-term" class="form-control">
            <option value="" data-keep="true">The number of monthly installments..</option>
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
