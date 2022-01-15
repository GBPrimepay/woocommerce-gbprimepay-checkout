<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
return apply_filters('as_gbprimepay_mbanking_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'gbprimepay-payment-gateways-mbanking' ),
            'label'       => __( 'Pay with Mobile Banking', 'gbprimepay-payment-gateways-mbanking' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => __( 'Title', 'gbprimepay-payment-gateways-mbanking' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'gbprimepay-payment-gateways-mbanking' ),
            'default'     => __( 'Pay with Mobile Banking', 'gbprimepay-payment-gateways-mbanking' ),
            'desc_tip'    => true,
        ),
        'description2' => array(
            'title'       => __( 'Description', 'gbprimepay-payment-gateways-mbanking' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'gbprimepay-payment-gateways-mbanking' ),
            'default'     => __( '' ),
            'desc_tip'    => true,
        ),
    )
);
