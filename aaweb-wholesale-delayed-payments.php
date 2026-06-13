<?php
/**
 * Plugin Name: AAWEB Wholesale Delayed Payments
 * Plugin URI: https://antoapweb.gr/
 * Description: Delayed payment workflow for WooCommerce wholesale customers with admin approval before payment.
 * Version: 1.0.0
 * Author: APOSTOLOU A
 * Author URI: https://antoapweb.gr/
 * Text Domain: aaweb-wholesale-delayed-payments
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 10.0
 * Requires Plugins: woocommerce
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AAWEB_WDP_VERSION', '1.0.0' );
define( 'AAWEB_WDP_FILE', __FILE__ );
define( 'AAWEB_WDP_GATEWAY_ID', 'aaweb_wholesale_pending' );
define( 'AAWEB_WDP_META_KEY', '_aaweb_wholesale_payment_pref' );
define( 'AAWEB_WDP_ROLE', 'wholesale_customer' );

/**
 * WooCommerce HPOS compatibility.
 */
add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				AAWEB_WDP_FILE,
				true
			);
		}
	}
);

/**
 * Admin notice if WooCommerce is not active.
 */
add_action(
	'admin_notices',
	function() {
		if ( class_exists( 'WooCommerce' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>';
		echo esc_html__( 'AAWEB Wholesale Delayed Payments requires WooCommerce to be installed and active.', 'aaweb-wholesale-delayed-payments' );
		echo '</p></div>';
	}
);

/**
 * Check if the current user is a wholesale customer.
 *
 * @return bool
 */
function aaweb_wdp_is_wholesale_user() {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	$user = wp_get_current_user();

	if ( ! $user || empty( $user->roles ) ) {
		return false;
	}

	return in_array( AAWEB_WDP_ROLE, (array) $user->roles, true );
}

/**
 * Check if a specific user ID is wholesale customer.
 *
 * @param int $user_id User ID.
 * @return bool
 */
function aaweb_wdp_is_wholesale_user_id( $user_id ) {
	$user_id = absint( $user_id );

	if ( ! $user_id ) {
		return false;
	}

	$user = get_user_by( 'id', $user_id );

	if ( ! $user || empty( $user->roles ) ) {
		return false;
	}

	return in_array( AAWEB_WDP_ROLE, (array) $user->roles, true );
}

/**
 * Get allowed payment preference values.
 *
 * @return array
 */
function aaweb_wdp_get_payment_options() {
	return array(
		'card'          => __( 'Card payment', 'aaweb-wholesale-delayed-payments' ),
		'bank_transfer' => __( 'Bank transfer', 'aaweb-wholesale-delayed-payments' ),
		'cod'           => __( 'Cash on delivery', 'aaweb-wholesale-delayed-payments' ),
	);
}

/**
 * Get human-readable payment preference label.
 *
 * @param string $pref Payment preference.
 * @return string
 */
function aaweb_wdp_get_payment_label( $pref ) {
	$options = aaweb_wdp_get_payment_options();

	return isset( $options[ $pref ] ) ? $options[ $pref ] : $pref;
}

/**
 * Verify WooCommerce checkout nonce before reading posted checkout data.
 *
 * @return bool
 */
function aaweb_wdp_verify_checkout_nonce() {
	$nonce = '';

	if ( isset( $_POST['woocommerce-process-checkout-nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$nonce = sanitize_text_field( wp_unslash( $_POST['woocommerce-process-checkout-nonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	if ( empty( $nonce ) && isset( $_POST['_wpnonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	if ( empty( $nonce ) ) {
		return false;
	}

	return (bool) wp_verify_nonce( $nonce, 'woocommerce-process_checkout' );
}

/**
 * Get posted wholesale payment preference safely.
 *
 * @return string
 */
function aaweb_wdp_get_posted_payment_pref() {
	if ( ! aaweb_wdp_verify_checkout_nonce() ) {
		return '';
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( ! isset( $_POST['aaweb_wholesale_payment_pref'] ) ) {
		return '';
	}

	$pref = sanitize_key( wp_unslash( $_POST['aaweb_wholesale_payment_pref'] ) );
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	$allowed = array_keys( aaweb_wdp_get_payment_options() );

	if ( ! in_array( $pref, $allowed, true ) ) {
		return '';
	}

	return $pref;
}

/**
 * Force shipping address fields for wholesale users.
 */
add_filter(
	'woocommerce_cart_needs_shipping_address',
	function( $needs_shipping_address ) {
		if ( aaweb_wdp_is_wholesale_user() ) {
			return true;
		}

		return $needs_shipping_address;
	},
	10,
	1
);

/**
 * Add wholesale payment preference field to checkout.
 */
add_action(
	'woocommerce_after_order_notes',
	function( $checkout ) {
		if ( ! aaweb_wdp_is_wholesale_user() ) {
			return;
		}

		echo '<div class="aaweb-wdp-checkout-field">';
		echo '<h3>' . esc_html__( 'Wholesale Payment Preference', 'aaweb-wholesale-delayed-payments' ) . '</h3>';

		woocommerce_form_field(
			'aaweb_wholesale_payment_pref',
			array(
				'type'     => 'select',
				'class'    => array( 'form-row-wide' ),
				'label'    => esc_html__( 'Preferred payment method after order confirmation', 'aaweb-wholesale-delayed-payments' ),
				'required' => true,
				'options'  => array(
					''              => esc_html__( '— Select —', 'aaweb-wholesale-delayed-payments' ),
					'card'          => esc_html__( 'Card payment', 'aaweb-wholesale-delayed-payments' ),
					'bank_transfer' => esc_html__( 'Bank transfer', 'aaweb-wholesale-delayed-payments' ),
					'cod'           => esc_html__( 'Cash on delivery', 'aaweb-wholesale-delayed-payments' ),
				),
			),
			$checkout->get_value( 'aaweb_wholesale_payment_pref' )
		);

		echo '<p class="aaweb-wdp-checkout-note" style="font-size:14px;font-weight:600;margin-top:4px;">';
		echo esc_html__( 'Payment is not made now. We will first confirm availability and then send payment instructions.', 'aaweb-wholesale-delayed-payments' );
		echo '</p>';

		echo '</div>';
	}
);

/**
 * Validate wholesale payment preference.
 */
add_action(
	'woocommerce_checkout_process',
	function() {
		if ( ! aaweb_wdp_is_wholesale_user() ) {
			return;
		}

		$pref = aaweb_wdp_get_posted_payment_pref();

		if ( empty( $pref ) ) {
			wc_add_notice(
				__( 'Please select your preferred wholesale payment method.', 'aaweb-wholesale-delayed-payments' ),
				'error'
			);
		}
	}
);

/**
 * Save preference and set initial wholesale order status to on-hold.
 */
add_action(
	'woocommerce_checkout_create_order',
	function( $order, $data ) {
		if ( ! aaweb_wdp_is_wholesale_user() ) {
			return;
		}

		$pref = aaweb_wdp_get_posted_payment_pref();

		if ( ! empty( $pref ) ) {
			$order->update_meta_data( AAWEB_WDP_META_KEY, $pref );
		}

		$order->set_status(
			'on-hold',
			__( 'AAWEB: Wholesale order is waiting for availability confirmation.', 'aaweb-wholesale-delayed-payments' )
		);
	},
	10,
	2
);

/**
 * Force wholesale checkout orders back to on-hold if another gateway/status changes them.
 */
add_action(
	'woocommerce_checkout_order_processed',
	function( $order_id, $posted_data, $order ) {
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order ) {
			return;
		}

		if ( ! aaweb_wdp_is_wholesale_user_id( $order->get_user_id() ) ) {
			return;
		}

		if ( ! $order->has_status( 'on-hold' ) ) {
			$order->update_status(
				'on-hold',
				__( 'AAWEB: Wholesale order was moved back to on-hold after checkout.', 'aaweb-wholesale-delayed-payments' )
			);
		}
	},
	10000,
	3
);

/**
 * Display wholesale payment preference in admin order screen.
 */
add_action(
	'woocommerce_admin_order_data_after_billing_address',
	function( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$pref = $order->get_meta( AAWEB_WDP_META_KEY );

		if ( empty( $pref ) ) {
			return;
		}

		echo '<p><strong>';
		echo esc_html__( 'Wholesale payment preference:', 'aaweb-wholesale-delayed-payments' );
		echo '</strong> ';
		echo esc_html( aaweb_wdp_get_payment_label( $pref ) );
		echo '</p>';
	}
);

/**
 * Add virtual wholesale pending payment gateway.
 */
add_filter(
	'woocommerce_payment_gateways',
	function( $methods ) {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return $methods;
		}

		if ( ! class_exists( 'AAWEB_WDP_Gateway_Wholesale_Pending' ) ) {

			class AAWEB_WDP_Gateway_Wholesale_Pending extends WC_Payment_Gateway {

				/**
				 * Constructor.
				 */
				public function __construct() {
					$this->id                 = AAWEB_WDP_GATEWAY_ID;
					$this->method_title       = __( 'AAWEB Wholesale Pending Payment', 'aaweb-wholesale-delayed-payments' );
					$this->method_description = __( 'Virtual payment method for wholesale orders without immediate payment.', 'aaweb-wholesale-delayed-payments' );
					$this->has_fields         = false;
					$this->enabled            = 'yes';
					$this->title              = __( 'Payment after confirmation — you are not charged now.', 'aaweb-wholesale-delayed-payments' );
				}

				/**
				 * Process payment.
				 *
				 * @param int $order_id Order ID.
				 * @return array
				 */
				public function process_payment( $order_id ) {
					$order = wc_get_order( $order_id );

					if ( ! $order ) {
						return array(
							'result'   => 'failure',
							'redirect' => wc_get_checkout_url(),
						);
					}

					return array(
						'result'   => 'success',
						'redirect' => $order->get_checkout_order_received_url(),
					);
				}
			}
		}

		$methods[] = 'AAWEB_WDP_Gateway_Wholesale_Pending';

		return $methods;
	}
);

/**
 * Manage available payment gateways.
 */
add_filter(
	'woocommerce_available_payment_gateways',
	function( $gateways ) {
		if ( is_admin() ) {
			return $gateways;
		}

		$is_order_pay = function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-pay' );
		$is_checkout  = function_exists( 'is_checkout' ) && is_checkout();

		if ( ! aaweb_wdp_is_wholesale_user() ) {
			if ( isset( $gateways[ AAWEB_WDP_GATEWAY_ID ] ) ) {
				unset( $gateways[ AAWEB_WDP_GATEWAY_ID ] );
			}

			return $gateways;
		}

		if ( $is_order_pay ) {
			$order_id = absint( get_query_var( 'order-pay' ) );
			$order    = wc_get_order( $order_id );

			if ( $order && $order->has_status( 'pending' ) ) {
				if ( isset( $gateways[ AAWEB_WDP_GATEWAY_ID ] ) ) {
					unset( $gateways[ AAWEB_WDP_GATEWAY_ID ] );
				}

				return $gateways;
			}

			return array();
		}

		if ( $is_checkout && ! $is_order_pay ) {
			$wholesale_gateways = array();

			if ( isset( $gateways[ AAWEB_WDP_GATEWAY_ID ] ) ) {
				$wholesale_gateways[ AAWEB_WDP_GATEWAY_ID ] = $gateways[ AAWEB_WDP_GATEWAY_ID ];
			}

			return $wholesale_gateways;
		}

		if ( isset( $gateways[ AAWEB_WDP_GATEWAY_ID ] ) ) {
			unset( $gateways[ AAWEB_WDP_GATEWAY_ID ] );
		}

		return $gateways;
	},
	10,
	1
);

/**
 * If a wholesale order is moved to processing from frontend, move it back to on-hold.
 */
add_action(
	'woocommerce_order_status_changed',
	function( $order_id, $old_status, $new_status, $order ) {
		if ( is_admin() ) {
			return;
		}

		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order ) {
			return;
		}

		if ( ! aaweb_wdp_is_wholesale_user_id( $order->get_user_id() ) ) {
			return;
		}

		if ( 'processing' === $new_status ) {
			$order->update_status(
				'on-hold',
				__( 'AAWEB: Wholesale order was returned to on-hold until final confirmation.', 'aaweb-wholesale-delayed-payments' )
			);
		}
	},
	1000,
	4
);