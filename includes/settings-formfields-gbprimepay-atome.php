<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
return apply_filters('as_gbprimepay_atome_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'gbprimepay-payment-gateways-atome' ),
            'label'       => __( 'Pay with Atome', 'gbprimepay-payment-gateways-atome' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => __( 'Title', 'gbprimepay-payment-gateways-atome' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'gbprimepay-payment-gateways-atome' ),
            'default'     => __( 'Pay with Atome', 'gbprimepay-payment-gateways-atome' ),
            'desc_tip'    => true,
        ),
        'description2' => array(
            'title'       => __( 'Description', 'gbprimepay-payment-gateways-atome' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'gbprimepay-payment-gateways-atome' ),
            'default'     => __( '' ),
            'desc_tip'    => true,
        ),
    )
);
