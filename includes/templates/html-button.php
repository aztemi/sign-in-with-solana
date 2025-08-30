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

<div class="<?php echo esc_attr( PLUGIN_ID ); ?>" style="display: none; text-align: center; clear: both; padding-top: 1rem;">
	<style type="text/css">
	.sign_in_with_solana_or {
		display: flex;
		justify-content: center;
		align-items: center;
		color: currentColor;
		margin: 1rem 0;
	}

	.sign_in_with_solana_or:after,
	.sign_in_with_solana_or:before {
		content: '';
		display: block;
		background-color: currentColor;
		width: 35%;
		height: 1px;
		margin: 0 0.5rem;
	}

	.sign_in_with_solana_btn {
		display: flex;
		align-items: center;
	}

	.sign_in_with_solana_btn img {
		width: 1.2rem;
		margin-right: 0.7rem;
	}
	</style>

	<div class="sign_in_with_solana_or"><?php echo esc_attr__('OR', 'sign-in-with-solana'); ?></div>
	<button class="<?php echo esc_attr( $class ); ?>" data-attr="sign_in_button" type="button">
		<span class="sign_in_with_solana_btn">
			<img src="<?php echo esc_attr( $icon ); ?>" alt="Solana icon" />
			<span><?php echo esc_attr( $text ); ?></span>
		</span>
	</button>
</div>
