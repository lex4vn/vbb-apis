<?php
/**
 * Description of itunesReceiptValidator
 *
 * @author LE DINH HUNG
 */
class itunesReceiptValidator {
    public $receipt;
    public $endpoint;

    const SANDBOX_URL    = 'https://sandbox.itunes.apple.com/verifyReceipt';
    const PRODUCTION_URL = 'https://buy.itunes.apple.com/verifyReceipt';

    function __construct($endpoint, $receipt = NULL) {
        $this->setEndPoint($endpoint);

        if ($receipt) {
            $this->setReceipt($receipt);
        }
    }

    function getReceipt() {
        return $this->receipt;
    }

    function setReceipt($receipt) {
            $this->receipt = $receipt;
    }

    function getEndpoint() {
        return $this->endpoint;
    }

    function setEndPoint($endpoint) {
        $this->endpoint = $endpoint;
    }

    function validateReceipt() {
        $decoded_response = $this->makeRequest();
        $decoded_response = $this->decodeResponse($decoded_response);
//print_r($decoded_response);die;
        if (!isset($decoded_response->status) || $decoded_response->status != 0) {
            throw new Exception('Invalid receipt. Status code: ' . (!empty($decoded_response->status) ? $decoded_response->status : 'N/A'));
        }

        if (!is_object($decoded_response)) {
            throw new Exception('Invalid response data');
        }
//        echo $decoded_response->receipt;die;
        return $decoded_response;
    }

    private function encodeRequest() {
//        echo $this->getReceipt();die;
        return json_encode(array('receipt-data' => $this->getReceipt()));
    }

    private function decodeResponse($response) {
        return json_decode($response);
    }

    private function makeRequest() {
        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->encodeRequest());

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $errmsg   = curl_error($ch);
        curl_close($ch);

        if ($errno != 0) {
            throw new Exception($errmsg, $errno);
        }

        return $response;
    }
}
