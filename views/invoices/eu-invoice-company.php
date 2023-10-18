<?php
/**
 * Invoice Company Template
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
$vat_numbers = Util::get_company_vat_numbers();

?>

<div class="storefront">
	<header>
		<?php esc_html_e( 'Invoice From:', 'edd-eu-vat' ); ?>
	</header>

	<article>
		<div class="address">
			<?php
			// Company Name
			if ( $company->name ) {
				?>
				<div class="invoice-label"><?php echo esc_html( $company->name ); ?></div>
				<?php
			}

			// Address + Company Details
			echo wp_kses_post( nl2br( $company->formatted_address ) ); ?>
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
		if ( $vat_numbers ) {
			?>
			<!-- Vendor Tax/VAT # -->
			<div class="storefront__vat">
				<span class="invoice-label"><?php esc_html_e( 'Tax/VAT:', 'edd-eu-vat' ); ?></span> <?php echo esc_html( $vat_numbers ); ?>
			</div>
			<?php
		}

		/**
		 * Fires at the end of the company details.
		 *
		 * @since 1.3.2
		 * @param \EDD\Orders\Order|EDD_Payment $order The order/payment object.
		 */
		do_action( 'edd_invoices_after_company_details', $order );
		?>
	</article>
</div>
