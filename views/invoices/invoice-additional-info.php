<?php
/**
 * Invoice Additional Info Template
 *
 * To modify this template, create a folder called `edd_templates` inside of your active theme's directory.
 * Copy this file into that new folder.
 *
 * @version 1.0
 *
 * @var \EDD\Orders\Order|EDD_Payment $order
 */

use Barn2\Plugin\EDD_VAT\Util;

$notes           = edd_invoices_get_custom_order_meta( $order, 'invoices_notes' );
$additional_text = edd_get_option( 'edd-invoices-additional-text' );

// Get the payment VAT details.
$is_eu_payment = Util::is_eu_payment( $order->ID );
$payment_vat   = Util::get_payment_vat( $order->ID );

$should_show = false;

if ( ! empty( $notes ) || ! empty( $additional_text ) || $is_eu_payment ) {
	$should_show = true;
}

if ( ! $should_show ) {
	return;
}

?>
<div class="information">
	<header><?php esc_html_e( 'Additional Information:', 'edd-eu-vat' ); ?></header>

	<?php if ( Util::is_eu_payment( $order->ID ) && ( $payment_vat->is_reverse_charged || $order->tax > 0 ) ) : ?>
		<article>
		<?php if ( $payment_vat->is_reverse_charged ) : ?>
			<div class="vat-note">
				<span class="invoice-label"><?php esc_html_e( 'VAT reverse charged', 'edd-eu-vat' ); ?></span>
			</div>
		<?php elseif ( $order->tax > 0 ) : ?>
			<div class="vat-note">
				<span class="invoice-label">
					<?php
					echo esc_html(
						sprintf(
						/* translators: %s is the VAT tax rate */
							__( 'VAT charged at %s%%', 'edd-eu-vat' ),
							$order->tax_rate * 100
						)
					);
					?>
				</span>
			</div>
		<?php endif; ?>
		</article>
	<?php endif; ?>

	<article>
		<?php
		// Customer Notes.
		if ( $notes ) {
			?>
			<!-- Notes -->
			<div class="customer-note">
				<span class="invoice-label"><?php esc_html_e( 'Notes:', 'edd-eu-vat' ); ?></span>
				<?php echo wp_kses_post( wpautop( $notes ) ); ?>
			</div>
			<?php
		}

		// Additional Text.
		if ( $additional_text ) {
			?>
			<!-- Additional Text -->
			<div class="store-note">
				<?php echo wp_kses_post( wpautop( $additional_text ) ); ?>
			</div>
			<?php
		}
		?>

	</article>
</div>
