<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
return apply_filters('as_gbprimepay_truewallet_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'gbprimepay-payment-gateways-truewallet' ),
            'label'       => __( 'Pay with TrueMoney Wallet', 'gbprimepay-payment-gateways-truewallet' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => __( 'Title', 'gbprimepay-payment-gateways-truewallet' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'gbprimepay-payment-gateways-truewallet' ),
            'default'     => __( 'Pay with TrueMoney Wallet', 'gbprimepay-payment-gateways-truewallet' ),
            'desc_tip'    => true,
        ),
        'description2' => array(
            'title'       => __( 'Description', 'gbprimepay-payment-gateways-truewallet' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'gbprimepay-payment-gateways-truewallet' ),
            'default'     => __( '' ),
            'desc_tip'    => true,
        ),
    )
);
