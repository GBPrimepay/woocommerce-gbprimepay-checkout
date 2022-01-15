<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
return apply_filters('as_gbprimepay_linepay_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'gbprimepay-payment-gateways-linepay' ),
            'label'       => __( 'Pay with Rabbit Line Pay', 'gbprimepay-payment-gateways-linepay' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => __( 'Title', 'gbprimepay-payment-gateways-linepay' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'gbprimepay-payment-gateways-linepay' ),
            'default'     => __( 'Pay with Rabbit Line Pay', 'gbprimepay-payment-gateways-linepay' ),
            'desc_tip'    => true,
        ),
        'description2' => array(
            'title'       => __( 'Description', 'gbprimepay-payment-gateways-linepay' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'gbprimepay-payment-gateways-linepay' ),
            'default'     => __( '' ),
            'desc_tip'    => true,
        ),
    )
);
