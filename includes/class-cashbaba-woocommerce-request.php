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

        if (!is_wp_error($response)) {

            $result = json_decode($response['body'], true);
            set_transient('cashbaba_token', $result['access_token'], $result['expires_in']);
        } else {
            wc_add_notice(json_encode($response), 'error');
            return;
        }
        return get_transient('cashbaba_token');
    }

    public function create_checkout($order_id)
    {


        $token = $this->get_token();

        global $woocommerce;
        $order = new WC_Order($order_id);



        $header = array(
            'Authorization' => "Bearer " . "CfDJ8F1jnbBPjxhPqZbqHemqexfDr5kgwvTKBmk9pjQK3cXxqBDmwfTCDHf0tijQVcfhfXRBbXOlpq2puOLnOtGbWUrNQnsBEptqBF26EOyn_Mr76syhJ6cIVcEPSE7x90dd4jPZDry5plls5n4r-bUvkmoG86_2U3LhsfXFWtnrL8h6CENrZTTVlwBMR7DbbWW40KqsgJr4OyOtKXHHpstk5I0W6gwEsPP1vh-VG9bLDFlhxZZzCY7ezb1lfzkz_E5Vv8hwug-l_zuRiqHDqMcWry8QEfrtFZKvYS4jRrAUeZj3TuWuCXqhQnrhsnnVrr7DSP4LC-775rMVd-Zb3pqC9k9t_7IdBv3KKIZQFz2SqrtRLhH6ha4Eb-TjqbBUn_frvmUsRMoa7g4qFTHBASAfAfs6i90EnyMcF5QLpiOugWy4UW8vo44WOnWgxZGBBxNTTQ4tannWthnsGM3vQCgAO67faJ-rsp_tRh_pSXTdenbvSAKcR1w0QGdHaj7NMbnxp_4sAVCEj1vpHfCLHeu6WrQNkSnO7oDabdtzLAqnhggese_9I_K4v04hu3mdsBszlS9zXnkO5lDkaRUzb-qFaNo",
            'Content-Type' => 'application/json',
            'timeout'     => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
        );

        $requestBody = array(
            'mddid' => 'SSLWireless',
            'customerid' => '1162137',
            'currency' => 'bdt',
            'orderamount' => '15.00',
            'intent' => 'sale',
            'orderid' => '4234534535ghghg',
            'successcallbackurl' => 'http://google.com',
            'failurecallbackurl' => 'http://google.com',
            'cancelcallbackurl' => 'http://google.com',
            'RequestDateTime' => '2021-06-30 08:19:06'
        );



        $args = array(
            'method' => 'POST',
            'headers' => $header,
            'body' => $requestBody,
        );


        $tokenUrl = $this->config['gatewayUrl'] . "/ecommerce/checkout/create";




        $response = wp_remote_post($tokenUrl, $args);
        $body     = wp_remote_retrieve_body( $response );
        $this->gateway::log(json_encode($response));
//        if (!is_wp_error($response)) {
//          //  $result = json_decode($response['body'], true);
//           // $this->gateway::log(json_encode($result));
//        }else{
//
//        }

    }


}