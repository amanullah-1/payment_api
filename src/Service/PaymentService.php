<?php 
// src/Service/PaymentService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaymentService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function processPayment($system, $amount, $currency, $cardNumber, $expYear, $expMonth, $cvv, $paymentBrand, $paymentType)
    {
        if ($system === 'shift4') {
            return $this->processShift4($amount, $currency, $cardNumber, $expYear, $expMonth, $cvv);
        } elseif ($system === 'aci') {
            return $this->processACI($amount, $currency, $cardNumber, $expYear, $expMonth, $cvv, $paymentBrand, $paymentType);
        }

        throw new \InvalidArgumentException('Invalid system');
    }

    private function processShift4($amount, $currency, $cardNumber, $expYear, $expMonth, $cvv)
    {
        //Customer ID
        $customer_id = "cust_YuU91wcSAJmbyTYMykLSWnsn"; //'env(SHIFT_CUSTOMER_ID)';
        $card_data = array(
        'number'=>$cardNumber, 
        'exp_year' => $expYear, 
        'exp_month' => $expMonth,
        'cvv' => $cvv );
        //Token
        $token = $this->createToken($card_data);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.shift4.com/charges');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "amount=$amount&currency=$currency&customerId=$customer_id&card=$token&description=Example charge");
        curl_setopt($ch, CURLOPT_USERPWD, 'sk_test_ctR8b5CxlJvPuG6XtspLNIqk' . ':' . '');

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $responseData;

    }

    private function processACI($amount, $currency, $cardNumber, $expYear, $expMonth, $cvv, $paymentBrand, $paymentType)
    {
        // Implement the ACI API call here
        $url = "https://eu-test.oppwa.com/v1/payments";
        $data = "entityId=8a8294174b7ecb28014b9699220015ca" .
                "&amount=$amount" .
                "&currency=$currency" .
                "&paymentBrand=$paymentBrand" .
                "&paymentType=$paymentType" .
                "&card.number=$cardNumber" .
                "&card.holder=Jane Jones" .
                "&card.expiryMonth=$expMonth" .
                "&card.expiryYear=$expYear" .
                "&card.cvv=$cvv";

    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                       'Authorization:Bearer OGE4Mjk0MTc0YjdlY2IyODAxNGI5Njk5MjIwMDE1Y2N8c3k2S0pzVDg='));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if(curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);

        return $responseData;
    }

    private function unifyResponse($data)
    {
        return [
            'transaction_id' => $data['transaction_id'] ?? 'N/A',
            'created_at' => date('Y-m-d H:i:s'),
            'amount' => $data['amount'] ?? 'N/A',
            'currency' => $data['currency'] ?? 'N/A',
            'card_bin' => substr($data['card']['number'], 0, 6),
        ];
    }

    private function createToken($card_data){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.shift4.com/tokens');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "number=".$card_data['number']."&expMonth=".$card_data['exp_month']."&expYear=".$card_data['exp_year']."&cvc=".$card_data['cvv']."&cardholderName=John Doe");
        curl_setopt($ch, CURLOPT_USERPWD, 'sk_test_ctR8b5CxlJvPuG6XtspLNIqk' . ':' . '');
        
        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
    
        $tokenData =  json_decode($result, TRUE);

        $token_id = $tokenData['id'];
        return $token_id;
    }
}
