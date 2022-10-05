<?php

if(isset($_GET["page"]) && !empty($_GET["page"])){
  $res =  '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' .
          '<html><head>' .
          '<script type="text/javascript"> function OnLoadEvent() { setTimeout(function(){genChecksum();}, 1000);setTimeout(function(){document.form.submit();}, 1000); }</script>' .
          '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' .
          '<title>GBPrimePay Payments</title></head>' .
          '<body OnLoad="OnLoadEvent();">' .
          'GBPrimePay Checkout, Invoking Secure Payment, Please Wait ..' .
          '<form name="form" action="'. rawurldecode($_GET['page']).'" method="post"  target="_top">' .
          '<input type="hidden" name="publicKey" value="'. rawurldecode($_GET['publicKey']).'">' .
          '<input type="hidden" name="referenceNo" value="'. rawurldecode($_GET['referenceNo']).'">' .
          '<input type="hidden" name="responseUrl" value="'. rawurldecode($_GET['responseUrl']).'">' .
          '<input type="hidden" name="backgroundUrl" value="'. rawurldecode($_GET['backgroundUrl']).'">' .
          '<input type="hidden" name="detail" value="'. rawurldecode($_GET['detail']).'">' .
          '<input type="hidden" name="customerName" value="'. rawurldecode($_GET['customerName']).'">' .
          '<input type="hidden" name="customerEmail" value="'. rawurldecode($_GET['customerEmail']).'">' .
          '<input type="hidden" name="customerAddress" value="'. rawurldecode($_GET['customerAddress']).'">' .
          '<input type="hidden" name="customerTelephone" value="'. rawurldecode($_GET['customerTelephone']).'">' .
          '<input type="hidden" name="amount" value="'. rawurldecode($_GET['amount']).'">' .
          '<input type="hidden" name="bankCode" value="'. rawurldecode($_GET['bankCode']).'">' .
          '<input type="hidden" name="term" value="'. rawurldecode($_GET['term']).'">' .
          '<input type="hidden" name="merchantDefined1" value="'. rawurldecode($_GET['merchantDefined1']).'">' .
          '<input type="hidden" name="merchantDefined2" value="'. rawurldecode($_GET['merchantDefined2']).'">' .
          '<input type="hidden" name="merchantDefined3" value="'. rawurldecode($_GET['merchantDefined3']).'">' .
          '<input type="hidden" name="merchantDefined4" value="'. rawurldecode($_GET['merchantDefined4']).'">' .
          '<input type="hidden" name="merchantDefined5" value="'. rawurldecode($_GET['merchantDefined5']).'">' .
          '<input type="hidden" name="checksum" value="">' .
          '<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.min.js"></script>' .
          '<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/hmac-sha256.min.js"></script>' .
          '<script>' .
          'function genChecksum(){' .
          'var hash = CryptoJS.HmacSHA256(' .
          'document.getElementsByName("amount")[0].value +' .
          'document.getElementsByName("referenceNo")[0].value +' .
          'document.getElementsByName("responseUrl")[0].value +' .
          'document.getElementsByName("backgroundUrl")[0].value +' .
          'document.getElementsByName("bankCode")[0].value +' .
          'document.getElementsByName("term")[0].value ,' .
          '"'. rawurldecode($_GET['secret_key']).'");' .
          'document.getElementsByName("checksum")[0].value = hash;' .
          '}' .
          '</script>' .
          '<noscript>' .
          '<center><p>Please click button below to Authenticate your card</p><input type="submit" value="Go"/></p></center>' .
          '</noscript>' .
          '</form></body></html>';

  echo $res;
}

?>
