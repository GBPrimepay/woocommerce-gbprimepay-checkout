<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
return apply_filters('as_gbprimepay_qrwechat_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'gbprimepay-payment-gateways-qrwechat' ),
            'label'       => __( 'Pay with QR Wechat', 'gbprimepay-payment-gateways-qrwechat' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => __( 'Title', 'gbprimepay-payment-gateways-qrwechat' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'gbprimepay-payment-gateways-qrwechat' ),
            'default'     => __( 'Pay with QR Wechat', 'gbprimepay-payment-gateways-qrwechat' ),
            'desc_tip'    => true,
        ),
        'description2' => array(
            'title'       => __( 'Description', 'gbprimepay-payment-gateways-qrwechat' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'gbprimepay-payment-gateways-qrwechat' ),
            'default'     => __( '' ),
            'desc_tip'    => true,
        ),
    )
);
