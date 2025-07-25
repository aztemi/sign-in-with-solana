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
	}
}
