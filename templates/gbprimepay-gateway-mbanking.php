<?php
include_once(dirname(__FILE__) . '/../includes/class-as-gbprimepay-gateway-mbanking.php');
$gbprimepay_mbanking = new AS_Gateway_Gbprimepay_Mbanking();
// Get order_id from GET
$order_id = $_GET['order_id'];
get_header();
$gbprimepay_mbanking->request_payment($order_id);
get_footer();
?>
