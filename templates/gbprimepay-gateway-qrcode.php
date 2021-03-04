<?php
include_once(dirname(__FILE__) . '/../includes/class-as-gbprimepay-gateway-qrcode.php');

$gbprimepay_qrcode = new AS_Gateway_Gbprimepay_Qrcode();

// Get order_id from GET
$order_id = $_GET['order_id'];

get_header();

$gbprimepay_qrcode->request_payment($order_id);

get_footer();

?>
