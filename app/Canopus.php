<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use DateTime;
use DateInterval;
use Crypt_RSA;
use Illuminate\Support\Facades\Session;
use Mock\CanopusMock;

class Canopus {

    public static function generateToken() {
        $canopusUrl = env('CANOPUS_BASE_URL') . '/api/v1/merchants/' . env('CANOPUS_MERCHANT_ID') . '/token';
        $request = array();

        $fields = array(
            "secret" => env('CANOPUS_SECRET')
        );

        $headers = array(
            'Content-Type: application/json'
        );

        array_push($request, $headers, $fields, $canopusUrl);

        $response = self::callAPI($request, 'token');
        if (!$response['status']) {
            return $response;
        }
        $responseCode = $response['bodyResponse'] -> response -> result -> code;
        if ($responseCode != "00000000"){
            $response['status'] = false;
            $response['message'] = $response['bodyResponse'] -> response -> result -> message;
            unset($response['bodyResponse']);
            return $response;
        }
        $token = $response['bodyResponse'] -> response -> data -> token;
        $expiredAt = $response['bodyResponse'] -> response -> data -> expiredAt;

        $diff = self::getTimeDifference($expiredAt);

        Cache::put('token', $token, $diff);
        return $token;
    }

    public static function createCart($data) {
        $canopusUrl = env('CANOPUS_BASE_URL') . '/api/v1/merchants/' . env('CANOPUS_MERCHANT_ID') . '/snap/cart';
        $request = array();

        $timestamp = new DateTime();
        $timestamp->add(new DateInterval('PT' . env('CANOPUS_CART_EXPIRY_HOUR') .'H'));
        $cartExpiry = $timestamp->format('c');
        $cartDetails = array("cartDetails" => array(
            "id" => $data['orderId'],
            "amount" => $data['amount'],
            "title" => 'Smart Presence Subscription Payment',
            "currency" => 'IDR',
            "expiredAt" => $cartExpiry
        )) ;

        $itemDetails = array("itemDetails" => array());
        $item = array(
            "name" => 'Smart Presence ' . $data['periode'] . ' Months Subscription',
            "price" => $data['amount'],
            "quantity" => 1,
            "SKU" => 'SP' . $data['periode']
        );
        array_push($itemDetails['itemDetails'], $item);

        $customerDetails = array("customerDetails"=>$data['customerDetails']);

//        $host = $_SERVER['HTTP_HOST'];
        $host = "example.com";
        $url = array("url" =>array(
            "returnURL" => 'https://' . $host . '/terimakasih',
            "cancelURL" => 'https://' . $host . '/pembayaran',
            "notificationURL" => 'https://' . $host . '/canopus/notifikasi'
        )); 
        
        $fields = array_merge($cartDetails, $itemDetails, $customerDetails, $url);

        $headers = array(
            'Content-Type: application/json'
        );

        if (env('CANOPUS_MERCHANT_ID') == 'M-0001'){
            array_push($headers, 'x-mock-ip: 127.0.0.1');
        }

        array_push($request, $headers, $fields, $canopusUrl);

        $response = self::callAPI($request, 'cart');

        $log_canopus = array(
            "request" => $request,
            "response" => $response
        );

        if (!$response['status']) {

            Utils::create_log(Session::get('conf_webperusahaan'),'create-cart', json_encode($log_canopus));

            return $response;
        }

        Utils::create_log(Session::get('conf_webperusahaan'),'create-cart', json_encode($log_canopus));

        $responseCode = $response['bodyResponse'] -> response -> result -> code;
        if ($responseCode != "00000000"){
            $response['status'] = false;
            $response['message'] = $response['bodyResponse'] -> response -> result -> message;
            unset($response['bodyResponse']);
            return $response;
        }
        $response['checkoutUrl'] = $response['bodyResponse'] -> response -> data -> checkoutURL;
        unset($response['bodyResponse']);

        return $response;
    }

    public static function generateSignature($requestBody) {
        $path = __DIR__ . '/../public/lib/keys/';
        $openssl_private_key = openssl_pkey_get_private('file://' . $path . env('CANOPUS_MERCHANT_ID') .'.key');
        if ($openssl_private_key == NULL || $openssl_private_key == false) {
            return false;
        };
        Log::info('Generate Signature Request Body: ', [json_encode($requestBody)]);
        $open_sign = openssl_sign(json_encode($requestBody, JSON_UNESCAPED_SLASHES), $signature, $openssl_private_key, OPENSSL_ALGO_SHA256);
        if ($open_sign == false) {
            return false;
        };
        $signature_result = base64_encode($signature);
        openssl_free_key($openssl_private_key);
        return $signature_result;
    }

    public static function validateSignature($body) {
        $path = __DIR__ . '/../public/lib/keys/';
        $openssl_public_key = file_get_contents('file://' . $path . 'canopus.pem');
        $rsa = new Crypt_RSA;
        $rsa -> loadKey($openssl_public_key);
        $signature = base64_decode($body -> signature);
        $result = openssl_verify(json_encode($body -> request, JSON_UNESCAPED_SLASHES), $signature, $rsa, 'sha256WithRSAEncryption');
        return $result;
    }

    public static function callAPI($request, $command) {
        if (Session::has('isUnitTest')) {
            $command = Session::get('isUnitTest');
            $response = CanopusMock::canopusMock($command);
            return $response;
        }
        $response = array();
        $headers = $request[0];
        $fields = $request[1];
        $url = $request[2];

        $signature = self::generateSignature($fields);
        if (!$signature){
            $error = 'Generate signature failed';
            $response['status'] = false;
            $response['message'] = $error;
            Log::error($error);
            return $response;
        }
        array_push($headers, 'x-signature: ' . $signature);
        if ($command != 'token') {
            $token = Cache::get('token');
            if ($token == NULL) {
                $token = self::generateToken();
                if (!$token) {
                    $error = 'Generate token failed';
                    $response['status'] = false;
                    $response['message'] = $error;
                    Log::error($error);
                    return $response;
                }
            }
            array_push($headers, 'authorization: Bearer ' . $token); 
        };

        Log::info('Request Parameter: ', ['headers' => $headers, 'bodyRequest' => $fields, 'url' => $url]);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, env('CANOPUS_TIMEOUT'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        if ($result === FALSE) {
            $error = 'Curl failed';
            $response['status'] = false;
            $response['message'] = $error;
            Log::error($error);
            return $response;
        }
        $bodyResponse = json_decode($result);
        // Check HTTP status code
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:
                    break;
                case 403:
                    Log::info('Token Expired');
                    $token = self::generateToken();
                    if (!$token) {
                        $error = 'Generate token failed';
                        $response['status'] = false;
                        $response['message'] = $error;
                        Log::error($error);
                        return $response;
                    }
                    $response = self::callAPI($request, $command);
                    return $response;
                default:
                    $error = 'Unexpected HTTP code: ' . $http_code;
                    $response['status'] = false;
                    $response['message'] = $error;
                    Log::error($error, [$bodyResponse]);
                    return $response;
            }
        }
        curl_close($ch);
        $response['status'] = true;
        $response['message'] = 'Curl Success';
        $response['bodyResponse'] = $bodyResponse;
        if ($command != 'token') {
            Log::info('Success Response: ', [$bodyResponse]);
        }
        return $response;
    }

    public static function getTimeDifference($expireTime) {
        $now = new DateTime;
        $expiredAt = new DateTime($expireTime);
        $diff = $expiredAt->getTimestamp() - $now->getTimestamp();
        $diff = floor($diff/60);
        return $diff;
    }


}