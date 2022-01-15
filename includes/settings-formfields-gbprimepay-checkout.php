<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
return apply_filters('as_gbprimepay_checkout_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'gbprimepay-payment-gateways-checkout' ),
            'label'       => __( 'GBPrimePay Checkout', 'gbprimepay-payment-gateways-checkout' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => __( 'Title', 'gbprimepay-payment-gateways-checkout' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'gbprimepay-payment-gateways-checkout' ),
            'default'     => __( 'GBPrimePay Checkout', 'gbprimepay-payment-gateways-checkout' ),
            'desc_tip'    => true,
        ),
        'description2' => array(
            'title'       => __( 'Description', 'gbprimepay-payment-gateways-checkout' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'gbprimepay-payment-gateways-checkout' ),
            'default'     => __( '' ),
            'desc_tip'    => true,
        ),
    )
);
