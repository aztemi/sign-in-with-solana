<?php
/**
 * Plugin Name: Sign-in With Solana
 * Plugin URI:  https://apps.aztemi.com/sign-in-with-solana
 * Description: Authenticate with Solana Wallets.
 * Version:     0.1.0
 * Author:      AZTemi
 * Author URI:  https://www.aztemi.link
 * License:     GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: sign-in-with-solana
 * Domain Path: /languages
 *
 * Requires PHP:         7.2
 * Requires at least:    5.2
 * Tested up to:         6.8
 *
 * @package AZTemi\Sign_In_With_Solana
 */

namespace AZTemi\Sign_In_With_Solana;

// die if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

// initialize the plugin
function init_plugin() {
	// define named constants
	require_once __DIR__ . '/includes/constants.php';
	define_constants( __DIR__, __FILE__ );

	// load plugin core class and initialize its instance
	require_once __DIR__ . '/includes/class-sign-in-with-solana.php';
	new Sign_In_With_Solana();
}

// initialize plugin on plugins_loaded
add_action( 'plugins_loaded', __NAMESPACE__ . '\init_plugin' );
