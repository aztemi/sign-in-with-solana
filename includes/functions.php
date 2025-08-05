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
 * Check if BC Math Arbitrary Precision Mathematics extension is installed or not.
 *
 * @return bool true if service is installed, otherwise false.
 */
function is_bcmath_installed() {
	return extension_loaded('bcmath');
}


/**
 * Check if GNU Multiple Precision Mathematics extension is installed or not.
 *
 * @return bool true if service is installed, otherwise false.
 */
function is_gmp_installed() {
	return extension_loaded('gmp');
}


/**
 * Check if WooCommerce plugin is activated or not.
 *
 * @return bool true if WooCommerce is activated, otherwise false.
 */
function is_woocommerce_activated() {
	return in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) );
}


/**
 * Display an error notice message on the admin screen.
 *
 * @param string $notice Error message to display.
 */
function show_error_notice( $notice ) {
	add_action(
		'admin_notices',
		function () use ( $notice ) {
			echo wp_kses_post( '<div class="notice notice-error"><p>' . $notice . '</p></div>'  );
		}
	);
}


/**
 * Generate standardized hook name
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
 * Shorten a given wallet address
 * by returning the first 6 and last 6 characters, with "..." in between.
 *
 * @param string|null $address The address string to shorten.
 * @return string The shortened address or an empty string if input is null or empty.
 */
function shorten_address( $address ) {
	return empty( $address ) ? '' : substr( $address, 0, 6 ) . '...' . substr( $address, -6 );
}


/**
 * Decode a Base58-encoded string to binary using GMP
 *
 * @param string $input Base58-encoded string
 * @return string Binary decoded string
 * @throws InvalidArgumentException if input contains invalid characters
 */
function base58_decode_gmp( $input ) {
	$alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
	$base = strlen( $alphabet );
	$length = strlen( $input );
	$decoded = gmp_init( 0, 10 );

	for ( $i = 0; $i < $length; $i++ ) {
		$char = $input[ $i ];
		$pos = strpos( $alphabet, $char );

		if ( false === $pos ) {
			throw new InvalidArgumentException( esc_attr( "Invalid Base58 character: $char" ) );
		}

		$decoded = gmp_add( gmp_mul( $decoded, $base ), $pos );
	}

	$hex = gmp_strval( $decoded, 16 );
	if ( strlen( $hex ) % 2 !== 0 ) {
		$hex = '0' . $hex;
	}

	$binary = hex2bin( $hex );

	// handle leading zeros (represented as "1" in base58)
	$pad = 0;
	while ( $pad < $length && '1' === $input[ $pad ] ) {
		$pad++;
	}

	return str_repeat( "\x00", $pad ) . $binary;
}


/**
 * Decode a Base58-encoded string to binary using BCMath
 *
 * @param string $input Base58-encoded string
 * @return string Binary decoded string
 * @throws InvalidArgumentException if input contains invalid characters
 */
function base58_decode_bcmath( $input ) {
	$alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
	$base = strlen( $alphabet );
	$length = strlen( $input );
	$num = '0';

	// Convert Base58 to base10 using bcmath
	for ( $i = 0; $i < $length; $i++ ) {
		$char = $input[ $i ];
		$pos = strpos( $alphabet, $char );

		if ( false === $pos ) {
			throw new InvalidArgumentException( esc_attr( "Invalid Base58 character: $char" ) );
		}

		$num = bcmul( $num, $base );
		$num = bcadd( $num, (string) $pos );
	}

	// Convert base10 to hex
	$hex = '';
	while ( bccomp( $num, '0' ) > 0 ) {
		$rem = bcmod( $num, '256' );
		$num = bcdiv( $num, '256', 0 );
		$hex = str_pad( dechex( $rem ), 2, '0', STR_PAD_LEFT ) . $hex;
	}

	// Convert hex to binary
	$binary = '' === $hex ? '' : hex2bin( $hex );

	// Handle leading zeros (encoded as '1's in Base58)
	$pad = 0;
	while ( $pad < $length && '1' === $input[ $pad ] ) {
		$pad++;
	}

	return str_repeat( "\x00", $pad ) . $binary;
}
