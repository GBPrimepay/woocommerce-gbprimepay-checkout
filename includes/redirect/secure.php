<?php

if(isset($_GET["page"]) && !empty($_GET["page"])){
  $res =  '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' .
          '<html><head>' .
          '<script type="text/javascript"> function OnLoadEvent() { document.form.submit(); }</script>' .
          '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' .
          '<title>GBPrimePay Payments</title></head>' .
          '<body OnLoad="OnLoadEvent();">' .
          'GBPrimePay, Invoking 3-D Secure Payment, Please Wait ..' .
          '<form name="form" action="'. rawurldecode($_GET['page']).'" method="post"  target="_top">' .
          '<input type="hidden" name="publicKey" value="'. rawurldecode($_GET['publicKey']).'">' .
          '<input type="hidden" name="gbpReferenceNo" value="'. rawurldecode($_GET['gbpReferenceNo']).'">' .
          '<noscript>' .
          '<center><p>Please click button below to Authenticate your card</p><input type="submit" value="Go"/></p></center>' .
          '</noscript>' .
          '</form></body></html>';

  echo $res;
}

?>
