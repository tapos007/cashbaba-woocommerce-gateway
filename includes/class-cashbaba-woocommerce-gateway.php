<?php

class Cashbaba_Wocommerce_Gateway  extends WC_Payment_Gateway
{

    private $allowedCurrencies = array(
        'SEK', 'EUR', 'NOK', 'USD', 'CLP'
    );
    private const SUCCESS_CALLBACK_URL = "cashbaba/sucess";
    private const FAILURE_CALLBACK_URL = "cashbaba/failure";
    private const TEST_URL = "http://localhost:5000/api/v1/";
    private const  PRODUCTION_URL = "https://api.cashbaba.com.bd/api/v1/";
    private $backendApplicationUrl;
    private const SUCCESS_REDIRECT_URL = "/checkout/order-received/";
    private const FAILURE_REDIRECT_URL = "/checkout/order-received/";
    private $table = 'cashbaba_transactions';
    public function __construct()
    {
        $this->id = 'cashbaba'; // payment gateway plugin ID
        $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
        $this->has_fields = true; // in case you need a custom credit card form
        $this->method_title = 'CashBaba Payment Gateway';
        $this->method_description = 'Description of CashBaba payment gateway'; // will be displayed on the options page
        $title                    = $this->get_option('title');
        $this->title              = empty($title) ? 'CashBaba' : $title;
        $this->description        = $this->get_option('description');

        // gateways can support subscriptions, refunds, saved payment methods,
        // but in this tutorial we begin with simple payments
        $this->supports = array(
            'products'
        );

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();


        $this->siteUrl = get_site_url();

        if ($this->get_option('test_mode')) {
            $this->backendApplicationUrl = self::TEST_URL;
        } else {
            $this->backendApplicationUrl = self::PRODUCTION_URL;
        }


        // This action hook saves the settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_' . self::SUCCESS_CALLBACK_URL, array($this, 'payment_success'));
        add_action('woocommerce_api_' . self::FAILURE_CALLBACK_URL, array($this, 'payment_failure'));
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
    }
    public function init_form_fields()
    {

        $this->form_fields = array(
            'enabled'     => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable CashBaba Gateway',
                'default' => 'yes',
                'required'    => TRUE,
            ),
            'test_mode'   => array(
                'title'   => 'Test Mode',
                'required'    => TRUE,
                'type'    => 'select',
                'options' => ["on" => "ON", "off" => "OFF"],
                'default' => 'on',
            ),
            'title'       => array(
                'title'   => 'Title',
                'required'    => true,
                'type'    => 'text',
                'default' => 'cashbaba Payment',
            ),
            'client_id'    => array(
                'title' => 'Client Id',
                'required'    => true,
                'type'  => 'text',
            ),
            'client_secret'    => array(
                'title' => 'Client Secret',
                'required'    => true,
                'type'  => 'text',
            ),
            'success_call_back'    => array(
                'title' => 'Success CallBack',
                'required'    => true,
                'type'  => 'text',
                'default' => get_site_url() . "/wc-api/" . self::SUCCESS_CALLBACK_URL,
            ),
            'error_call_back'    => array(
                'title' => 'Success CallBack',
                'required'    => true,
                'type'  => 'text',
                'default' => get_site_url() . "/wc-api/" . self::FAILURE_CALLBACK_URL,
            ),
            'description' => array(
                'title'       => 'Description',
                'type'        => 'textarea',
                'description' => 'Payment method description that the customer will see on your checkout.',
                'default'     => 'Pay via CashBaba',
                'desc_tip'    => true,
            ),
        );
    }


    public function payment_fields()
    {
    }
    public function payment_scripts()
    {
    }
    public function validate_fields()
    {
    }
    public function process_payment($order_id)
    {

        $token = $this->get_token();
        global $woocommerce;

        //To receive order id 
        $order = wc_get_order($order_id);

        //To receive order amount
        $amount = $order->get_total();

        $requestBody = array(
            'currency' => "BDT",
            'amount' => $amount,
            'intent' => 'sale',
            'merchantInvoiceNumber' => $order_id
        );

        wc_add_notice(json_encode($requestBody), 'error');


        $header = array(
            'Authorization' => "Bearer " . $token,
            'Content-Type' => 'application/json'
        );

        $args = array(
            'method' => 'POST',
            'headers' => $header,
            'body' => json_encode($requestBody),
        );

        $apiUrl = $this->backendApplicationUrl . "ecommerce/checkout/create";

        $response = wp_remote_post($apiUrl, $args);
        WC()->cart->empty_cart();
        if (!is_wp_error($response)) {
            $result = json_decode($response['body'], true);
            $insertData = [
                "order_number"       => $order_id,
                "payment_id"         => $result['paymentId'],
                "trx_id"             => $result['paymentId'],
                "transaction_status" => $result['transactionStatus'],
                "invoice_number"     => $order_id,
                "amount"             => $amount,
                "cashbaba_url"       => $result['cashBabaUrl'],
            ];
            $this->insertCashBabaPayment($insertData);
            $order->update_status('processing');
            return array(
                'result' => 'success',
                'redirect' => $result['cashBabaUrl']
            );
        } else {
            wc_add_notice(json_encode($response), 'error');
            return;
        }
    }
    public function payment_success()
    {
        
        global $wpdb;
        $variable = $_GET['paymentId'];

        $query = "SELECT * FROM {$wpdb->prefix}{$this->table} where payment_id='{$variable}'";

        $result = $wpdb->get_row($query);
        
        $order = wc_get_order($result->order_number);
        $order->update_status('completed');
        $order->payment_complete();
        $order->reduce_order_stock();
        
       
        wp_redirect( $this->get_return_url( $order ) );
        

        exit;
       
        
    }

    public function payment_failure()
    {

        global $wpdb;
        $variable = $_GET['paymentId'];

        $query = "SELECT * FROM {$wpdb->prefix}{$this->table} where payment_id='{$variable}'";
        $result = $wpdb->get_row($query);
        $order = wc_get_order($result->order_number);
        $order->update_status('failed');
        wp_redirect( $this->get_return_url( $order ) );
    
        exit;
        

    }

    private function get_token()
    {
        if ($token = get_transient('cashbaba_token')) {
            return $token;
        }

        $header = array(
            'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
        );

        $requestBody = array(
            'grant_type' => "client_credentials",
            'client_id' => $this->get_option('client_id'),
            'client_secret' => $this->get_option('client_secret')
        );
       

        $args = array(
            'method' => 'POST',
            'headers' => $header,
            'body' => $requestBody,
        );
        
        $tokenUrl = $this->backendApplicationUrl . "connect/token";
        

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

    private function insertCashBabaPayment($paymentInfo)
    {
        global $wpdb;

        $insert = $wpdb->insert($wpdb->prefix . $this->table, array(
            "order_number"       => sanitize_text_field($paymentInfo['order_number']),
            "payment_id"         => sanitize_text_field($paymentInfo['payment_id']),
            "trx_id"             => sanitize_text_field($paymentInfo['trx_id']),
            "transaction_status" => sanitize_text_field($paymentInfo['transaction_status']),
            "invoice_number"     => sanitize_text_field($paymentInfo['invoice_number']),
            "amount"             => sanitize_text_field($paymentInfo['amount']),
        ));

        return $insert;
    }
}
