<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
return apply_filters('as_gbprimepay_shopeepay_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'gbprimepay-payment-gateways-shopeepay' ),
            'label'       => __( 'Pay with ShopeePay', 'gbprimepay-payment-gateways-shopeepay' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => __( 'Title', 'gbprimepay-payment-gateways-shopeepay' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'gbprimepay-payment-gateways-shopeepay' ),
            'default'     => __( 'Pay with ShopeePay', 'gbprimepay-payment-gateways-shopeepay' ),
            'desc_tip'    => true,
        ),
        'description2' => array(
            'title'       => __( 'Description', 'gbprimepay-payment-gateways-shopeepay' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'gbprimepay-payment-gateways-shopeepay' ),
            'default'     => __( '' ),
            'desc_tip'    => true,
        ),
    )
);
