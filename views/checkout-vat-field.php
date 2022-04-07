<?php
/**
 * Template for VAT field on Checkout.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<p id="edd-card-vat-wrap">
	<label for="edd-vat-number" class="edd-label">
		<?php esc_html_e( 'VAT Number', 'edd-eu-vat' ); ?>
		<?php if ( $vat_required ) : ?>
			<span class="edd-required-indicator">*</span>
		<?php endif; ?>
	</label>
	<span class="edd-description"><?php echo esc_html( apply_filters( 'edd_vat_checkout_vat_field_description', __( 'Enter the VAT number of your company.', 'edd-eu-vat' ) ) ); ?></span>
	<span class="edd-vat-number-wrap">
		<input
			type="text"
			name="vat_number"
			id="edd-vat-number"
			class="edd-vat-number-input<?php echo $vat_required ? ' required' : ''; ?>"
			value="<?php echo esc_attr( $vat_number ); ?>"
			placeholder="<?php echo esc_attr( apply_filters( 'edd_vat_checkout_vat_number_placeholder', __( 'e.g. DE123456789', 'edd-eu-vat' ) ) ); ?>"
		/>

		<input type="button" name="edd-vat-check" id="edd-vat-check-button" class="button edd-vat-check-button" value="<?php esc_attr_e( 'Check', 'edd-eu-vat' ); ?>" />
	</span>

	<?php
	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	// reason: Escaped in checkout-vat-result.php
	echo $vat_check_result;
	// phpcs: disable
	?>
</p>
