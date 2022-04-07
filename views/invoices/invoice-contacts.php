<?php
/**
 * Invoice Contacts Template
 *
 * To modify this template, create a folder called `edd_templates` inside of your active theme's directory.
 * Copy this file into that new folder.
 *
 * @version 1.0
 *
 * @var \EDD\Orders\Order|EDD_Payment $order
 */

use Barn2\Plugin\EDD_VAT\Util;

$company = Util::get_company_vat();

$show_website = edd_get_option( 'edd_vat_show_website_address' ) === '1';
$show_email   = edd_get_option( 'edd_vat_company_email', false );

?>

<div class="storefront">
	<header>
		<?php esc_html_e( 'From:', 'edd-eu-vat' ); ?>
	</header>

	<article>
		<div class="address">
			<?php
			// Company Name
			if ( isset( $company->name ) ) {
				?>
				<div class="invoice-label"><?php echo esc_html( $company->name ); ?></div>
				<?php
			}

			// Address + Company Details
			echo wp_kses_post( nl2br( $company->formatted_address ) );
			?>
		</div>
		<?php

		// Vendor Company Registration #
		$company_reg = edd_get_option( 'edd-invoices-number' );
		if ( $company_reg ) {
			?>
			<!-- Vendor Company Registration # -->
			<div class="storefront__registration">
				<span class="invoice-label"><?php esc_html_e( 'Registration:', 'edd-eu-vat' ); ?></span> <?php echo esc_html( $company_reg ); ?>
			</div>
			<?php
		}

		// Vendor Tax/VAT #
		$tax_id = $company->vat_number;
		if ( $tax_id ) {
			?>
			<!-- Vendor Tax/VAT # -->
			<div class="storefront__vat">
				<span class="invoice-label"><?php esc_html_e( 'VAT number:', 'edd-eu-vat' ); ?></span> <?php echo esc_html( $tax_id ); ?>
			</div>
			<?php
		}
		?>
	</article>

	<?php if ( $show_website || ! empty( $show_email ) ) : ?>
		<article style="padding-top:0">
			<?php if ( ! empty( $show_email ) ) : ?>
			<div class="storefront__email">
				<a href="mailto:<?php echo esc_html( antispambot( $show_email ) ); ?>"><?php echo esc_html( $show_email ); ?></a>
			</div>
			<?php endif; ?>

			<?php if ( $show_website ) : ?>
			<div class="storefront__url">
				<a href="<?php echo esc_url( home_url() ); ?>"><?php echo esc_url( home_url() ); ?></a>
			</div>
			<?php endif; ?>
		</article>
	<?php endif; ?>

</div>

<div class="customer">
	<header><?php esc_html_e( 'To:', 'edd-eu-vat' ); ?></header>

	<article>
		<?php if ( edd_invoices_can_user_edit_invoice_data( get_current_user_id() ) ) : ?>
			<a href="<?php echo esc_url( edd_invoices_get_invoice_form_url( $order->ID ) ); ?>" class="button hide-on-print" data-html2canvas-ignore>
				<?php
				printf(
				/* Translators: %1$s - opening <span> tag; %2$s - closing </span> tag */
					__( 'Update %1$sbilling information%2$s', 'edd-eu-vat' ),
					'<span class="screen-reader-text">',
					'</span>'
				);
				?>
			</a>
		<?php endif; ?>

		<div class="address">
			<?php
			$address = edd_invoices_get_order_address( $order );
			if ( ! empty( $address['name'] ) ) {
				?>
				<div class="invoice-label"><?php echo esc_html( $address['name'] ); ?></div>
				<?php
			}
			$keys = [ 'line1', 'line2', 'city', 'zip', 'state', 'country' ];
			foreach ( $keys as $key ) {
				if ( ! empty( $address[ $key ] ) ) {
					echo esc_html( $address[ $key ] ) . '<br />';
				}
			}
			?>
		</div>
		<?php
		// Customer Tax/VAT #
		$vat = edd_invoices_get_custom_order_meta( $order, 'invoices_vat' );
		if ( $vat ) {
			?>
			<!-- Customer Tax/VAT # -->
			<div class="customer-vat">
				<span class="invoice-label"><?php esc_html_e( 'VAT number:', 'edd-eu-vat' ); ?></span> <?php echo esc_html( $vat ); ?>
			</div>
			<?php
		}
		?>
	</article>
</div>
