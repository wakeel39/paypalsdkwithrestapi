<?php
include_once "Paypal_PyamentCalss..php";
$p = new PayPalSdk();
$data = array();
$data["cardId"]="CARD-19N71561KU705062YK7D4TYY";
$data["item_name"]="passenger payment";
$data["price"]="100";
$data["descp"]="this is test payment";
$data["InvoiceNo"]="10092";
$exception = $p->CreatePayment($data);

print_r($exception);

?>