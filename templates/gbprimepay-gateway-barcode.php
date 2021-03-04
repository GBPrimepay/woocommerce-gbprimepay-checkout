<?php
include_once(dirname(__FILE__) . '/../includes/class-as-gbprimepay-gateway-barcode.php');

$gbprimepay_barcode = new AS_Gateway_Gbprimepay_Barcode;

// Get order_id from GET
$order_id = $_GET['order_id'];

get_header();

$gbprimepay_barcode->request_payment($order_id);

get_footer();

?>
