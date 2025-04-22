<?php
/**
 * Plugin Name: WDM WooCommerce CSP Minimum Quantity
 * Description: Sets minimum product quantities based on Customer Specific Pricing rules
 * Version: 1.0.0
 * Author: Saniyaj Mallik
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 5.0
 *
 * @package WDM_ooCommerce_CSP_Minimum_Quantity
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if WooCommerce is active.
if ( ! in_array(
	'woocommerce/woocommerce.php',
	apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
	true
) ) {
	return;
}

// Check if Customer Specific Pricing for WooCommerce is active.
if ( ! in_array(
	'customer-specific-pricing-for-woocommerce/customer-specific-pricing-for-woocommerce.php',
	apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
	true
) ) {
	add_action(
		'admin_notices',
		function() {
			echo '<div class="error"><p>WDM WooCommerce CSP Minimum Quantity requires Customer Specific Pricing for WooCommerce to be installed and activated.</p></div>';
		}
	);
	return;
}

// Define plugin constants.
define( 'WDM_WOO_CSP_MIN_QTY_VERSION', '1.0.0' );
define( 'WDM_WOO_CSP_MIN_QTY_PATH', plugin_dir_path( __FILE__ ) );
define( 'WDM_WOO_CSP_MIN_QTY_URL', plugin_dir_url( __FILE__ ) );

// Include the activator class.
require_once WDM_WOO_CSP_MIN_QTY_PATH . 'includes/class-wdm-woocommerce-csp-min-quantity-activator.php';

// Include the integration class.
require_once WDM_WOO_CSP_MIN_QTY_PATH . 'includes/class-wdm-woocommerce-csp-min-quantity-integration.php';

// Include the main class file.
require_once WDM_WOO_CSP_MIN_QTY_PATH . 'class-wdm-woocommerce-csp-minimum-quantity.php';

// Register activation hook.
register_activation_hook( __FILE__, array( 'WDM_WooCommerce_CSP_Min_Quantity_Activator', 'activate' ) );

// Initialize the plugin.
new WDM_WooCommerce_CSP_Minimum_Quantity();

// Initialize the integration.
new WDM_WooCommerce_CSP_Min_Quantity_Integration();
