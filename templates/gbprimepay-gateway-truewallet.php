<?php
include_once(dirname(__FILE__) . '/../includes/class-as-gbprimepay-gateway-truewallet.php');
$gbprimepay_truewallet = new AS_Gateway_Gbprimepay_Truewallet();
// Get order_id from GET
$order_id = $_GET['order_id'];
get_header();
$gbprimepay_truewallet->request_payment($order_id);
get_footer();
?>
