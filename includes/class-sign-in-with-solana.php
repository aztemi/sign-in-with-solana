<?php
/**
 * The plugin core class.
 *
 * @package AZTemi\Sign_In_With_Solana
 */

namespace AZTemi\Sign_In_With_Solana;

// die if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}


class Sign_In_With_Solana {

	public function __construct() {
		$this->load_dependencies();
		$this->register_hooks();
	}


	/**
	 * Load required dependencies for this class
	 */
	private function load_dependencies() {
	}


	/**
	 * Register action hooks
	 */
	private function register_hooks() {
		// enqueue style and javascript files
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}


	/**
	 * Register JS scripts and CSS styles
	 */
	public function enqueue_scripts() {
		// enqueue js files
		$js  = PLUGIN_URL . '/build/index.js';
		$php = PLUGIN_DIR . '/build/index.asset.php';
		$handle = PLUGIN_ID . '_js';

		$dependency = require $php;
		array_push( $dependency['dependencies'], 'jquery' );
		wp_register_script( $handle, $js, $dependency['dependencies'], $dependency['version'], true );
		wp_localize_script(
			$handle,
			'SignInWithSolana',
			array(
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'pluginId' => PLUGIN_ID
			)
		);
		wp_enqueue_script( $handle );
	}
}
