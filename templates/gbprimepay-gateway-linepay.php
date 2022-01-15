<?php
include_once(dirname(__FILE__) . '/../includes/class-as-gbprimepay-gateway-linepay.php');
$gbprimepay_linepay = new AS_Gateway_Gbprimepay_Linepay();
// Get order_id from GET
$order_id = $_GET['order_id'];
get_header();
$gbprimepay_linepay->request_payment($order_id);
get_footer();
?>
