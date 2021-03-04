<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return apply_filters('as_gbprimepay_qrcode_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'gbprimepay-payment-gateways-qrcode' ),
            'label'       => __( 'Pay with QR Code', 'gbprimepay-payment-gateways-qrcode' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => __( 'Title', 'gbprimepay-payment-gateways-qrcode' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'gbprimepay-payment-gateways-qrcode' ),
            'default'     => __( 'Pay with QR Code', 'gbprimepay-payment-gateways-qrcode' ),
            'desc_tip'    => true,
        ),
        'description2' => array(
            'title'       => __( 'Description', 'gbprimepay-payment-gateways-qrcode' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'gbprimepay-payment-gateways-qrcode' ),
            'default'     => __( '' ),
            'desc_tip'    => true,
        ),
    )
);
