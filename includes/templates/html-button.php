<?php
/**
 * HTML template for Sign-in buttons
 *
 * @package AZTemi\Sign_In_With_Solana
 */

namespace AZTemi\Sign_In_With_Solana;

// die if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="<?php echo esc_attr( PLUGIN_ID ); ?>" style="display: none; clear: both; padding-top: 1rem;">
	<button class="<?php echo esc_attr( $class ); ?>" data-attr="sign_in_button" type="button">
		<?php echo esc_attr( $text ); ?>
	</button>
</div>
