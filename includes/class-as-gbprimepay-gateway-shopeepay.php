<?php
class AS_Gateway_Gbprimepay_Shopeepay extends WC_Payment_Gateway_eCheck
{
    public $environment;
    public $description2;
    public function __construct()
    {
        $this->id = 'gbprimepay_shopeepay';
        $this->method_title = __('GBPrimePay ShopeePay', 'gbprimepay-payment-gateways-shopeepay');
        $this->method_description = sprintf(__('ShopeePay integration with GBPrimePay'));
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
        $this->payment_settings_shopeepay = get_option('gbprimepay_payment_settings_shopeepay');
        $this->payment_settings_checkout = get_option('gbprimepay_payment_settings_checkout');
        $this->title = $this->payment_settings_shopeepay['title'];
        $this->description2 = $this->payment_settings_shopeepay['description2'];
        $this->environment = $this->account_settings['environment'];
        update_option('gbprimepay_payment_settings_shopeepay', $this->settings);
        // Add hooks
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts')); // not yet use this
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action( 'init', array( $this, 'shopeepay_callback_handler' ) );
        add_action( 'woocommerce_api_'. strtolower( get_class($this) ), array( $this, 'shopeepay_callback_handler' ) );
    }
    public function init_form_fields()
    {
        $this->form_fields = include('settings-formfields-gbprimepay-shopeepay.php');
    }
    /**
     * Check if this gateway is enabled
     */
    public function is_available()
    {
        if ($this->payment_settings_shopeepay['enabled'] === 'yes') {
            // return AS_Gbprimepay_API::get_credentials('shopeepay');
          if ($this->payment_settings_checkout['enabled'] === 'yes') {
            return false;
          }
        }
        return false;
    }
    public function payment_fields()
    {
    }
    function process_payment( $order_id ) {
    }
    public function payment_scripts()
    {
        if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order']) && !is_add_payment_method_page()) {
            return;
        }
        wp_enqueue_script('gbprimepay_shopeepay', plugin_dir_url( __DIR__ ) .'assets/js/gbprimepay-shopeepay.js', '', '', true );
    }
  public function shopeepay_callback_handler() {
  }
  public function form()
  {
  }
    public function send_failed_order_email($order_id)
    {
        $emails = WC()->mailer()->get_emails();
        if (!empty($emails) && !empty($order_id)) {
            $emails['WC_Email_Failed_Order']->trigger($order_id);
        }
    }
}
