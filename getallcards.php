<?php
include_once "Paypal_PyamentCalss..php";
$p = new PayPalSdk();
$userid = "MyStore1";
$res = $p->getAllCards($userid);
print_r($res);


?>