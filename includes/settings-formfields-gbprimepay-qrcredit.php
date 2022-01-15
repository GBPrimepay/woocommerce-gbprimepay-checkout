<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
return apply_filters('as_gbprimepay_qrcredit_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'gbprimepay-payment-gateways-qrcredit' ),
            'label'       => __( 'Pay with QR Visa', 'gbprimepay-payment-gateways-qrcredit' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => __( 'Title', 'gbprimepay-payment-gateways-qrcredit' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'gbprimepay-payment-gateways-qrcredit' ),
            'default'     => __( 'Pay with QR Visa', 'gbprimepay-payment-gateways-qrcredit' ),
            'desc_tip'    => true,
        ),
        'description2' => array(
            'title'       => __( 'Description', 'gbprimepay-payment-gateways-qrcredit' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'gbprimepay-payment-gateways-qrcredit' ),
            'default'     => __( '' ),
            'desc_tip'    => true,
        ),
    )
);
