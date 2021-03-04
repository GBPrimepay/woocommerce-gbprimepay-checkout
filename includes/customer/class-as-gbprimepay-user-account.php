<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AS_Gbprimepay_User_Account
{
    protected $gbprimepay_user_id = '';
    protected $user_id = '';
    protected $metadata = array();

    /**
     * AS_Gbprimepay_Customer_Abstract constructor.
     * @param int $user_id
     * @param WC_Order $order
     */
    public function __construct($wp_user_id = 0, $order = null)
    {
        include_once(dirname(__FILE__) . '/../class-as-gbprimepay-api.php');

        if ($wp_user_id) {
            $this->set_user_id($wp_user_id);
            // if not exist gbprimepay user id, set it
            $gbprimepayUserId = get_user_meta($wp_user_id, '_gbprimepay_user_id', true);
            if (!$gbprimepayUserId && $order) { // gbprimepay user not exist and will be create through checkout
                $gbprimepayUserId = str_replace('.', '', $order->get_billing_email() . '-' . $this->get_user_id() . '-' . time());
                $gbprimepayApiObj = new AS_Gbprimepay_API();
                $response = $gbprimepayApiObj->createUserWithOrder($gbprimepayUserId, $order);

                if (array_key_exists('id', $response)) {
                    update_user_meta($wp_user_id, '_gbprimepay_user_id', $response['id']);
                    $this->set_gbprimepay_user_id($response['id']);
                    $this->metadata = $response;
                } else {
                    die();
                }
            } else if (!$gbprimepayUserId && !$order) { // gbprimepay user not exist and will be created through add card page
                if (!is_user_logged_in()) {
                    throw new Exception(__('Customer not found.'));
                }
                $wpUser = new WC_Customer($wp_user_id);
                if (!$wpUser->get_email()) {
                    throw new Exception(__('Customer email is not defined'));
                }
                $gbprimepayUserId = str_replace('.', '', $wpUser->get_email() . '-' . $wpUser->get_id() . '-' . time());
                $gbprimepayApiObj = new AS_Gbprimepay_API();
                $response = $gbprimepayApiObj->createUser($wpUser, $gbprimepayUserId);

                if (array_key_exists('id', $response)) {
                    update_user_meta($wp_user_id, '_gbprimepay_user_id', $response['id']);
                    $this->set_gbprimepay_user_id($response['id']);
                    $this->metadata = $response;
                } else {
                    die();
                }
            } else {
                // gbprimepay user already exists
                $this->set_gbprimepay_user_id($gbprimepayUserId);
                $gbprimepayApiObj = new AS_Gbprimepay_API();
                $response = $gbprimepayApiObj->getUserFromGbprimepay($gbprimepayUserId);

                if (array_key_exists('id', $response)) {
                    $this->metadata = $response;
                } else {
                    die();
                }
            }
        }
    }

    public function set_user_id( $user_id ) {
        $this->user_id = absint( $user_id );
    }
    public function get_user_id() {
        return absint( $this->user_id );
    }

    public function set_gbprimepay_user_id($user_id) {
        $this->gbprimepay_user_id = $user_id;
    }
    public function get_gbprimepay_user_id() {
        return $this->gbprimepay_user_id;
    }
    public function get_gbprimepay_full_name() {
        return $this->metadata['first_name'] . ' ' . $this->metadata['last_name'];
    }
}
