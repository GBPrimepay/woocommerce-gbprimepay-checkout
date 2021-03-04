<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return apply_filters('as_gbprimepay_barcode_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'gbprimepay-payment-gateways-barcode' ),
            'label'       => __( 'Pay with Bill Payment', 'gbprimepay-payment-gateways-barcode' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => __( 'Title', 'gbprimepay-payment-gateways-barcode' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'gbprimepay-payment-gateways-barcode' ),
            'default'     => __( 'Pay with Bill Payment', 'gbprimepay-payment-gateways-barcode' ),
            'desc_tip'    => true,
        ),
        'description3' => array(
            'title'       => __( 'Description', 'gbprimepay-payment-gateways-barcode' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'gbprimepay-payment-gateways-barcode' ),
            'default'     => __( '' ),
            'desc_tip'    => true,
        ),
    )
);
