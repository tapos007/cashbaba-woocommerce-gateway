<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       tapos.pro
 * @since      1.0.0
 *
 * @package    Cashbaba_Woocommerce
 * @subpackage Cashbaba_Woocommerce/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Cashbaba_Woocommerce
 * @subpackage Cashbaba_Woocommerce/includes
 * @author     Biswa Nath Ghosh <tapos.aa@gmail.com>
 */
class Cashbaba_Woocommerce_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'cashbaba-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
