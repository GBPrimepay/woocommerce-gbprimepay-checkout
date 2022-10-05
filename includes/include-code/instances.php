<?php
function gbp_instances( $instances ) {
    $inc = array(
    '3D_SECURE_PAYMENT' => TRUE,    // Enabling 3-D Secure payment(TRUE/FALSE).
                                    // Please be informed that you must contact GB Prime Pay support team before enable or disable this option.
                                    // (3-D Secure only available in Production Mode).  

    'PLATFORM' => 'woocommerce',
    'MODE' => 'payment',
    'STATUS' => 'draft',
    
    'URL_CHECKOUT_TEST' => 'https://checkout.globalprimepay.com',
    'URL_CHECKOUT_LIVE' => 'https://checkout.gbprimepay.com',

    'URL_MERCHANT_TEST' => 'https://api.globalprimepay.com/getmerchantinfo',
    'URL_MERCHANT_LIVE' => 'https://api.gbprimepay.com/getmerchantinfo',

    'URL_CHECKPUBLICKEY_TEST' => 'https://api.globalprimepay.com/checkPublicKey',
    'URL_CHECKPUBLICKEY_LIVE' => 'https://api.gbprimepay.com/checkPublicKey',

    'URL_CHECKPRIVATEKEY_TEST' => 'https://api.globalprimepay.com/checkPrivateKey',
    'URL_CHECKPRIVATEKEY_LIVE' => 'https://api.gbprimepay.com/checkPrivateKey',
    
    'URL_CHECKCUSTOMERKEY_TEST' => 'https://api.globalprimepay.com/checkCustomerKey',
    'URL_CHECKCUSTOMERKEY_LIVE' => 'https://api.gbprimepay.com/checkCustomerKey',
);
$inc_code = isset( $inc[$instances] ) ? $inc[$instances] : $instances;
return $inc_code;
}
