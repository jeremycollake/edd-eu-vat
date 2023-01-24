<?php
/**
 * Template for the EDD EU VAT batch export metabox.
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

<div class="postbox edd-export-payment-history">
	<h3><span><?php esc_html_e( 'Export EU VAT Report', 'edd-eu-vat' ); ?></span></h3>

	<div class="inside">
		<p><?php esc_html_e( 'Download a CSV of all European VAT collected, which you can use for your EU VAT/MOSS tax return.', 'edd-eu-vat' ); ?></p>

		<form id="edd-export-eu-vat" class="edd-export-form edd-import-export-form" method="post">
			<fieldset class="edd-from-to-wrapper">
				<legend class="screen-reader-text">
					<?php esc_html_e( 'Export EU VAT Report', 'edd-eu-vat' ); ?>
				</legend>
				<label for="edd-eu-vat-export-start" class="screen-reader-text">
					<?php esc_html_e( 'Choose start date', 'edd-eu-vat' ); ?>
				</label>
				<?php
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				// reason: Escaped in EDD
				echo EDD()->html->date_field(
				// phpcs:enable
					[
						'id'          => 'edd-eu-vat-export-start',
						'name'        => 'start',
						'placeholder' => esc_attr__( 'Choose start date', 'edd-eu-vat' ),
						'class'       => 'edd-export-start',
					]
				);
				?>
				<label for="edd-eu-vat-export-end" class="screen-reader-text">
					<?php esc_html_e( 'Choose end date', 'edd-eu-vat' ); ?>
				</label>
				<?php
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				// reason: Escaped in EDD
				echo EDD()->html->date_field(
				// phpcs:enable
					[
						'id'          => 'edd-eu-vat-export-end',
						'name'        => 'end',
						'placeholder' => esc_attr__( 'Choose end date', 'edd-eu-vat' ),
						'class'       => 'edd-export-end',
					]
				);
				?>
			</fieldset>
			<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
			<input type="hidden" name="edd-export-class" value="Batch_VAT_Payments_Export" />
			<input type="hidden" name="export-key" value="<?php echo esc_attr( uniqid() ); ?>" />
			<span>
				<input type="submit" value="<?php esc_attr_e( 'Generate CSV', 'edd-eu-vat' ); ?>" class="button-secondary" />
				<span class="spinner"></span>
			</span>
		</form>

	</div><!-- .inside -->
</div><!-- .postbox -->
