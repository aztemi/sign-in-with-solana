<?php
/**
 * Helper functions and utilities.
 *
 * @package AZTemi\Sign_In_With_Solana
 */

namespace AZTemi\Sign_In_With_Solana;

// die if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Generate standardized hook name
 *
 * by prefixing with PLUGIN_ID, replacing dashes with underscores, and converting to lowercase.
 *
 * Example:
 *   If PLUGIN_ID is 'login-with-solana' and $name is 'custom-hook',
 *   the result will be 'login_with_solana_custom_hook'.
 *
 * @param string $name The base name of the hook.
 * @return string The formatted hook name.
 */
function get_hook_name( $name ) {
	return strtolower( str_replace( '-', '_', PLUGIN_ID . '_' . $name ) );
}


/**
 * Check if WooCommerce plugin is activated or not.
 *
 * @return bool true if WooCommerce is activated, otherwise false.
 */
function is_woocommerce_activated() {
	return in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) );
}
