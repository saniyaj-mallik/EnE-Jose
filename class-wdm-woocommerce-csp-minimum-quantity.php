<?php
/**
 * WDM WooCommerce CSP Minimum Quantity - Main Class
 *
 * @package WDM_WooCommerce_CSP_Minimum_Quantity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main plugin class
 */
class WDM_WooCommerce_CSP_Minimum_Quantity {
	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize the plugin.
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize the plugin
	 */
	public function init() {
		// Add product settings.
		add_action( 'woocommerce_product_options_inventory_product_data', array( $this, 'add_product_settings' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_settings' ) );

		// Change quantity input arguments.
		add_filter( 'woocommerce_quantity_input_args', array( $this, 'change_quantity_input_args' ), 10, 2 );
	}

	/**
	 * Add custom settings to product editor
	 */
	public function add_product_settings() {
		echo '<div class="options_group">';

		// Enable/Disable CSP minimum quantity.
		woocommerce_wp_checkbox(
			array(
				'id'          => '_enable_csp_min_quantity',
				'label'       => 'Enable CSP Minimum Quantity',
				'description' => 'When enabled, the minimum quantity for this product will be based on Customer Specific Pricing rules.',
				'default'     => 'yes',
			)
		);

		// Default minimum quantity for guests or users without CSP rules.
		woocommerce_wp_text_input(
			array(
				'id'                => '_default_min_quantity',
				'label'             => 'Default Minimum Quantity',
				'description'       => 'Minimum quantity for guests or users without CSP rules.',
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => '1',
					'min'  => '1',
				),
				'default'           => '1',
			)
		);

		echo '</div>';
	}

	/**
	 * Save product settings
	 *
	 * @param int $post_id Product ID.
	 */
	public function save_product_settings( $post_id ) {
		// Save enable/disable setting.
		$enable_csp_min_quantity = isset( $_POST['_enable_csp_min_quantity'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_enable_csp_min_quantity', $enable_csp_min_quantity );

		// Save default minimum quantity.
		$default_min_quantity = isset( $_POST['_default_min_quantity'] ) ? absint( $_POST['_default_min_quantity'] ) : 1;
		update_post_meta( $post_id, '_default_min_quantity', max( 1, $default_min_quantity ) );
	}

	/**
	 * Change quantity input arguments
	 *
	 * @param array      $args    Quantity arguments.
	 * @param WC_Product $product Product object.
	 * @return array Modified arguments.
	 */
	public function change_quantity_input_args( $args, $product ) {
		// Check if product exists and if CSP minimum quantity is enabled for the product.
		if ( ! $product || 'no' === get_post_meta( $product->get_id(), '_enable_csp_min_quantity', true ) ) {
			return $args;
		}

		// Get the current user ID.
		$user_id = get_current_user_id();

		// If user is logged in, get CSP rules and set minimum quantity.
		if ( $user_id > 0 ) {
			$min_quantity = $this->get_csp_min_quantity( $product->get_id(), $user_id );

			if ( $min_quantity > 0 ) {
				$args['input_value'] = max( $args['input_value'], $min_quantity );
				$args['min_value'] = $min_quantity;
			}
		} else {
			// For guests, use the default minimum quantity setting.
			$default_min_quantity = get_post_meta( $product->get_id(), '_default_min_quantity', true );

			if ( $default_min_quantity ) {
				$args['input_value'] = max( $args['input_value'], $default_min_quantity );
				$args['min_value'] = $default_min_quantity;
			}
		}

		return $args;
	}

	/**
	 * Get the minimum quantity from CSP rules
	 *
	 * @param int $product_id Product ID.
	 * @param int $user_id User ID.
	 * @return int Minimum quantity.
	 */
	public function get_csp_min_quantity( $product_id, $user_id ) {
		// Get CSP quantity-based pricing rules for the product and user.
		$csp_prices = \WuspSimpleProduct\WuspCSPProductPrice::getQuantityBasedPricing( $product_id, $user_id );

		// If no CSP rules found, return 0 (will use default minimum).
		if ( empty( $csp_prices ) ) {
			return 0;
		}

		// Remove the default rule (quantity = 1) as mentioned in dev notes.
		unset( $csp_prices[1] );

		// If no rules left after removing the default, return 0.
		if ( empty( $csp_prices ) ) {
			return 0;
		}

		// Get the minimum quantity from the CSP rules.
		return min( array_keys( $csp_prices ) );
	}
}
