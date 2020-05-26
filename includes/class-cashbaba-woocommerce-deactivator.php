<?php

/**
 * Fired during plugin deactivation
 *
 * @link       tapos.pro
 * @since      1.0.0
 *
 * @package    Cashbaba_Woocommerce
 * @subpackage Cashbaba_Woocommerce/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Cashbaba_Woocommerce
 * @subpackage Cashbaba_Woocommerce/includes
 * @author     Biswa Nath Ghosh <tapos.aa@gmail.com>
 */
class Cashbaba_Woocommerce_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

}
