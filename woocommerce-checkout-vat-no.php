<?php
/*
Plugin Name: WooCommerce Checkout VAT No.
Plugin URI:  https://github.com/kgoedecke/woocommerce-checkout-vat-no
Description: Adds a VAT No. field to the WooCommerce checkout process. Customers can enter their VAT No., which will be saved as part of the billing address.
Version:     1.0.0
Author:      HaveALook UG
Author URI:  https://havealooklabs.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: woocommerce-checkout-vat-no
Domain Path: /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	add_action( 'admin_notices', 'woo_checkout_vat_no_required_fail' );
	return;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-woocommerce-checkout-vat-no-plugin.php' );
add_action( 'plugins_loaded', array( 'WooCommerce_Checkout_Vat_No_Plugin', 'get_instance' ) );

/**
 * Show a notice about the WooCommerce plugin is not activated.
 *
 * @since 1.0.0
 */
function woo_checkout_vat_no_required_fail() {
	$message = sprintf( __( 'plugin requires a <a href="%s" target="_blank">WooCommerce</a> plugin.', 'woocommerce-checkout-vat-no' ), esc_url( 'https://wordpress.org/plugins/woocommerce/' ) );

	$html_message = sprintf( '<div class="error"><p><strong>%1$s</strong> %2$s</p></div>', esc_html__( 'WooCommerce Checkout VAT No.', 'woocommerce-checkout-vat-no' ), $message );

	echo wp_kses_post( $html_message );
}
