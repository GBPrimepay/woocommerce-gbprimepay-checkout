<?php
class AS_Gateway_Gbprimepay_Atome extends WC_Payment_Gateway_eCheck
{
    public $environment;
    public $description2;
    public function __construct()
    {
        $this->id = 'gbprimepay_atome';
        $this->method_title = __('GBPrimePay Atome BNPL', 'gbprimepay-payment-gateways-atome');
        $this->method_description = sprintf(__('Atome BNPL integration with GBPrimePay'));
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
        $this->payment_settings_atome = get_option('gbprimepay_payment_settings_atome');
        $this->payment_settings_checkout = get_option('gbprimepay_payment_settings_checkout');
        $this->title = $this->payment_settings_atome['title'];
        $this->description2 = $this->payment_settings_atome['description2'];
        $this->environment = $this->account_settings['environment'];
        update_option('gbprimepay_payment_settings_atome', $this->settings);
        // Add hooks
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts')); // not yet use this
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action( 'init', array( $this, 'atome_callback_handler' ) );
        add_action( 'woocommerce_api_'. strtolower( get_class($this) ), array( $this, 'atome_callback_handler' ) );
    }
    public function init_form_fields()
    {
        $this->form_fields = include('settings-formfields-gbprimepay-atome.php');
    }
    /**
     * Check if this gateway is enabled
     */
    public function is_available()
    {
        if ($this->payment_settings_atome['enabled'] === 'yes') {
          $_wc_cart_total = !empty(WC()->cart->total) ? WC()->cart->total : null;
          if(($_wc_cart_total >= 20) && ($this->account_settings['environment']=='production')){
            // return true;
          if ($this->payment_settings_checkout['enabled'] === 'yes') {
            return false;
          }
          if ($this->account_settings['environment'] === 'prelive') {
            return false;
          }
          }else{
            return false;
          }
        }
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
        wp_enqueue_script('gbprimepay_atome', plugin_dir_url( __DIR__ ) .'assets/js/gbprimepay-atome.js', '', '', true );
    }
  public function atome_callback_handler() {
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
