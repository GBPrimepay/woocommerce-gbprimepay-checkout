<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
return apply_filters('as_gbprimepay_installment_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'gbprimepay-payment-gateways-installment' ),
            'label'       => __( 'Pay with Credit Card Installment', 'gbprimepay-payment-gateways-installment' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes',
        ),
        'title' => array(
            'title'       => __( 'Title', 'gbprimepay-payment-gateways-installment' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'gbprimepay-payment-gateways-installment' ),
            'default'     => __( 'Pay with Credit Card Installment', 'gbprimepay-payment-gateways-installment' ),
            'desc_tip'    => true,
        ),
        'description2' => array(
            'title'       => __( 'Description', 'gbprimepay-payment-gateways-installment' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'gbprimepay-payment-gateways-installment' ),
            'default'     => __( '' ),
            'desc_tip'    => true,
            'css'    => 'margin-bottom:30px;',
        ),
        array(
  				'title' => 'Issuers Bank/Installment Terms.',
  				'type'  => 'title',
  				'id'    => 'issuers_bank',
          'description' => __( 'Input the desired Installment Terms. Separate with comma.<br>example: 3 months, 6 months, 10 months <br>eg: <b>3, 6, 10</b> ', 'gbprimepay-payment-gateways-installment' ),
  			),
        'kasikorn_installment_term' => array(
          'title' => __( 'KASIKORN', 'gbprimepay-payment-gateways-installment' ),
          'type' => 'text',
          'description' => __( 'Kasikornbank Public Company Limited. <br>Installment Terms. default: <b>3, 4, 5, 6, 7, 8, 9, 10</b>', 'gbprimepay-payment-gateways-installment' ),
          'default' => '3, 4, 5, 6, 7, 8, 9, 10'
        ),
        'krungthai_installment_term' => array(
          'title' => __( 'KRUNG THAI', 'gbprimepay-payment-gateways-installment' ),
          'type' => 'text',
          'description' => __( 'Krung Thai Bank Public Company Limited. <br>Installment Terms. default: <b>3, 4, 5, 6, 7, 8, 9, 10</b>', 'gbprimepay-payment-gateways-installment' ),
          'default' => '3, 4, 5, 6, 7, 8, 9, 10'
        ),
        'thanachart_installment_term' => array(
          'title' => __( 'TTB', 'gbprimepay-payment-gateways-installment' ),
          'type' => 'text',
          'description' => __( 'TMBThanachart Bank Public Company Limited. <br>Installment Terms. default: <b>3, 4, 6, 10</b>', 'gbprimepay-payment-gateways-installment' ),
          'default' => '3, 4, 6, 10'
        ),
        'ayudhya_installment_term' => array(
          'title' => __( 'AYUDHYA', 'gbprimepay-payment-gateways-installment' ),
          'type' => 'text',
          'description' => __( 'Bank of Ayudhya Public Company Limited <br>Installment Terms. default: <b>3, 4, 6, 9, 10</b>', 'gbprimepay-payment-gateways-installment' ),
          'default' => '3, 4, 6, 9, 10'
        ),
        'firstchoice_installment_term' => array(
          'title' => __( 'FIRST CHOICE', 'gbprimepay-payment-gateways-installment' ),
          'type' => 'text',
          'description' => __( 'Krungsri First Choice. <br>Installment Terms. default: <b>3, 4, 6, 9, 10, 12, 18, 24</b>', 'gbprimepay-payment-gateways-installment' ),
          'default' => '3, 4, 6, 9, 10, 12, 18, 24'
        ),
        'scb_installment_term' => array(
          'title' => __( 'SCB', 'gbprimepay-payment-gateways-installment' ),
          'type' => 'text',
          'description' => __( 'Siam Commercial Bank Public Company Limited. <br>Installment Terms. default: <b>3, 4, 6, 10</b>', 'gbprimepay-payment-gateways-installment' ),
          'default' => '3, 4, 6, 10'
        ),
        'bbl_installment_term' => array(
          'title' => __( 'BBL', 'gbprimepay-payment-gateways-installment' ),
          'type' => 'text',
          'description' => __( 'Bangkok Bank Public Company Limited. <br>Installment Terms. default: <b>3, 4, 6, 8, 9, 10</b>', 'gbprimepay-payment-gateways-installment' ),
          'default' => '3, 4, 6, 8, 9, 10'
        ),
    )
);
