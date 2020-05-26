<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              tapos.pro
 * @since             1.0.0
 * @package           Cashbaba_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Payment Gateway CashBaba for WC
 * Plugin URI:        https://cashbaba.com.bd/
 * Description:       An eCommerce payment method that helps you sell anything. Beautifully.
 * Version:           1.0.0
 * Author:            Biswa Nath Ghosh
 * Author URI:        tapos.pro
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cashbaba-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CASHBABA_WOOCOMMERCE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cashbaba-woocommerce-activator.php
 */
function activate_cashbaba_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cashbaba-woocommerce-activator.php';
	Cashbaba_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cashbaba-woocommerce-deactivator.php
 */
function deactivate_cashbaba_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cashbaba-woocommerce-deactivator.php';
	Cashbaba_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cashbaba_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_cashbaba_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cashbaba-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cashbaba_woocommerce() {

	$plugin = new Cashbaba_Woocommerce();
	$plugin->run();

}
run_cashbaba_woocommerce();
