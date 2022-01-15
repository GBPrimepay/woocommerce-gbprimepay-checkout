<?php
include_once(dirname(__FILE__) . '/../includes/class-as-gbprimepay-gateway-qrwechat.php');
$gbprimepay_qrwechat = new AS_Gateway_Gbprimepay_Qrwechat();
// Get order_id from GET
$order_id = $_GET['order_id'];
get_header();
$gbprimepay_qrwechat->request_payment($order_id);
get_footer();
?>
