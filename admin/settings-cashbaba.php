<?php
/**
 * Settings for PayPal Standard Gateway.
 *
 * @package WooCommerce\Classes\Payment
 */

defined( 'ABSPATH' ) || exit;

return [
    'enabled'                             => [
        'title'       => __( 'Enable/Disable', 'cashbaba-woocommerce' ),
        'label'       => __( 'Enable CashBaba Gateway', 'cashbaba-woocommerce' ),
        'type'        => 'checkbox',
        'description' => '',
        'default'     => 'no',
    ],
    'title'                               => [
        'title'       => __( 'Checkout page title', 'cashbaba-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'This controls the title which the user sees during checkout.', 'cashbaba-woocommerce' ),
        'default'     => __( 'Pay by CashBaba', 'cashbaba-woocommerce' ),
        'desc_tip'    => true,

    ],
    'description'                         => [
        'title'       => __( 'Checkout page description', 'cashbaba-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'This controls the description which the user sees during checkout.', 'cashbaba-woocommerce' ),
        'default'     => __( 'Pay with your CashBaba Wallet.', 'cashbaba-woocommerce' ),
        'desc_tip'    => true,
    ],
    'testmode'                            => [
        'title'       => __( 'Test mode', 'cashbaba-woocommerce' ),
        'label'       => __( 'Enable Test Mode', 'cashbaba-woocommerce' ),
        'type'        => 'checkbox',
        'description' => __( 'Place the payment gateway in test mode using test information.', 'cashbaba-woocommerce' ),
        'default'     => 'yes',
        'desc_tip'    => true,
    ],
    'test_client_id'                => [
        'title'       => __( 'Test Client Id', 'cashbaba-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'Get your Test Client Id from Cashbaba Team', 'cashbaba-woocommerce' ),
        'default'     => '',
        'desc_tip'    => true,
    ],
    'test_client_secret'                     => [
        'title'       => __( 'Test Client Secret', 'cashbaba-woocommerce' ),
        'type'        => 'password',
        'description' => __( 'Get your Test Client Secret from Cashbaba Team', 'cashbaba-woocommerce' ),
        'default'     => '',
        'desc_tip'    => true,
    ],
    'test_merchant_id'                => [
        'title'       => __( 'Test Merchant Id', 'cashbaba-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'Get your Test Merchant Id from Cashbaba Team', 'cashbaba-woocommerce' ),
        'default'     => '',
        'desc_tip'    => true,
    ],
    'test_customer_id'                => [
        'title'       => __( 'Test Customer Id', 'cashbaba-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'Get your Test Customer Id from Cashbaba Team', 'cashbaba-woocommerce' ),
        'default'     => '',
        'desc_tip'    => true,
    ],
    'live_client_id'                => [
        'title'       => __( 'Live Client Id', 'cashbaba-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'Get your Live Client Id from Cashbaba Team', 'cashbaba-woocommerce' ),
        'default'     => '',
        'desc_tip'    => true,
    ],
    'live_client_secret'                     => [
        'title'       => __( 'Live  Client Secret', 'cashbaba-woocommerce' ),
        'type'        => 'password',
        'description' => __( 'Get your Live  Client Secret from Cashbaba Team', 'cashbaba-woocommerce' ),
        'default'     => '',
        'desc_tip'    => true,
    ],
    'live_merchant_id'                => [
        'title'       => __( 'Live  Merchant Id', 'cashbaba-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'Get your Live  Merchant Id from Cashbaba Team', 'cashbaba-woocommerce' ),
        'default'     => '',
        'desc_tip'    => true,
    ],
    'live_customer_id'                => [
        'title'       => __( 'Live  Customer Id', 'cashbaba-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'Get your Live  Customer Id from Cashbaba Team', 'cashbaba-woocommerce' ),
        'default'     => '',
        'desc_tip'    => true,
    ],
    'debug'       => array(
        'title'       => __( 'Debug log', 'cashbaba-woocommerce' ),
        'type'        => 'checkbox',
        'label'       => __( 'Enable logging', 'cashbaba-woocommerce' ),
        'default'     => 'no',
        /* translators: %s: URL */
        'description' => sprintf( __( 'Log CashBaba events inside %s Note: this may log personal information. We recommend using this for debugging purposes only and deleting the logs when finished.', 'cashbaba-woocommerce' ), '<code>' . WC_Log_Handler_File::get_log_file_path( $this->id ) . '</code>' ),
    ),

];
