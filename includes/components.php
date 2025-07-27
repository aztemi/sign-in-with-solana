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
 * Return all supported components and their configurations
 */
function get_all_components() {
	$default_components = array(
		'wallet_address' => array(
			'type'       => 'span',
			'attribute'  => array( 'userid' => get_current_user_id() ),
		),
		'sign_in_button' => array(
			'type'       => 'button',
			'content'    => __( 'Login with Solana', 'sign-in-with-solana' ),
		),
		'connect_button' => array(
			'type'       => 'button',
			'content'    => __( 'Select Wallet', 'sign-in-with-solana' ),
		),
		'disconnect_button' => array(
			'type'       => 'button',
			'content'    => __( 'Disconnect', 'sign-in-with-solana' ),
		),
	);

	/**
	 * Filters a list of components.
	 *
	 * @since 0.1.0
	 */
	$components = apply_filters( get_hook_name('components_list'), $default_components );

	// Make array keys hook names since components will be registered as Shortcodes
	$rtn = array();
	$default_config = array(
		'type'      => '',
		'class'     => '',
		'content'   => '',
		'attribute' => array(),
	);
	foreach ( $components as $k => $v ) {
		$v['id'] = $k;
		$key = get_hook_name( $k );
		$rtn[ $key ] = array_merge( $default_config, $v );
	}

	return $rtn;
}
