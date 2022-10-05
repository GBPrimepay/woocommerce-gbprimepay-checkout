<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( ! class_exists( 'AS_Gbprimepay_ACCOUNT' ) ) :
function gbprimepay_settings() {
	class AS_Gbprimepay_ACCOUNT extends WC_Settings_Page {
    public $notices = array();
		public function __construct() {
			$this->id    = 'gbprimepay_settings';
			$this->label = __( 'GBPrimePay Settings', 'gbprimepay-payment-gateways' );
      add_action( 'admin_notices', array( $this, 'admin_notices' ), 25 );
			add_filter( 'woocommerce_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id,      array( $this, 'output' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'woocommerce_sections_' . $this->id,      array( $this, 'output_sections' ) );
			add_action( 'woocommerce_settings__update_currency_' . $this->id,        array( $this, '_update_currency' ), 10, 3);
		}
		public function get_sections() {
			$sections = array(
				''         => __( 'setting', 'gbprimepay-payment-gateways' )
			);
			return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
		}
		public function get_settings( $current_section = '' ) {
				$settings = apply_filters( 'as_gbprimepay_account_settings', array(
          'section_title' => array(
              'name'     => __( '', 'woocommerce-gbprimepay-settings' ),
              'type'     => 'title',
              'desc'     => 'GBPrimePay Account Settings<hr>',
              'id'       => 'gbprimepay_account_settings_section'
          ),
          'environment'         => array(
            'title'       => __( 'Environment', 'gbprimepay-payment-gateways' ),
            'type'        => 'select',
            'class'       => 'wc-enhanced-select',
            'desc_tip' => __( 'Set The Test Mode or Production Mode', 'gbprimepay-payment-gateways' ),
            'default'     => 'prelive',
            'options'     => array(
              'prelive'          => __( 'Test Mode', 'gbprimepay-payment-gateways' ),
              'production' => __( 'Production Mode', 'gbprimepay-payment-gateways' ),
            ),
            'id'   => 'gbprimepay_account_settings[environment]'
          ),
          'live_public_key' => array(
              'title'       => __( 'Production Public Key', 'gbprimepay-payment-gateways' ),
              'type'        => 'text',
              'desc_tip' => __( 'Get your Public Key credentials from GB Prime Pay.', 'gbprimepay-payment-gateways' ),
              'default'     => __( '', 'gbprimepay-payment-gateways' ),
              'id'   => 'gbprimepay_account_settings[live_public_key]'
          ),
          'live_secret_key' => array(
              'title'       => __( 'Production Secret Key', 'gbprimepay-payment-gateways' ),
              'type'        => 'text',
              'desc_tip' => __( 'Get your Secret Key credentials from GB Prime Pay.', 'gbprimepay-payment-gateways' ),
              'default'     => __( '', 'gbprimepay-payment-gateways' ),
              'id'   => 'gbprimepay_account_settings[live_secret_key]'
          ),
          'live_token_key'     => array(
            'title'       => __( 'Production Token', 'gbprimepay-payment-gateways' ),
            'type'        => 'textarea',
            'css'         => 'width:90%;',
            'desc_tip' => __( 'Get your Token Key credentials from GB Prime Pay.', 'gbprimepay-payment-gateways' ),
            'default'     => __( '', 'gbprimepay-payment-gateways' ),
            'id'   => 'gbprimepay_account_settings[live_token_key]'
          ),
          'test_public_key' => array(
              'title'       => __( 'Test Public Key', 'gbprimepay-payment-gateways' ),
              'type'        => 'text',
              'desc_tip' => __( 'Get your Public Key credentials from GB Prime Pay.', 'gbprimepay-payment-gateways' ),
              'default'     => __( '', 'gbprimepay-payment-gateways' ),
              'id'   => 'gbprimepay_account_settings[test_public_key]'
          ),
          'test_secret_key' => array(
              'title'       => __( 'Test Secret Key', 'gbprimepay-payment-gateways' ),
              'type'        => 'text',
              'desc_tip' => __( 'Get your Secret Key credentials from GB Prime Pay.', 'gbprimepay-payment-gateways' ),
              'default'     => __( '', 'gbprimepay-payment-gateways' ),
              'id'   => 'gbprimepay_account_settings[test_secret_key]'
          ),
          'test_token_key'     => array(
            'title'       => __( 'Test Token', 'gbprimepay-payment-gateways' ),
            'type'        => 'textarea',
            'css'         => 'width:90%;',
            'desc_tip' => __( 'Get your Token Key credentials from GB Prime Pay.', 'gbprimepay-payment-gateways' ),
            'default'     => __( '', 'gbprimepay-payment-gateways' ),
            'id'   => 'gbprimepay_account_settings[test_token_key]'
          ),
          'Logging'     => array(
              'title'       => __( 'Logging', 'gbprimepay-payment-gateways' ),
              'desc'       => __( 'Enable debug logging', 'gbprimepay-payment-gateways' ),
              'type'        => 'checkbox',
              'default'     => 'no',
              'desc_tip'    => __( 'Save debug messages to the WooCommerce System Status log.', 'gbprimepay-payment-gateways' ),
              'id'   => 'gbprimepay_account_settings[logging]'
          ),
					'sectionend'     => array(
						'type' => 'sectionend',
						'id'   => 'gbprimepay_account_settings_section'
					),
				) );
			return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
		}
		public function output() {
			global $current_section;
			$settings = $this->get_settings( $current_section );
      self::gbprimepay_top();
			WC_Admin_Settings::output_fields( $settings );
      self::availablemethods();
      echo '<p class="description" style="font-size: 12px;">Save changes & Verified, GBPrimePay Payments Settings.</p>';
		}
    public function notice_message($message) {
      // echo $message;
      // exit;
    $account_settings = get_option('gbprimepay_account_settings');
    if($account_settings['environment']=='prelive'){
      $echopaymode = sprintf(__('Test Mode'));
    }else{
      $echopaymode = sprintf(__('Production Mode'));
    }
        switch ($message) {
          case 0:
self::add_admin_notice( 'gbprimepay_prompt_connect', 'notice notice-success', 'defective', sprintf( __( 'Verified, GBPrimePay Payments Settings is already set in '.$echopaymode.'.', 'gbprimepay-payment-gateways' ), '' ) );
          break;
          case 2:
          break;
          case 3:
self::add_admin_notice( 'gbprimepay_prompt_connect', 'notice notice-error', 'defective', sprintf( __( 'Error!, Missing credentials in config in '.$echopaymode.'.', 'gbprimepay-payment-gateways' ), '' ) );
          break;
          default:
          break;
        }
  }
    public function gbprimepay_top() {
$echocode = ''."\r\n";
$echocode .= ''."\r\n";
$echocode .= '<img style="margin:15px 0px 0px -12px !important;" src="'.plugin_dir_url( __DIR__ ).'assets/images/gbprimepay-logo.png'.'" alt="gbprimepay.com">'."\r\n";
$echocode .= '<h2>GBPrimePay Payments<small class="wc-admin-breadcrumb"><a href="admin.php?page=wc-settings&amp;tab=checkout" aria-label="Return to payments"><img draggable="false" class="emoji" alt="?" src="https://s.w.org/images/core/emoji/11/svg/2934.svg"></a></small></h2>'."\r\n";
echo $echocode;
    }
    public function availablemethods() {
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
        if(gbp_instances('3D_SECURE_PAYMENT')==TRUE){
            if($account_settings['environment']=='prelive'){
              $ccintegration = sprintf(__('3-D Secure Credit Card Payment Gateway with GBPrimePay'));
            }else{
              $ccintegration = sprintf(__('3-D Secure Credit Card Payment Gateway with GBPrimePay'));
            }
        }else{
          $ccintegration = sprintf(__('Credit Card integration with GBPrimePay'));
        }
        if($account_settings['environment']=='prelive'){
          $echopaymode = sprintf(__('Test Mode'));
        }else{
          $echopaymode = sprintf(__('Production Mode'));
        }
        if ($payment_settings_checkout['enabled'] === 'yes') {
          $echoenabledpayment_checkout = '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled">Yes</span>';
        }else{
          $echoenabledpayment_checkout = '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled">No</span>';
        }
        if ($payment_settings['enabled'] === 'yes') {
          $echoenabledpayment = '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled">Yes</span>';
        }else{
          $echoenabledpayment = '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled">No</span>';
        }
        if ($payment_settings_installment['enabled'] === 'yes') {
          $echoenabledpayment_installment = '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled">Yes</span>';
        }else{
          $echoenabledpayment_installment = '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled">No</span>';
        }
        if ($payment_settings_qrcode['enabled'] === 'yes') {
          $echoenabledpayment_qrcode = '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled">Yes</span>';
        }else{
          $echoenabledpayment_qrcode = '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled">No</span>';
        }
        if ($payment_settings_qrcredit['enabled'] === 'yes') {
          $echoenabledpayment_qrcredit = '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled">Yes</span>';
        }else{
          $echoenabledpayment_qrcredit = '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled">No</span>';
        }
        if ($payment_settings_qrwechat['enabled'] === 'yes') {
          $echoenabledpayment_qrwechat = '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled">Yes</span>';
        }else{
          $echoenabledpayment_qrwechat = '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled">No</span>';
        }
        if ($payment_settings_linepay['enabled'] === 'yes') {
          $echoenabledpayment_linepay = '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled">Yes</span>';
        }else{
          $echoenabledpayment_linepay = '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled">No</span>';
        }
        if ($payment_settings_truewallet['enabled'] === 'yes') {
          $echoenabledpayment_truewallet = '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled">Yes</span>';
        }else{
          $echoenabledpayment_truewallet = '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled">No</span>';
        }
        if ($payment_settings_mbanking['enabled'] === 'yes') {
          $echoenabledpayment_mbanking = '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled">Yes</span>';
        }else{
          $echoenabledpayment_mbanking = '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled">No</span>';
        }
        if ($payment_settings_atome['enabled'] === 'yes') {
          $echoenabledpayment_atome = '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled">Yes</span>';
        }else{
          $echoenabledpayment_atome = '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled">No</span>';
        }
        if ($payment_settings_shopeepay['enabled'] === 'yes') {
          $echoenabledpayment_shopeepay = '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled">Yes</span>';
        }else{
          $echoenabledpayment_shopeepay = '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled">No</span>';
        }
        if ($payment_settings_barcode['enabled'] === 'yes') {
          $echoenabledpayment_barcode = '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled">Yes</span>';
        }else{
          $echoenabledpayment_barcode = '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled">No</span>';
        }
        $echocode = ''."\r\n";
        $echocode .= '<div class="wrap">'."\r\n";
        $echocode .= '<hr><h3>Payment Methods ('.$echopaymode.')</h3>'."\r\n";
             $echocode .= '<table class="widefat fixed striped" cellspacing="0" style="width:60%;min-width:640px;">'."\r\n";
        							$echocode .= '<thead>'."\r\n";
        								$echocode .= '<tr>'."\r\n";
        									$echocode .= '<th style="padding: 10px;width:50%;" class="name">Payment Method</th>'."\r\n";
                          $echocode .= '<th style="text-align: center; padding: 10px;" class="status">Status</th>'."\r\n";
                          $echocode .= '<th style="text-align: center; padding: 10px;" class="setting"></th>'."\r\n";
                        $echocode .= '</tr>'."\r\n";
        							$echocode .= '</thead>'."\r\n";
        							$echocode .= '<tbody>'."\r\n";
                      
                      $echocode .= '<tr>'."\r\n";
                        $echocode .= '<td class="name" style="padding-left: 30px;">'."\r\n";
                                $echocode .= '<span id="span-for-active-button">GBPrimePay Checkout</span>'."\r\n";
                              $echocode .= '</td>'."\r\n";
                        $echocode .= '<td class="status" style="text-align: center;">'."\r\n";
                          $echocode .= $echoenabledpayment_checkout;
                        $echocode .= '</td><td class="setting" style="text-align: center;">'."\r\n";
                                $echocode .= '<a href="admin.php?page=wc-settings&amp;tab=checkout&amp;section=gbprimepay_checkout">Configuration</a>'."\r\n";
                        $echocode .= '</td>'."\r\n";
                      $echocode .= '</tr>'."\r\n";

        								$echocode .= '<tr>'."\r\n";
                          $echocode .= '<td class="name" style="padding-left: 30px;">'."\r\n";
        													$echocode .= '<span id="span-for-active-button">'.$ccintegration.'</span>'."\r\n";
        												$echocode .= '</td>'."\r\n";
                          $echocode .= '<td class="status" style="text-align: center;">'."\r\n";
                            $echocode .= $echoenabledpayment;
                          $echocode .= '</td><td class="setting" style="text-align: center;">'."\r\n";
        													$echocode .= '<a href="admin.php?page=wc-settings&amp;tab=checkout&amp;section=gbprimepay">Configuration</a>'."\r\n";
        									$echocode .= '</td>'."\r\n";
                        $echocode .= '</tr>'."\r\n";

                        $echocode .= '<tr>'."\r\n";
                          $echocode .= '<td class="name" style="padding-left: 30px;">'."\r\n";
        													$echocode .= '<span id="span-for-active-button">Credit Card Installment integration with GBPrimePay</span>'."\r\n";
        												$echocode .= '</td>'."\r\n";
                          $echocode .= '<td class="status" style="text-align: center;">'."\r\n";
                            $echocode .= $echoenabledpayment_installment;
                          $echocode .= '</td><td class="setting" style="text-align: center;">'."\r\n";
        													$echocode .= '<a href="admin.php?page=wc-settings&amp;tab=checkout&amp;section=gbprimepay_installment">Configuration</a>'."\r\n";
        									$echocode .= '</td>'."\r\n";
                        $echocode .= '</tr>'."\r\n";

                        $echocode .= '<tr>'."\r\n";
                          $echocode .= '<td class="name" style="padding-left: 30px;">'."\r\n";
        													$echocode .= '<span id="span-for-active-button">QR Code integration with GBPrimePay</span>'."\r\n";
        												$echocode .= '</td>'."\r\n";
                          $echocode .= '<td class="status" style="text-align: center;">'."\r\n";
                            $echocode .= $echoenabledpayment_qrcode;
                          $echocode .= '</td><td class="setting" style="text-align: center;">'."\r\n";
        													$echocode .= '<a href="admin.php?page=wc-settings&amp;tab=checkout&amp;section=gbprimepay_qrcode">Configuration</a>'."\r\n";
        									$echocode .= '</td>'."\r\n";
                        $echocode .= '</tr>'."\r\n";

                        $echocode .= '<tr>'."\r\n";
                          $echocode .= '<td class="name" style="padding-left: 30px;">'."\r\n";
        													$echocode .= '<span id="span-for-active-button">QR Visa integration with GBPrimePay</span>'."\r\n";
        												$echocode .= '</td>'."\r\n";
                          $echocode .= '<td class="status" style="text-align: center;">'."\r\n";
                            $echocode .= $echoenabledpayment_qrcredit;
                          $echocode .= '</td><td class="setting" style="text-align: center;">'."\r\n";
        													$echocode .= '<a href="admin.php?page=wc-settings&amp;tab=checkout&amp;section=gbprimepay_qrcredit">Configuration</a>'."\r\n";
        									$echocode .= '</td>'."\r\n";
                        $echocode .= '</tr>'."\r\n";

                        $echocode .= '<tr>'."\r\n";
                          $echocode .= '<td class="name" style="padding-left: 30px;">'."\r\n";
        													$echocode .= '<span id="span-for-active-button">QR Wechat integration with GBPrimePay</span>'."\r\n";
        												$echocode .= '</td>'."\r\n";
                          $echocode .= '<td class="status" style="text-align: center;">'."\r\n";
                            $echocode .= $echoenabledpayment_qrwechat;
                          $echocode .= '</td><td class="setting" style="text-align: center;">'."\r\n";
        													$echocode .= '<a href="admin.php?page=wc-settings&amp;tab=checkout&amp;section=gbprimepay_qrwechat">Configuration</a>'."\r\n";
        									$echocode .= '</td>'."\r\n";
                        $echocode .= '</tr>'."\r\n";

                        $echocode .= '<tr>'."\r\n";
                          $echocode .= '<td class="name" style="padding-left: 30px;">'."\r\n";
        													$echocode .= '<span id="span-for-active-button">Rabbit Line Pay integration with GBPrimePay</span>'."\r\n";
        												$echocode .= '</td>'."\r\n";
                          $echocode .= '<td class="status" style="text-align: center;">'."\r\n";
                            $echocode .= $echoenabledpayment_linepay;
                          $echocode .= '</td><td class="setting" style="text-align: center;">'."\r\n";
        													$echocode .= '<a href="admin.php?page=wc-settings&amp;tab=checkout&amp;section=gbprimepay_linepay">Configuration</a>'."\r\n";
        									$echocode .= '</td>'."\r\n";
                        $echocode .= '</tr>'."\r\n";

                        $echocode .= '<tr>'."\r\n";
                          $echocode .= '<td class="name" style="padding-left: 30px;">'."\r\n";
        													$echocode .= '<span id="span-for-active-button">TrueMoney Wallet integration with GBPrimePay</span>'."\r\n";
        												$echocode .= '</td>'."\r\n";
                          $echocode .= '<td class="status" style="text-align: center;">'."\r\n";
                            $echocode .= $echoenabledpayment_truewallet;
                          $echocode .= '</td><td class="setting" style="text-align: center;">'."\r\n";
        													$echocode .= '<a href="admin.php?page=wc-settings&amp;tab=checkout&amp;section=gbprimepay_truewallet">Configuration</a>'."\r\n";
        									$echocode .= '</td>'."\r\n";
                        $echocode .= '</tr>'."\r\n";

                        $echocode .= '<tr>'."\r\n";
                          $echocode .= '<td class="name" style="padding-left: 30px;">'."\r\n";
        													$echocode .= '<span id="span-for-active-button">Mobile Banking integration with GBPrimePay</span>'."\r\n";
        												$echocode .= '</td>'."\r\n";
                          $echocode .= '<td class="status" style="text-align: center;">'."\r\n";
                            $echocode .= $echoenabledpayment_mbanking;
                          $echocode .= '</td><td class="setting" style="text-align: center;">'."\r\n";
        													$echocode .= '<a href="admin.php?page=wc-settings&amp;tab=checkout&amp;section=gbprimepay_mbanking">Configuration</a>'."\r\n";
        									$echocode .= '</td>'."\r\n";
                        $echocode .= '</tr>'."\r\n";


                        $echocode .= '<tr>'."\r\n";
                          $echocode .= '<td class="name" style="padding-left: 30px;">'."\r\n";
        													$echocode .= '<span id="span-for-active-button">Atome integration with GBPrimePay</span>'."\r\n";
        												$echocode .= '</td>'."\r\n";
                          $echocode .= '<td class="status" style="text-align: center;">'."\r\n";
                            $echocode .= $echoenabledpayment_atome;
                          $echocode .= '</td><td class="setting" style="text-align: center;">'."\r\n";
        													$echocode .= '<a href="admin.php?page=wc-settings&amp;tab=checkout&amp;section=gbprimepay_atome">Configuration</a>'."\r\n";
        									$echocode .= '</td>'."\r\n";
                        $echocode .= '</tr>'."\r\n";


                        $echocode .= '<tr>'."\r\n";
                          $echocode .= '<td class="name" style="padding-left: 30px;">'."\r\n";
        													$echocode .= '<span id="span-for-active-button">ShopeePay integration with GBPrimePay</span>'."\r\n";
        												$echocode .= '</td>'."\r\n";
                          $echocode .= '<td class="status" style="text-align: center;">'."\r\n";
                            $echocode .= $echoenabledpayment_shopeepay;
                          $echocode .= '</td><td class="setting" style="text-align: center;">'."\r\n";
        													$echocode .= '<a href="admin.php?page=wc-settings&amp;tab=checkout&amp;section=gbprimepay_shopeepay">Configuration</a>'."\r\n";
        									$echocode .= '</td>'."\r\n";
                        $echocode .= '</tr>'."\r\n";


                        $echocode .= '<tr>'."\r\n";
                          $echocode .= '<td class="name" style="padding-left: 30px;">'."\r\n";
        													$echocode .= '<span id="span-for-active-button">Bill Payment integration with GBPrimePay</span>'."\r\n";
        												$echocode .= '</td>'."\r\n";
                          $echocode .= '<td class="status" style="text-align: center;">'."\r\n";
                            $echocode .= $echoenabledpayment_barcode;
                          $echocode .= '</td><td class="setting" style="text-align: center;">'."\r\n";
        													$echocode .= '<a href="admin.php?page=wc-settings&amp;tab=checkout&amp;section=gbprimepay_barcode">Configuration</a>'."\r\n";
        									$echocode .= '</td>'."\r\n";
                        $echocode .= '</tr>'."\r\n";

                      $echocode .= '</tbody>'."\r\n";
        						$echocode .= '</table>'."\r\n";
            $echocode .= '<br>'."\r\n";
            $echocode .= '<hr>'."\r\n";
           $echocode .= '</div>'."\r\n";
        echo $echocode;
}
public function add_admin_notice( $slug, $class, $style, $message ) {
  $this->notices[ $slug ] = array(
    'class'   => $class,
    'style' => $style,
    'message' => $message,
  );
}
private static function _chk_currency($currencyCode) {  
  $get_as_gbprimepay_currency = get_option('as_gbprimepay_currency');
  if ( isset( $get_as_gbprimepay_currency ) ) {
    if($get_as_gbprimepay_currency != $currencyCode){
      self::_update_currency($currencyCode);
    }
  }else{    
      self::_update_currency($currencyCode);
  }
  return true;
}
private static function _update_currency($currencyCode) {
  update_option( 'as_gbprimepay_currency', $currencyCode );  
}
public function admin_notices() {
  foreach ( (array) $this->notices as $notice_key => $notice ) {
    if($notice['style']=='defective'){
    echo "<div class='" . esc_attr( $notice['class'] ) . "'  style='padding: 12px 12px;'><p><strong>";
    echo wp_kses( $notice['message'], array(
      'a' => array(
        'href' => array(),
      ),
    ) );
      echo '</strong></p></div>';
  }else{
    echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
    echo wp_kses( $notice['message'], array(
      'a' => array(
        'href' => array(),
      ),
    ) );
    echo '</p></div>';
  }
  }
}
		public function save() {
			global $current_section;
			$settings = $this->get_settings( $current_section );
			WC_Admin_Settings::save_fields( $settings );
      $checked_check_save_verified = AS_Gbprimepay_API::check_save_verified();
      self::notice_message($checked_check_save_verified);
      if($checked_check_save_verified==0){
        $account_settings = get_option('gbprimepay_account_settings');
        if ($account_settings['environment'] === 'prelive') {
            $url = gbp_instances('URL_MERCHANT_TEST');
        } else {
            $url = gbp_instances('URL_MERCHANT_LIVE');
        }
        $merchant_data = AS_Gbprimepay_API::sendMerchantCurl("$url", [], 'GET');
        $currencyCode = $merchant_data['currency_code'];
        self::_chk_currency($currencyCode);
      }
		}
	}
	return new AS_Gbprimepay_ACCOUNT();
}
add_filter( 'woocommerce_get_settings_pages', 'gbprimepay_settings', 15 );
endif;
