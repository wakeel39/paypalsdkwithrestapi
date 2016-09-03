<?php
//require __DIR__ . '/paypalsdk/sample/bootstrap.php';
$composerAutoload = __DIR__ . '/paypalsdk/vendor/autoload.php';


if (!file_exists($composerAutoload)) {
    echo "The 'vendor' folder is missing. You must run 'composer update' to resolve application dependencies.\nPlease see the README for more information.\n";
    exit(1);
}
require $composerAutoload;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;

class PayPalSdk
{
    private $clientId = 'ARAYg3_1vmJLE_gD5yWxWY_XAnMazFWqKFF1cTZxIWZKW1xD2B7tBY-gDs36XYunNmL56Ad-AqNJRwne';
    private $clientSecret = 'EJVsWQEjEkJNPYB3d9h-3jig2VxrxbhpMptm2tYyoxY4fT05YjcP7mJQ3MAUCssSHs9--rYpEy1FQvG7';
    private $apiContext;

    function __construct()
    {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->clientId,
                $this->clientSecret
            )
        );

        // Comment this line out and uncomment the PP_CONFIG_PATH
        // 'define' block if you want to use static file
        // based configuration

        $this->apiContext->setConfig(
            array(
                'mode' => 'sandbox',
                'log.LogEnabled' => true,
                'log.FileName' => '../PayPal.log',
                'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => true,
                // 'http.CURLOPT_CONNECTTIMEOUT' => 30
                // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
                //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
            )
        );
    }

    //store card on server
    public function StoreCreditCardPaypal($data)
    {

        $card = new CreditCard();
        $card->setType($data["cardType"])
            ->setNumber($data["CardNo"])
            ->setExpireMonth($data["CardExpireMonth"])
            ->setExpireYear($data["CardExpireYear"])
            ->setCvv2($data["CardCvv"])
            ->setFirstName($data["FirstName"])
            ->setLastName($data["LastName"]);

        // ### Additional Information
        // Now you can also store the information that could help you connect
        // your users with the stored credit cards.
        // All these three fields could be used for storing any information that could help merchant to point the card.
        // However, Ideally, MerchantId could be used to categorize stores, apps, websites, etc.
        // ExternalCardId could be used for uniquely identifying the card per MerchantId. So, combination of "MerchantId" and "ExternalCardId" should be unique.
        // ExternalCustomerId could be userId, user email, etc to group multiple cards per user.
        $card->setMerchantId($data["UserId"]);
        $card->setExternalCardId($data["CardExternalId"]);
        $card->setExternalCustomerId($data["UserEmail"]);

        try {
            $c = $card->create($this->apiContext);
            return  $this->getResponse(1,$c);
        } catch (Exception $ex) {
            return  $this->getResponse(0,$ex);
        }
    }

    //create payment
    public function CreatePayment($data)
    {

        $creditCardToken = new CreditCardToken();
        $creditCardToken->setCreditCardId($data["cardId"]);

        // ### FundingInstrument
        // A resource representing a Payer's funding instrument.
        // For stored credit card payments, set the CreditCardToken
        // field on this object.
        $fi = new FundingInstrument();
        $fi->setCreditCardToken($creditCardToken);

        // ### Payer
        // A resource representing a Payer that funds a payment
        // For stored credit card payments, set payment method
        // to 'credit_card'.
        $payer = new Payer();
        $payer->setPaymentMethod("credit_card")
            ->setFundingInstruments(array($fi));

        // ### Itemized information
        // (Optional) Lets you specify item wise
        // information
        $item1 = new Item();
        $item1->setName($data["item_name"])
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setPrice($data["price"]);


        $itemList = new ItemList();
        $itemList->setItems(array($item1));


        // ### Amount
        // Lets you specify a payment amount.
        // You can also specify additional details
        // such as shipping, tax.
        $amount = new Amount();
        $amount->setCurrency("USD")
            ->setTotal($data["price"]);

        // ### Transaction
        // A transaction defines the contract of a
        // payment - what is the payment for and who
        // is fulfilling it.
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription($data["descp"])
            ->setInvoiceNumber($data["InvoiceNo"]);

        // ### Payment
        // A Payment Resource; create one using
        // the above types and intent set to 'sale'
        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setTransactions(array($transaction));


        try {
            $s = $payment->create($this->apiContext);
            return  $this->getResponse(1,$s);
        } catch (Exception $ex) {
            return  $this->getResponse(0,$ex);
        }
    }

    //get all cards aginst user
    public function getAllCards($userid)
    {
        try {
            $params = array(
                "sort_by" => "create_time",
                "sort_order" => "desc",
                "merchant_id" => $userid  // Filtering by MerchantId set during CreateCreditCard.
            );
            $cards = CreditCard::all($params, $this->apiContext);
            return  $this->getResponse(1,$cards);
        } catch (Exception $ex) {
            return  $this->getResponse(0,$ex);
        }
    }

    //get single card info
    public function getSingleCardInfo($cardid)
    {
        try {
            $card = CreditCard::get($cardid, $this->apiContext);
            return  $this->getResponse(1,$card);
        } catch (Exception $ex) {
            return  $this->getResponse(0,$ex);
        }
    }

    public function DeleteCard($cardid)
    {
        $card = $this->getSingleCardInfo($cardid);

        if($card["success"]==1) {
            $card = $card["data"];
            try {
                $c = $card->delete($this->apiContext);
                return $this->getResponse(1, $c);

            } catch (Exception $ex) {
                return $this->getResponse(0, $ex);
            }
        }else{
            return $this->getResponse(0, $card["data"]);
        }

    }
    //response handling
    function getResponse($success,$data){
        $res = array();
        $res['success'] =$success;
        $res['data'] =$data;
        if($success==0) {
            $d = null;
            if ($data instanceof \PayPal\Exception\PayPalConnectionException) {
                $d = json_decode($data->getData());
            }
            $msg =$d->message;
            if($d == 'VALIDATION_ERROR') { $msg = $d->details[0]->issue; }
            $res['data'] =$msg;
        }

        return $res;
    }
}

$p = new PayPalSdk();
/*$data=array();
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
print_r($res->type);*/
//payment
/*$data = array();
$data["cardId"]="CARD-19N71561KU705062YK7D4TYY";
$data["item_name"]="passenger payment";
$data["price"]="100";
$data["descp"]="this is test payment";
$data["InvoiceNo"]="10092";
$exception = $p->CreatePayment($data);

print_r($exception);*/

//get all cards of speific users only
$userid = "MyStore1";
$res = $p->getAllCards($userid);
print_r($res);

/*$card_id = "CARD-0D288407RF303720BK7D4UMI";
$res = $p->DeleteCard($card_id);
print_r($res);*/


?>