<?php
include_once(dirname(__FILE__) . '/../includes/class-as-gbprimepay-gateway-qrcredit.php');
$gbprimepay_qrcredit = new AS_Gateway_Gbprimepay_Qrcredit();
// Get order_id from GET
$order_id = $_GET['order_id'];
get_header();
$gbprimepay_qrcredit->request_payment($order_id);
get_footer();
?>
