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
		// load plugin helper functions
		require_once PLUGIN_DIR . '/includes/functions.php';
	}


	/**
	 * Register action hooks
	 */
	private function register_hooks() {
		// configure all components
		add_action( 'init', array( $this, 'configure_components' ) );

		// enqueue style and javascript files
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}


	/**
	 * Load and configure all supported components
	 */
	public function configure_components() {
		// load all supported components
		require_once PLUGIN_DIR . '/includes/components.php';
		define_constant( 'COMPONENTS', get_all_components() );

		// register shortcodes for all components
		foreach ( COMPONENTS as $k => $v ) {
			add_shortcode( $k, array( $this, 'handle_shortcodes' ) );
		}
	}


	/**
	 * Handle shortcode actions
	 */
	public function handle_shortcodes( $atts, $content, $shortcode_tag ) {
		if (! array_key_exists( $shortcode_tag, COMPONENTS )) return '';

		// prepare attributes
		$component = COMPONENTS[ $shortcode_tag ];
		$atts = shortcode_atts( $component['attribute'], $atts, $shortcode_tag );
		if ( ! $content ) $content = $component['content'];

		$str = '';
		$extra_cls = '';

		// return html markup based on component type
		switch ( $component['type'] ) {
			case 'link_button':
				$str = '<a %s href="">%s</a>';
				$extra_cls = 'button';
				break;
			case 'button':
				$str = '<button %s type="button">%s</button>';
				$extra_cls = 'button';
				break;
			case 'span':
				$str = '<span %s>%s</span>';
				break;
			default:
				break;
		}

		$cls = sprintf( '%s %s %s', PLUGIN_ID, $component['class'], $extra_cls );
		$cls = implode( ' ', array_filter( explode( ' ', $cls ) ) );
		$placeholder = 'class="' . $cls . '" data-attr="' . $component['id'] . '"';
		$str = sprintf( $str, $placeholder, $content );

		return $str;
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
