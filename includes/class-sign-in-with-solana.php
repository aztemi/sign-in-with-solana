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

		// add Sign-in button to login pages
		add_action( 'login_form', array( $this, 'add_sign_in_button' ) );
		if ( is_woocommerce_activated() ) {
			add_action( 'woocommerce_login_form', array( $this, 'add_sign_in_button' ) );
		}

		// add wallet address field to user profile
		add_action( 'show_user_profile', array( $this, 'show_wallet_address_in_user_profile' ) );
		add_action( 'edit_user_profile', array( $this, 'show_wallet_address_in_user_profile' ) );
		add_action( 'personal_options_update', array( $this, 'save_wallet_address_to_user_meta' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_wallet_address_to_user_meta' ) );

		// add wallet address column to users table list
		add_filter( 'manage_users_columns', array( $this, 'add_column_to_users_table' ) );
		add_filter( 'wpmu_users_columns', array( $this, 'add_column_to_users_table' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'show_wallet_address_in_users_table' ), 10, 3 );
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
				$extra_cls = 'button wp-block-button__link';
				break;
			case 'button':
				$str = '<button %s type="button">%s</button>';
				$extra_cls = 'button wp-element-button';
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


	/**
	 * Add Sign-in button to the login page
	 */
	public function add_sign_in_button() {
		$short = '[' . get_hook_name('sign_in_button') . ']';
		echo wp_kses_post( '<div style="margin-bottom: 1rem">' . do_shortcode( $short ) . '</div>' );
	}


	/**
	 * Add custom wallet address field to the user profile page
	 */
	public function show_wallet_address_in_user_profile( $user ) {
		?>
		<?php wp_nonce_field( 'wallet_address_settings', 'wallet_address_settings_nonce', false ); ?>
		<table class="form-table <?php esc_attr_e( PLUGIN_ID ); ?>-table" role="presentation">
		<tr>
			<th>
				<label for="solana_wallet_address"><?php esc_html_e('Solana Wallet Address', 'sign-in-with-solana'); ?></label>
			</th>
			<td>
				<input class="regular-text" style="min-width: 32em" id="solana_wallet_address" name="solana_wallet_address" type="text"
					value="<?php esc_attr_e( get_user_meta( $user->ID, WALLET_ADDRESS_USER_META_KEY, true ) ); ?>" />
				<p class="description"><?php esc_html_e( 'If provided, the user will be able to sign in using the wallet.', 'sign-in-with-solana' ); ?></p>
			</td>
		</tr>
		</table>
		<?php
	}


	/**
	 * Load required dependencies for this class
	 */
	private function get_user_by_wallet_address( $address ) {
		if ( empty( $address ) ) {
			return null;
		}

		$users = get_users( array(
			'meta_key'   => WALLET_ADDRESS_USER_META_KEY,
			'meta_value' => $address,
			'number'     => 1
		) );

		$user = is_wp_error( $users ) ? null : reset( $users );

		return $user;
	}


	/**
	 * Save custom wallet address to user metadata
	 */
	public function save_wallet_address_to_user_meta( $user_id ) {
		if ( isset( $_POST['wallet_address_settings_nonce'] ) ) {
			check_admin_referer( 'wallet_address_settings', 'wallet_address_settings_nonce' );

			if ( current_user_can( 'edit_user', $user_id ) && isset( $_POST['solana_wallet_address'] ) ) {
				$wallet_address = trim( sanitize_text_field( $_POST['solana_wallet_address'] ) );

				if ( ! empty( $wallet_address ) && ! preg_match( '/^[1-9A-HJ-NP-Za-km-z]{32,44}$/', $wallet_address ) ) {
					wp_die( __('Specified Solana wallet address is invalid', 'sign-in-with-solana') );
				}

				if ( $this->get_user_by_wallet_address( $wallet_address ) ) {
					wp_die( __('Specified wallet address is already linked to another user', 'sign-in-with-solana') );
				}

				update_user_meta( $user_id, WALLET_ADDRESS_USER_META_KEY, $wallet_address );
			}
		}
	}


	/**
	 * Add wallet address column to the users list table
	 */
	public function add_column_to_users_table( array $columns ) {
		$columns['solana_wallet_address'] = __('Wallet Address', 'sign-in-with-solana');
		return $columns;
	}


	/**
	 * Show users wallet address on the column in the users list table
	 */
	public function show_wallet_address_in_users_table( $output, $column_name, $user_id ) {
		if ( 'solana_wallet_address' === $column_name ) {
			return esc_attr( shorten_address( get_user_meta( $user_id, WALLET_ADDRESS_USER_META_KEY, true ) ) );
		}
	}
}
