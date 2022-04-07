<?php
/**
 * Template for the EDD Payment metabox in wp-admin.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Barn2\Plugin\EDD_VAT\Util;

$payment = new \EDD_Payment( $payment_id );

if ( ! $payment ) {
	return;
}

$address     = $payment->address;
$payment_vat = Util::get_payment_vat( $payment->ID );
?>
<style type="text/css">
	.edd-payment-vat .checkbox-container {
		display: block;
		padding: 5px 0;
	}
	#edd-vat-details .checkbox-container input {
		display: inline-block;
	}
	.edd-payment-vat textarea.large-text {
		width: 90%;
	}
	#edd-vat-details .column {
		vertical-align: top;
	}
	.edd-payment-vat-evidence h4 {
		border-bottom: 1px solid #eee;
		padding-bottom: 5px;
		float: left;
		margin-bottom: 1em;
	}
	.edd-payment-vat-evidence p {
		margin: 0.2em 0;
		clear: left;
	}
</style>
<div id="edd-vat-details" class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'VAT Details', 'edd-eu-vat' ); ?></h3>
	<div class="inside edd-clearfix">
		<div class="data column-container edd-payment-vat">
			<div class="column edd-payment-vat-summary">
				<p>
					<strong class="label edd-payment-vat-label"><?php \esc_html_e( 'VAT number:', 'edd-eu-vat' ); ?></strong><br/>
					<input type="text" id="edd-payment-vat-number" name="edd-payment-vat-number" value="<?php echo esc_attr( $payment_vat->vat_number ); ?>" class="medium-text" />
				</p>
				<p>
					<strong class="label edd-payment-vat-label"><?php \esc_html_e( 'Consultation number:', 'edd-eu-vat' ); ?></strong><br/>
					<input type="text" id="edd-payment-vat-consultation-number" name="edd-payment-vat-consultation-number" value="<?php echo esc_attr( $payment_vat->consultation_number ); ?>" class="medium-text" />
				</p>
				<p>
					<label class="checkbox-container" for="edd-payment-vat-number-valid">
						<input type="checkbox" id="edd-payment-vat-number-valid" name="edd-payment-vat-number-valid" value="1" <?php \checked( $payment_vat->is_vat_number_valid ); ?> />
						<?php esc_html_e( 'VAT number valid', 'edd-eu-vat' ); ?>
					</label>
					<label class="checkbox-container" for="edd-payment-vat-reverse-charged">
						<input type="checkbox" id="edd-payment-vat-reverse-charged" name="edd-payment-vat-reverse-charged" value="1" <?php \checked( $payment_vat->is_reverse_charged ); ?> />
						<?php esc_html_e( 'VAT reverse charged', 'edd-eu-vat' ); ?>
					</label>
				</p>
			</div>
			<div class="column edd-payment-vat-company">
				<p>
					<strong class="label edd-payment-vat-label"><?php esc_html_e( 'Company name:', 'edd-eu-vat' ); ?></strong><br/>
					<input type="text" id="edd-payment-vat-company-name" name="edd-payment-vat-company-name" value="<?php echo esc_attr( $payment_vat->name ); ?>" class="large-text" />
				</p>
				<p>
					<strong class="label edd-payment-vat-label"><?php esc_html_e( 'Company address:', 'edd-eu-vat' ); ?></strong><br/>
					<textarea rows="2" cols="40" id="edd-payment-vat-company-address" name="edd-payment-vat-company-address" class="large-text"><?php echo esc_textarea( $payment_vat->address ); ?></textarea>
				</p>
			</div>
			<div class="column edd-payment-vat-evidence">
				<h4><?php esc_html_e( 'Location Evidence', 'edd-eu-vat' ); ?></h4>
				<p><strong class="label edd-payment-vat-evidence-label"><?php esc_html_e( 'Billing country:', 'edd-eu-vat' ); ?></strong> <?php echo esc_html( edd_get_country_name( $address['country'] ) ); ?></p>
				<p>
					<strong class="label edd-payment-vat-evidence-label"><?php esc_html_e( 'IP address:', 'edd-eu-vat' ); ?></strong>
					<?php
					// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
					// reason: Escaped in EDD
					echo edd_payment_get_ip_address_url( $payment->ID );
					// phpcs: enable
					?>
				</p>
			</div>
		</div>
	</div>
</div>
