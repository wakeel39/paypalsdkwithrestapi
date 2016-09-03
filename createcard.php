<?php
include_once "Paypal_PyamentCalss..php";
$p = new PayPalSdk();
$data=array();
$data["cardType"]="visa";
$data["CardNo"]="4917912523797702";
$data["CardExpireMonth"]="11";
$data["CardExpireYear"]="2019";
$data["CardCvv"]="012";
$data["FirstName"]="Joe";
$data["LastName"]="Shopper";
$data["UserId"]="MyStore1";
$data["CardExternalId"]="CardNumber123" . uniqid();
$data["UserEmail"]="123123-myUser1@something.com";
$res = $p->StoreCreditCardPaypal($data);
print_r($res->type);


?>