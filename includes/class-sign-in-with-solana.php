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

		add_action( 'init', array( $this, 'run' ) );
	}


	/**
	 * Load required dependencies for this class
	 */
	private function load_dependencies() {
		// load plugin helper functions
		require_once PLUGIN_DIR . '/includes/functions.php';
	}


	/**
	 * Register and execute hooks if all dependencies are available
	 */
	public function run() {
		// return if required dependencies are missing
		if ( ! $this->is_available() ) {
			return;
		}

		// register hooks
		$this->register_hooks();
		$this->register_ajax_callbacks();
	}


	/**
	 * Register action hooks
	 */
	private function register_hooks() {
		// enqueue style and javascript files
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// add Login & Register buttons
		add_action( 'login_form', array( $this, 'add_login_button' ) );
		add_action( 'register_form', array( $this, 'add_register_button' ) );

		// add wallet address field to user profile
		add_action( 'show_user_profile', array( $this, 'show_wallet_address_in_user_profile' ) );
		add_action( 'edit_user_profile', array( $this, 'show_wallet_address_in_user_profile' ) );
		add_action( 'personal_options_update', array( $this, 'save_user_profile' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_profile' ) );

		// add wallet address column to users table list
		add_filter( 'manage_users_columns', array( $this, 'add_column_to_users_table' ) );
		add_filter( 'wpmu_users_columns', array( $this, 'add_column_to_users_table' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'show_wallet_address_in_users_table' ), 10, 3 );

		// add Sign-in buttons and wallet address fields to WooCommerce
		if ( is_woocommerce_activated() ) {
			add_action( 'woocommerce_login_form', array( $this, 'add_login_button' ) );
			add_action( 'woocommerce_register_form', array( $this, 'add_register_button' ) );
			add_action( 'woocommerce_edit_account_form_fields', array( $this, 'add_nonce_field' ) );
			add_filter( 'woocommerce_edit_account_form_fields', array( $this, 'wc_add_account_form_fields' ), 10, 1 );
			add_action( 'woocommerce_save_account_details', array( $this, 'wc_save_account_form_fields' ), 10, 1 );
		}
	}


	/**
	 * Register callback handlers for AJAX requests
	 */
	private function register_ajax_callbacks() {
		// action
		define_constant( 'SIGN_IN', get_hook_name('sign_in') );

		// handler to verify message signature and login user
		add_action( 'wp_ajax_' . SIGN_IN, array( $this, 'validate_and_login' ) );
		add_action( 'wp_ajax_nopriv_' . SIGN_IN, array( $this, 'validate_and_login' ) );
	}


	/**
	 * Validate plugin dependencies.
	 */
	private function is_available() {
		// check if GMP or BCMath extension for base58 decoding is installed
		if ( ! is_gmp_installed() && ! is_bcmath_installed() ) {
			show_error_notice( __( '<b>Sign-in With Solana</b> plugin requires <b>GMP</b> or <b>BCMath</b>. Please install <b>GMP</b> or <b>BCMath</b> extension for PHP.', 'sign-in-with-solana' ) );
			return false;
		}

		return true;
	}


	/**
	 * Check if specified address conforms with Solana wallet address spec or not
	 */
	private function is_solana_wallet_address( $address ) {
		return preg_match( '/^[1-9A-HJ-NP-Za-km-z]{32,44}$/', $address );
	}


	/**
	 * Check if new users registration is enabled or not
	 */
	private function can_users_register() {
		// WP Settings > General > "Anyone can register" option
		if ( get_option( 'users_can_register' ) ) {
			return true;
		}

		// WC Settings > Accounts & Privacy > Allow customers to create an account - on "My Account" page option
		if ( is_woocommerce_activated() && ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Register a new user account using base58-encoded wallet address as username
	 */
	private function create_user_with_wallet_address( $address_b58 ) {
		$address_b64 = $this->convert_base58_solana_address_to_base64( $address_b58 );
		if ( is_wp_error( $address_b64 ) ) {
			return null;
		}

		$username = 'sol_' . $address_b58; // add common prefix to simplify sorting and searching in users table
		$user_id = wp_create_user( $username, wp_generate_password() );
		if ( is_wp_error( $user_id ) ) {
			return null;
		}

		$this->update_user_wallet_address( $user_id, $address_b58, $address_b64 );

		return get_user( $user_id );
	}


	/**
	 * Find a user account by base58-encoded wallet address
	 */
	private function get_user_by_wallet_address( $address_b58 ) {
		if ( empty( $address_b58 ) ) {
			return null;
		}

		$users = get_users( array(
			'meta_key'   => WALLET_ADDRESS_BASE58_META_KEY,
			'meta_value' => $address_b58,
			'number'     => 1,
		) );

		return is_wp_error( $users ) ? null : reset( $users );
	}


	/**
	 * Return user wallet address in specified encoding
	 */
	private function get_user_wallet_address( $user_id, $encoding = 'b58' ) {
		$key = 'b64' === $encoding ? WALLET_ADDRESS_BASE64_META_KEY : WALLET_ADDRESS_BASE58_META_KEY;
		return get_user_meta( $user_id, $key, true );
	}


	/**
	 * Update wallet address in base58 and base64 in user metadata
	 */
	private function update_user_wallet_address( $user_id, $address_b58, $address_b64 ) {
		update_user_meta( $user_id, WALLET_ADDRESS_BASE58_META_KEY, $address_b58 );
		update_user_meta( $user_id, WALLET_ADDRESS_BASE64_META_KEY, $address_b64 );
	}


	/**
	 * Return a custom message string for wallet to sign
	 */
	private function get_message_to_sign() {
		return 'Sign with your wallet to verify ownership and login to ' . wp_parse_url( get_site_url(), PHP_URL_HOST ) . '.';
	}


	/**
	 * Convert a Base58-encoded Solana address to Base64
	 */
	private function convert_base58_solana_address_to_base64( $address_b58 ) {
		if ( ! $this->is_solana_wallet_address( $address_b58 ) ) {
			return new \WP_Error( 'wallet_not_valid', __( 'Specified Solana wallet address is not valid', 'sign-in-with-solana' ) );
		}

		$address_binary = $this->base58_decode( $address_b58 );
		if ( 32 !== strlen( $address_binary ) ) { // Solana pubkeys are 32 bytes
			return new \WP_Error( 'wallet_not_valid', __( 'Specified Solana wallet address is not valid', 'sign-in-with-solana' ) );
		}

		return base64_encode($address_binary);
	}


	/**
	 * Decode a Base58-encoded string to binary
	 */
	private function base58_decode( $address ) {
		try {
			return is_gmp_installed() ? base58_decode_gmp( $address ) : base58_decode_bcmath( $address );
		} catch ( \Exception $e ) {
			return '';
		}
	}


	/**
	 * Verify a Solana signature given base64-encoded inputs
	 */
	private function verify_signature_base64( $message, $signature_b64, $public_key_b64 ) {
		$signature = base64_decode( $signature_b64 );
		$public_key = base64_decode( $public_key_b64 );

		if ( ( strlen( $signature ) !== SODIUM_CRYPTO_SIGN_BYTES ) || ( strlen( $public_key ) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES ) ) {
			return false;
		}

		return sodium_crypto_sign_verify_detached( $signature, $message, $public_key );
	}


	/**
	 * Register JS scripts and CSS styles
	 */
	public function enqueue_scripts() {
		// enqueue css files
		$css = '/build/326.css';
		$css_url = PLUGIN_URL . $css;
		$css_dir = PLUGIN_DIR . $css;
		$handle  = PLUGIN_ID . '_css';
		wp_enqueue_style( $handle, $css_url, array(), filemtime( $css_dir ) );
		wp_style_add_data( $handle, 'rtl', 'replace' );

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
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'pluginId' => PLUGIN_ID,
				'action'   => SIGN_IN,
				'nonce'    => wp_create_nonce('ajax_nonce'),
				'message'  => $this->get_message_to_sign(),
			)
		);
		wp_enqueue_script( $handle );
	}


	/**
	 * Show plugin button with the specified text in div container
	 */
	public function display_sign_in_button( $text ) {
		$class = 'button button-hero wp-element-button';
		$icon = PLUGIN_URL . '/public/img/solana_icon.png';
		require PLUGIN_DIR . '/includes/templates/html-button.php';
	}


	/**
	 * Add Login button to the login form
	 */
	public function add_login_button() {
		$text = __( 'Login with Solana', 'sign-in-with-solana' );
		$this->display_sign_in_button( $text );
	}


	/**
	 * Add Register button to the new user registration form
	 */
	public function add_register_button() {
		$text = __( 'Register with Solana', 'sign-in-with-solana' );
		$this->display_sign_in_button( $text );
	}


	/**
	 * Echo hidden nonce field
	 */
	public function add_nonce_field() {
		wp_nonce_field( 'wallet_address_settings', 'wallet_address_settings_nonce', false );
	}


	/**
	 * Save custom wallet address to user metadata
	 */
	public function save_wallet_address_to_user_meta( $user_id ) {
		if ( isset( $_POST['wallet_address_settings_nonce'] ) ) {
			check_admin_referer( 'wallet_address_settings', 'wallet_address_settings_nonce' );

			if ( current_user_can( 'edit_user', $user_id ) && isset( $_POST['solana_wallet_address'] ) ) {
				$address_b58 = trim( sanitize_text_field( wp_unslash( $_POST['solana_wallet_address'] ) ) );
				$address_b64 = '';

				if ( ! empty( $address_b58 ) ) {
					$address_b64 = $this->convert_base58_solana_address_to_base64( $address_b58 );
					if ( is_wp_error( $address_b64 ) ) {
						return new \WP_Error( 'wallet_not_valid', __( 'Specified Solana wallet address is not valid', 'sign-in-with-solana' ) );
					}

					$user = $this->get_user_by_wallet_address( $address_b58 );
					if ( $user && $user->ID !== $user_id ) {
						return new \WP_Error( 'wallet_not_valid', __( 'Specified wallet address is already linked to another user', 'sign-in-with-solana' ) );
					}
				}

				$this->update_user_wallet_address( $user_id, $address_b58, $address_b64 );
			}
		}

		return true;
	}


	/**
	 * Add custom wallet address field to the user profile page
	 */
	public function show_wallet_address_in_user_profile( $user ) {
		?>
		<?php $this->add_nonce_field(); ?>
		<table class="form-table <?php echo esc_attr( PLUGIN_ID ); ?>-table" role="presentation">
		<tr>
			<th>
				<label for="solana_wallet_address"><?php esc_html_e( 'Solana Wallet Address', 'sign-in-with-solana' ); ?></label>
			</th>
			<td>
				<input class="regular-text" style="min-width: 32em" id="solana_wallet_address" name="solana_wallet_address" type="text"
					value="<?php echo esc_attr( $this->get_user_wallet_address( $user->ID ) ); ?>" />
				<p class="description"><?php esc_html_e( 'If provided, the user will be able to sign in using the wallet.', 'sign-in-with-solana' ); ?></p>
			</td>
		</tr>
		</table>
		<?php
	}


	/**
	 * Save wallet address from user profile
	 */
	public function save_user_profile( $user_id ) {
		$result = $this->save_wallet_address_to_user_meta( $user_id );
		if ( is_wp_error( $result ) ) {
			wp_die( esc_attr( $result->get_error_message() ) );
		}
	}


	/**
	 * Add custom wallet address field to the WooCommerce customer account details page
	 */
	public function wc_add_account_form_fields() {
		$field_key = 'solana_wallet_address';
		$form_field = array(
			'label' => esc_html_e( 'Solana Wallet Address', 'sign-in-with-solana' ),
			'value' => $this->get_user_wallet_address( get_current_user_id() ),
		);
		woocommerce_form_field( $field_key, $form_field, wc_get_post_data_by_key( $field_key, $form_field['value'] ) );
	}


	/**
	 * Save wallet address from WooCommerce customer account details page
	 */
	public function wc_save_account_form_fields( $user_id ) {
		$result = $this->save_wallet_address_to_user_meta( $user_id );
		if ( is_wp_error( $result ) ) {
			wc_add_notice( esc_attr( $result->get_error_message() ), 'error' );
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
			return esc_attr( shorten_address( $this->get_user_wallet_address( $user_id ) ) );
		}
	}


	/**
	 * Validate message signature and log in user if valid.
	 *
	 * Expected POST parameters:
	 * - nonce     (string) : AJAX nonce for CSRF protection.
	 * - signature (string) : Base64-encoded signature of the message to verify identity.
	 * - address   (string) : Base58-encoded public wallet address (Solana public key).
	 *
	 * Response:
	 * - On success: JSON object with 'message' and 'redirect' URL.
	 * - On failure: JSON error with appropriate HTTP status code.
	 */
	public function validate_and_login() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce', 403);
		}

		// retrieve base64-encoded signature
		$signature_b64 = isset( $_POST['signature'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['signature'] ) ) ) : '';
		if ( empty( $signature_b64 ) ) {
			wp_send_json_error( 'Bad Request - Signature missing', 400 );
		}

		// retrieve base58-encoded Solana address (public key)
		$address_b58 = isset( $_POST['address'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['address'] ) ) ) : '';
		if ( empty( $address_b58 ) ) {
			wp_send_json_error( 'Bad Request - Address missing', 400 );
		}

		// get user associated with the wallet address
		$user = $this->get_user_by_wallet_address( $address_b58 );

		// register user if not found and allow registration is enabled
		if ( ! $user && $this->can_users_register() ) {
			$user = $this->create_user_with_wallet_address( $address_b58 );
			if ( ! $user ) {
				wp_send_json_error( 'User registration failed', 500 );
			}
		}

		// return an error if the user is not found or cannot be registered
		if ( ! $user ) {
			wp_send_json_error( 'User account not found', 404 );
		}

		$user_id = $user->ID;
		$message  = $this->get_message_to_sign();
		$address_b64 = $this->get_user_wallet_address( $user_id, 'b64' );

		// verify the signature
		$verified = $this->verify_signature_base64( $message, $signature_b64, $address_b64 );
		if ( ! $verified ) {
			wp_send_json_error( 'Signature verification failed', 403 );
		}

		// signature is valid, log the user in
		wp_clear_auth_cookie();
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );

		// send success response with redirect URL to user's profile page
		wp_send_json_success( array( 'message' => 'OK', 'redirect' => esc_url_raw( get_edit_profile_url() ) ), 200 );
	}
}
