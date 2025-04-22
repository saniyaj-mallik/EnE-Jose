<?php
/**
 * Plugin Activator
 *
 * @package WDM_WooCommerce_CSP_Minimum_Quantity
 */

/**
 * Plugin Activator Class
 */
class WDM_WooCommerce_CSP_Min_Quantity_Activator {

	/**
	 * Runs on plugin activation
	 */
	public static function activate() {
		// Set all existing products to have CSP minimum quantity enabled by default.
		self::set_default_product_settings();
	}

	/**
	 * Set default product settings for all existing products
	 */
	private static function set_default_product_settings() {
		// Get all published simple products.
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => 'simple',
				),
			),
		);

		$product_ids = get_posts( $args );

		// Set default settings for each product.
		foreach ( $product_ids as $product_id ) {
			// Only set if the meta doesn't already exist.
			if ( ! metadata_exists( 'post', $product_id, '_enable_csp_min_quantity' ) ) {
				update_post_meta( $product_id, '_enable_csp_min_quantity', 'yes' );
			}

			if ( ! metadata_exists( 'post', $product_id, '_default_min_quantity' ) ) {
				update_post_meta( $product_id, '_default_min_quantity', 1 );
			}
		}
	}
}
