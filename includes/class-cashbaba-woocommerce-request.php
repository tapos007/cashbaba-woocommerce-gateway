<?php

if (!defined('ABSPATH')) {
    exit;
}

class Cashbaba_Wocommerce_Api_Request
{

    protected $config;
    protected $gateway;

    /**
     * Cashbaba_Wocommerce_Api_Request constructor.
     * @param $config
     */
    public function __construct($config,$gateway)
    {
        $this->config = $config;
        $this->gateway = $gateway;
    }

    public function get_token()
    {
        if ($token = get_transient('cashbaba_token')) {
            return $token;
        }

        $header = array(
            'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
        );

        $requestBody = array(
            'grant_type' => "client_credentials",
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret']
        );

        $args = array(
            'method' => 'POST',
            'headers' => $header,
            'body' => $requestBody,
        );

        $tokenUrl = $this->config['gatewayUrl'] . "/connect/token";

        $response = wp_remote_post($tokenUrl, $args);
        $responseCode = wp_remote_retrieve_response_code( $response );
        $body     = wp_remote_retrieve_body( $response );

        if($responseCode !=200){
            throw new Exception($body);
        }else{
            $result = json_decode($body, true);
            set_transient('cashbaba_token', $result['access_token'], $result['expires_in']);
            return $result['access_token'];
        }
    }

    public function create_checkout($order_id)
    {


        $token = $this->get_token();

        global $woocommerce;
        $order = new WC_Order($order_id);
        $header = array(
            'Authorization' => "Bearer " . $token,
            'Content-Type' => 'application/json',
        );

        $requestBody = array(
            'mid' => $this->config['merchant_id'],
            'customerid' => $this->config['customer_id'],
            'currency' => 'bdt',
            'orderamount' => $order->get_total(),
            'intent' => 'sale',
            'orderid' => uniqid()."-cb-".$order_id,
            'successcallbackurl' => $this->config['return_url'],
            'failurecallbackurl' => $this->config['return_url'],
            'cancelcallbackurl' => $this->config['return_url'],
            'RequestDateTime' => date("Y-m-d h:i:s")
        );



        $args = array(
            'method' => 'POST',
            'headers' => $header,
            'body' => json_encode($requestBody),
        );

        $tokenUrl = $this->config['gatewayUrl'] . "/ecommerce/checkout/create";

        $response = wp_remote_post($tokenUrl, $args);
        $responseCode = wp_remote_retrieve_response_code( $response );
        $body     = wp_remote_retrieve_body( $response );


        if($responseCode !=200){
            throw new Exception($body);
        }else{
            return json_decode($body, true);
        }

    }

    public function cbInquiry($paymentId,$referenceId,$orderId){

        $token = $this->get_token();


        $header = array(
            'Authorization' => "Bearer " . $token,
            'Content-Type' => 'application/json',
        );

        $requestBody = array(
            'paymentId' => $paymentId,
            'referenceId' => $referenceId,
            'orderId' => $orderId,
        );



        $args = array(
            'method' => 'POST',
            'headers' => $header,
            'body' => json_encode($requestBody),
        );

        $tokenUrl = $this->config['gatewayUrl'] . "/ecommerce/checkout/CheckStatus";

        $response = wp_remote_post($tokenUrl, $args);
        $responseCode = wp_remote_retrieve_response_code( $response );
        $body     = wp_remote_retrieve_body( $response );


        if($responseCode !=200){
            throw new Exception($body);
        }else{
            $this->gateway::log(json_encode($body));
            return json_decode($body, true);
        }
    }


}