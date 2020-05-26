<?php

/**
 * Fired during plugin activation
 *
 * @link       tapos.pro
 * @since      1.0.0
 *
 * @package    Cashbaba_Woocommerce
 * @subpackage Cashbaba_Woocommerce/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cashbaba_Woocommerce
 * @subpackage Cashbaba_Woocommerce/includes
 * @author     Biswa Nath Ghosh <tapos.aa@gmail.com>
 */
class Cashbaba_Woocommerce_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( ! self::has_woocommerce() ) {
			return;
		}
		self::install();
	}

	/**
	 * @return bool
	 */
	public static function has_woocommerce() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Install table for bkash
	 */
	public static function install() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'cashbaba_transactions';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `payment_id` varchar(255) DEFAULT NULL,
                  `cashbaba_url` varchar(255) DEFAULT NULL,
                  `trx_id` varchar(255) DEFAULT NULL,
                  `transaction_status` varchar(255) DEFAULT NULL,
                  `invoice_number` varchar(255) DEFAULT NULL,
                  `order_number` varchar(15) DEFAULT NULL,
                  `amount` float NOT NULL DEFAULT '0',
                  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

}
