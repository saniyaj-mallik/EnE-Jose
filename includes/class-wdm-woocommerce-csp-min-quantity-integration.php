<?php
/**
 * Plugin Integration
 *
 * @package WDM_WooCommerce_CSP_Minimum_Quantity
 */

/**
 * Plugin Integration Class
 */
class WDM_WooCommerce_CSP_Min_Quantity_Integration {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Add cart validation.
		add_action( 'woocommerce_cart_updated', array( $this, 'validate_cart_items' ) );

		// Add product validation messages.
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'add_min_quantity_notice' ) );
	}


	/**
	 * Validate cart items to enforce minimum quantities
	 */
	public function validate_cart_items() {
		if ( is_admin() ) {
			return;
		}

		$user_id = get_current_user_id();
		$plugin = new WDM_WooCommerce_CSP_Minimum_Quantity();

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = $cart_item['product_id'];
			$quantity = $cart_item['quantity'];

			if ( 'yes' !== get_post_meta( $product_id, '_enable_csp_min_quantity', true ) ) {
				continue;
			}

			// Get minimum quantity.
			if ( $user_id > 0 ) {
				$min_quantity = $plugin->get_csp_min_quantity( $product_id, $user_id );
				if ( $min_quantity <= 0 ) {
					$min_quantity = get_post_meta( $product_id, '_default_min_quantity', true );
				}
			} else {
				$min_quantity = get_post_meta( $product_id, '_default_min_quantity', true );
			}

			// Validate quantity.
			if ( $min_quantity > 0 && $quantity < $min_quantity ) {

				WC()->cart->set_quantity( $cart_item_key, $min_quantity, true );

				$product = wc_get_product( $product_id );
				wc_add_notice(
					sprintf(
						'The minimum order quantity for "%s" is %d.',
						$product->get_name(),
						$min_quantity
					),
					'error'
				);
			}
		}
	}

	/**
	 * Add minimum quantity notice on product page
	 */
	public function add_min_quantity_notice() {
		global $product;

		if ( ! $product ) {
			return;
		}

		// Check if CSP minimum quantity is enabled for this product.
		if ( 'yes' !== get_post_meta( $product->get_id(), '_enable_csp_min_quantity', true ) ) {
			return;
		}

		$user_id = get_current_user_id();
		$plugin = new WDM_WooCommerce_CSP_Minimum_Quantity();

		// Get minimum quantity.
		if ( $user_id > 0 ) {
			$min_quantity = $plugin->get_csp_min_quantity( $product->get_id(), $user_id );

			if ( $min_quantity <= 0 ) {
				// For users without CSP rules, use default min quantity.
				$min_quantity = get_post_meta( $product->get_id(), '_default_min_quantity', true );
			}
		} else {
			// For guests, use default min quantity.
			$min_quantity = get_post_meta( $product->get_id(), '_default_min_quantity', true );
		}

		// Display notice if minimum quantity is set.
		if ( $min_quantity > 0 && $min_quantity > 1 ) {
			echo '<div class="woocommerce-info">' . 
					sprintf(
						'The minimum order quantity for this product is %d.',
						$min_quantity
					) . 
				'</div>';
		}
	}
}
