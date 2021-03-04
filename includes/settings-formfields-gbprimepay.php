<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return apply_filters('as_gbprimepay_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'gbprimepay-payment-gateways' ),
            'label'       => __( 'Pay with Credit Card'.get_option('gbprimepay_settings[live_public_key]'), 'gbprimepay-payment-gateways' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => __( 'Title', 'gbprimepay-payment-gateways' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'gbprimepay-payment-gateways' ),
            'default'     => __( 'Pay with Credit Card', 'gbprimepay-payment-gateways' ),
            'desc_tip'    => true,
        ),
        'description1' => array(
            'title'       => __( 'Description', 'gbprimepay-payment-gateways' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'gbprimepay-payment-gateways' ),
            'default'     => __( '' ),
            'desc_tip'    => true,
        ),
    )
);
