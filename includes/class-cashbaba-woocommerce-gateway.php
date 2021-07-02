<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cashbaba_Wocommerce_Gateway extends WC_Payment_Gateway
{


    const GATEWAY_URL = 'https://dev.cash-baba.com:11443/api/v1';
    const GATEWAY_SANDBOX_URL = 'https://dev.cash-baba.com:11443/api/v1';
    const GATEWAY_ID = 'cashbaba';
    protected static $log_enabled = false;
    protected static $log = false;
    protected $current_currency;
    protected $supported_currencies;
    private $table = 'cashbaba_transactions';

    public function __construct()
    {
        $this->id = self::GATEWAY_ID; // payment gateway plugin ID
        $this->has_fields = false; // in case you need a custom credit card form
        $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
        $this->method_title = 'CashBaba Payment Gateway';
        $this->method_description = 'Description of CashBaba payment gateway'; // will be displayed on the options page
        $title = $this->get_option('title');
        $this->title = empty($title) ? 'CashBaba' : $title;
        $this->description = $this->get_option('description');
        $this->order_button_text = __('Pay with CashBaba', 'woo-alipay');
        $this->current_currency = get_option('woocommerce_currency');
        $this->supported_currencies = array('BDT');
        $this->form_submission_method = true;

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cashbaba-woocommerce-request.php';
        // gateways can support subscriptions, refunds, saved payment methods,
        // but in this tutorial we begin with simple payments
        $this->supports = array(
            'products'
        );

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        self::$log_enabled = ('yes' === $this->get_option('debug', 'no'));

        // This action hook saves the settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_' . $this->id, array($this, 'check_cashbaba_response'));
        add_action('woocommerce_receipt_cashbaba', array($this, 'receipt_page'));
        //  add_action( 'woocommerce_thankyou_cashbaba', array( $this, 'thankyou_page' ) );

        $this->validate_settings();

    }


    public function check_cashbaba_response()
    {


//        $response_data = $_POST;
//        if(empty($response_data)){
//            $error = __( 'Invalid CashBaba response: ', 'cashbaba-woocommerce' );
//            wc_add_notice( $error, 'error' );
//            wp_redirect(home_url('/'));
//        }

        $generate_order_id = $_POST['orderId'];
        $payment_id = $_POST['transactionToken'];
        $reference_id = $_POST['uniqueReferenceNumber'];

        if(empty($generate_order_id) || empty($payment_id) || empty($reference_id)){
            $error = __( 'Invalid CashBaba response: ', 'cashbaba-woocommerce' );
            wc_add_notice( $error, 'error' );
            wp_redirect(home_url('/'));
        }

        try {
            $apiRequest  =   new Cashbaba_Wocommerce_Api_Request($this->get_config(), $this);
            $cbInquiryResponse = $apiRequest->cbInquiry($payment_id,$reference_id,$generate_order_id);
            $dbData = $this->getTransactionCbAferCallBack($generate_order_id,$payment_id,$reference_id);
            $order  = wc_get_order( $dbData['original_order_id'] );

            if(!$order->needs_payment()){
                $error = __( 'CashBaba notified the payment was successful but the order was already paid for. Please double check that the payment was recorded properly.', 'cashbaba-woocommerce' );
                $order->add_order_note( $error);
                wc_add_notice( $error, 'error' );
                wp_redirect($order->get_checkout_order_received_url());
            }

            if($cbInquiryResponse['responseCode'] = 200 && $cbInquiryResponse['message'] = 'message'){
                $order->payment_complete( wc_clean( $cbInquiryResponse['transactionId'] ) );
                $order->add_order_note( __( 'Cashbaba payment completed', 'cashbaba-woocommerce' ) );
                WC()->cart->empty_cart();
                wp_redirect($order->get_checkout_order_received_url());

            }else{
                $order->add_order_note( __( 'Cashbaba closed the transaction and the order is no longer valid for payment.', 'cashbaba-woocommerce' ) );
                $error = __(  'Cashbaba closed the transaction and the order is no longer valid for payment.', 'cashbaba-woocommerce'  );
                wc_add_notice( $error, 'error' );
                wp_redirect($order->get_checkout_payment_url());
            }

        }catch (Exception $e){
            $error = __( $e->getMessage(), 'cashbaba-woocommerce' );
            wc_add_notice( $error, 'error' );
            wp_redirect(home_url('/'));
        }


        exit();
    }


    /**
     * method  for writing log
     */
    public static function log($message, $level = 'info', $force = false)
    {

        if (self::$log_enabled || $force) {

            if (empty(self::$log)) {
                self::$log = wc_get_logger();
            }

            self::$log->log($level, $message, array('source' => self::GATEWAY_ID));
        }
    }

    /**
     * method  for validation check
     */
    public function validate_settings()
    {
        $valid = true;

        if ($this->requires_exchange_rate()) {
            add_action('admin_notices', array($this, 'missing_exchange_rate_notice'), 10, 0);

            $valid = false;
        }

        return $valid;
    }

    /**
     * method  for exchange rate calculation
     */
    public function requires_exchange_rate()
    {

        return (!in_array($this->current_currency, $this->supported_currencies, true));
    }

    /**
     * method  for exchange rate notice
     */
    public function missing_exchange_rate_notice()
    {
        $message = __('CashBaba is enabled, but the store currency is not set to Bangladeshi BDT.', 'cashbaba-woocommerce');
        // translators: %1$s is the URL of the link and %2$s is the currency name
        $message .= __(' Please <a href="%1$s">set the %2$s against the Bangladeshi BDT</a>.', 'woo-alipay');

        $page = 'admin.php?page=wc-settings&tab=checkout&section=wc_cashbaba#woocommerce_cashbaba_exchange_rate';
        $url = admin_url($page);

        _e('<div class="error"><p>' . sprintf($message, $url, $this->current_currency . '</p></div>'),'cashbaba-woocommerce');

    }

    /**
     * method  for availability check
     */

    public function is_available()
    {
        $is_available = ('yes' === $this->enabled) ? true : false;

        if (!in_array(get_woocommerce_currency(), $this->supported_currencies, true)
        ) {
            $is_available = false;
        }

        return $is_available;
    }

    /**
     * process admin options
     */
    public function process_admin_options()
    {
        $saved = parent::process_admin_options();

        if ('yes' !== $this->get_option('debug', 'no')) {

            if (empty(self::$log)) {
                self::$log = wc_get_logger();
            }

            self::$log->clear(self::GATEWAY_ID);
        }


        return $saved;
    }


    public function init_form_fields()
    {
        $this->form_fields = require plugin_dir_path(dirname(__FILE__)) . 'admin/settings-cashbaba.php';
    }


    protected function get_config($order_id = 0)
    {

        $isTestEnvironment = ('yes' === $this->get_option('testmode', 'no'));
        $callback = add_query_arg('wc-api', $this->id, home_url('/'));
        $config = array(
            'client_id' => $isTestEnvironment ? $this->get_option('test_client_id') : $this->get_option('live_client_id'),
            'client_secret' => $isTestEnvironment ? $this->get_option('test_client_secret') : $this->get_option('live_client_secret'),
            'merchant_id' => $isTestEnvironment ? $this->get_option('test_merchant_id') : $this->get_option('live_merchant_id'),
            'customer_id' => $isTestEnvironment ? $this->get_option('test_customer_id') : $this->get_option('live_customer_id'),
            'return_url' => $callback,
            'gatewayUrl' => $isTestEnvironment ? self::GATEWAY_SANDBOX_URL : self::GATEWAY_URL,
            'company_logo' => $this->get_option('test_merchant_id'),
            'company_name' => $this->get_option('company_name'),


        );

        return $config;
    }

    /**
     * Output for the order received page.
     *
     * @access public
     * @return void
     */
    function receipt_page($order_id)
    {
        _e( '<p>' . __('Thank you for your order, please click the button below to pay with CashBaba.', 'cashbaba-woocommerce') . '</p>','cashbaba-woocommerce');
        _e($this->generate_cashbaba_form($order_id),'cashbaba-woocommerce');
    }


    function generate_cashbaba_form($order_id)
    {
        $dbData = $this->getTransactionCb($order_id);

        $alipay_args_array = array();
        $alipay_args_array[] = '<input type="hidden" name="' . esc_attr("orderId") . '" value="' . esc_attr($dbData['generate_order_id']) . '" />';
        $alipay_args_array[] = '<input type="hidden" name="' . esc_attr("referenceId") . '" value="' . esc_attr($dbData['reference_id']) . '" />';
        $alipay_args_array[] = '<input type="hidden" name="' . esc_attr("paymentId") . '" value="' . esc_attr($dbData['payment_id']) . '" />';


        wc_enqueue_js('
//            $.blockUI({
//                    message: "' . esc_js(__('Thank you for your order. We are now redirecting you to CashBaba to make payment.', 'alipay')) . '",
//                    baseZ: 99999,
//                    overlayCSS:
//                    {
//                        background: "#fff",
//                        opacity: 0.6
//                    },
//                    css: {
//                        padding:        "20px",
//                        zindex:         "9999999",
//                        textAlign:      "center",
//                        color:          "#555",
//                        border:         "3px solid #aaa",
//                        backgroundColor:"#fff",
//                        cursor:         "wait",
//                        lineHeight:     "24px",
//                    }
//                });
            jQuery("#submit_alipay_payment_form").click();
        ');

        return '<form id="alipaysubmit" name="alipaysubmit" action="' . $dbData['cashbaba_url'] . '" method="post" target="_top">' . implode('', $alipay_args_array) . '
                    <!-- Button Fallback -->
                    <div class="payment_buttons">
                        <input type="submit" class="button-alt" id="submit_alipay_payment_form" value="Pay via CashBaba" /> 
                    </div>
                    <script type="text/javascript">
                        jQuery(".payment_buttons").hide();
                    </script>
                </form>';
    }


    public function process_payment($order_id)
    {

        $order = new WC_Order($order_id);

        try {
            $api_request = new Cashbaba_Wocommerce_Api_Request($this->get_config($order_id), $this);
            $checkout_response = $api_request->create_checkout($order_id);
            $this->log($checkout_response);
            $this->insertCashBabaPayment($checkout_response,$order_id);

            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true)
            );
        } catch (Exception $e) {
            wc_add_notice( $e->getMessage(), 'error' );
            return array(
                'result' => 'false',
                'redirect' => ''
            );

        }


    }

    private function insertCashBabaPayment($paymentInfo,$orderId)
    {
        $this->deleteCbData($orderId);
        global $wpdb;

        $insert = $wpdb->insert($wpdb->prefix . $this->table, array(
            "payment_id"       => sanitize_text_field($paymentInfo['paymentId']),
            "reference_id"         => sanitize_text_field($paymentInfo['referenceId']),
            "cashbaba_url"             => sanitize_text_field($paymentInfo['cashBabaUrl']),
            "generate_order_id" => sanitize_text_field($paymentInfo['orderId']),
            "original_order_id"     => sanitize_text_field($orderId),
            "transaction_status"             => sanitize_text_field($paymentInfo['transactionStatus']),
            "transaction_message"             => sanitize_text_field($paymentInfo['message']),
        ));

        $this->log("insert log information");
        $this->log($insert);
        return $insert;
    }

    private function deleteCbData($orderId)
    {
        global $wpdb;

        $delete = $wpdb->delete($wpdb->prefix . $this->table, array(
            "original_order_id"       => sanitize_text_field($orderId),

        ));

        $this->log("delete log information");
        $this->log($delete);
        return $delete;
    }

    private function getTransactionCb($orderId){
        global $wpdb;

        $tableName = $wpdb->prefix . $this->table;
        $result = $wpdb->get_row("SELECT * FROM $tableName WHERE original_order_id = $orderId",ARRAY_A);
        return $result;
    }

    private function getTransactionCbAferCallBack($generateOrderId,$paymentId,$referenceNumber){
        global $wpdb;

        $tableName = $wpdb->prefix . $this->table;
        $result = $wpdb->get_row("SELECT * FROM $tableName WHERE generate_order_id = '$generateOrderId'  and payment_id= '$paymentId' and reference_id= '$referenceNumber'" ,ARRAY_A);
        return $result;
    }




}
