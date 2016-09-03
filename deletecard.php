<?php
include_once "Paypal_PyamentCalss..php";
$p = new PayPalSdk();
$card_id = "CARD-0D288407RF303720BK7D4UMI";
$res = $p->DeleteCard($card_id);
print_r($res);


?>