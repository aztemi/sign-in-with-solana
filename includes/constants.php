<?php
/**
 * Constants definition.
 *
 * @package AZTemi\Sign_In_With_Solana
 */

namespace AZTemi\Sign_In_With_Solana;

// die if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Define constants
 */
function define_constants( $dir, $file ) {
	define_constant( 'PLUGIN_ID', 'login-with-solana' );
	define_constant( 'PLUGIN_DIR', $dir );
	define_constant( 'PLUGIN_FILE', $file );
	define_constant( 'PLUGIN_URL', untrailingslashit( plugin_dir_url( PLUGIN_FILE ) ) );
	define_constant( 'PLUGIN_BASENAME', plugin_basename( PLUGIN_FILE ) );
}


/**
 * Define a constant if it is not already defined
 */
function define_constant( $name, $value ) {
	$name = __NAMESPACE__ . '\\' . $name;
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}
