<?php
namespace Barn2\Plugin\EDD_VAT;

use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Registerable;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Service;
use Barn2\Plugin\EDD_VAT\Util;
use EDD_Payment;
use WP_Post;

/**
 * Adds VAT information to the purchase receipt [edd_receipt] shortcode.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class Purchase_Receipt implements Registerable, Service {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		add_action( 'edd_payment_receipt_after', [ $this, 'add_vat_note' ], 999, 1 );
		add_filter( 'do_shortcode_tag', [ $this, 'add_vat_addresses' ], 10, 2 );
		add_filter( 'edd_vat_invoice_address_country_code', [ $this, 'maybe_parse_country_code' ], 10 );
	}

	/**
	 * When the "EU" option is selected under the "Country" dropdown
	 * in Downloads → Settings → Extensions → EU VAT - fallback
	 * to the EDD Store country code setting.
	 *
	 * The "EU" country code is a special code used to validate VAT
	 * details from the VIES api, however when using this code
	 * some address details (like invoices) will display "EU Moss Number" as country.
	 *
	 * For this reason we fallback to the store's country code.
	 *
	 * @param string $code
	 * @return string
	 */
	public function maybe_parse_country_code( $code ) {

		if ( $code === 'EU' ) {
			$code = edd_get_shop_country();
		}

		return $code;
	}

	/**
	 * Add VAT note right after table
	 *
	 * @param WP_Post $payment
	 */
	public function add_vat_note( $payment ) {
		if ( ! apply_filters( 'edd_vat_purchase_receipt_display_vat_rate', true ) ) {
			return;
		}

		if ( ! Util::is_eu_payment( $payment->ID ) ) {
			return;
		}

		$vat_note = '';

		// Convert WP_Post to EDD_Payment.
		$edd_payment        = new EDD_Payment( $payment->ID );
		$is_reverse_charged = $edd_payment->get_meta( '_edd_payment_vat_reverse_charged' );

		if ( $is_reverse_charged ) {
			$vat_note = __( 'VAT reverse charged', 'edd-eu-vat' );
		} elseif ( $edd_payment->tax > 0 ) {
			$vat_note = sprintf(
				/* translators: %s is the VAT tax rate */
				__( 'VAT charged at %s%%', 'edd-eu-vat' ),
				$edd_payment->tax_rate * 100
			);
		}
		if ( ! $vat_note ) {
			return;
		}
		?>
		<tr>
			<td colspan="2"><em><?php echo esc_html( $vat_note ); ?></em></td>
		</tr>
		<?php
	}

	/**
	 * Filter the shortcode output to add the addresses at the end
	 *
	 * @param string $output
	 * @param string $tag
	 *
	 * @return string
	 */
	public function add_vat_addresses( $output, $tag ) {
		global $edd_receipt_args;

		if ( ! edd_get_option( 'edd_vat_purchase_receipt' ) ) {
			return $output;
		}

		if ( $tag !== 'edd_receipt' ) {
			return $output;
		}

		// Get payment key
		$session = edd_get_purchase_session();

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// reason: This follows EDD core standards and the user is authenticated later via 'edd_can_view_receipt'
		if ( isset( $_GET['payment_key'] ) ) {
			$payment_key = urldecode( $_GET['payment_key'] );
		} elseif ( $session ) {
			$payment_key = $session['purchase_key'];
		} elseif ( $edd_receipt_args['payment_key'] ) {
			$payment_key = $edd_receipt_args['payment_key'];
		} elseif ( isset( $edd_receipt_args['id'] ) ) {
			$payment_key = edd_get_payment_key( $edd_receipt_args['id'] );
		}
		// phpcs:enable

		// No key found
		if ( ! isset( $payment_key ) ) {
			return $output;
		}

		$user_can_view = edd_can_view_receipt( $payment_key );

		if ( ! $user_can_view || empty( $edd_receipt_args['id'] ) ) {
			return $output;
		}

		// Setup \EDD_Payment
		$payment = new EDD_Payment( $edd_receipt_args['id'] );

		if ( ! $payment ) {
			return $output;
		}

		$billed_to_lines     = [];
		$billed_to_vat_lines = [];

		// Get customer data for receipt.
		$payment_vat      = Util::get_payment_vat( $payment->ID );
		$customer_name    = trim( $payment->first_name . ' ' . $payment->last_name );
		$customer_address = Util::format_edd_address( $payment->address );

		if ( $customer_name ) {
			$billed_to_lines[] = $customer_name;
		}

		if ( $customer_address ) {
			$billed_to_lines[] = nl2br( $customer_address );
		}

		if ( ! empty( $payment_vat->vat_number ) ) {
			/* translators: %s is the VAT number */
			$billed_to_vat_lines[] = sprintf( __( 'VAT number: %s', 'edd-eu-vat' ), esc_html( $payment_vat->vat_number ) );

			if ( ! empty( $payment_vat->name ) ) {
				/* translators: %s is the registered company name */
				$billed_to_vat_lines[] = sprintf( __( 'Company name: %s', 'edd-eu-vat' ), esc_html( $payment_vat->name ) );
			}
			if ( ! empty( $payment_vat->address ) ) {
				/* translators: %s is the registered company address */
				$billed_to_vat_lines[] = sprintf( __( 'Address: %s', 'edd-eu-vat' ), esc_html( $payment_vat->address ) );
			}
		}

		// Build customer details HTML.
		ob_start();

		if ( $billed_to_lines ) {
			?>
			<div class="edd-eu-vat-receipt-customer-details edd-eu-vat-receipt-column edd_eu_vat-customer-details">
				<h3><?php echo esc_html( apply_filters( 'edd_vat_purchase_receipt_bill_to_heading', __( 'Billed To', 'edd-eu-vat' ) ) ); ?></h3>
				<p class="edd-eu-vat-receipt-customer-name-address">
					<?php echo wp_kses( implode( '<br/>', $billed_to_lines ), [ 'br' => [] ] ); ?></p>

					<?php if ( $billed_to_vat_lines ) : ?>
						<p class="edd-eu-vat-receipt-customer-vat">
							<strong><?php esc_html_e( 'VAT Details', 'edd-eu-vat' ); ?></strong><br/>
							<?php echo wp_kses( implode( '<br/>', $billed_to_vat_lines ), [ 'br' => [] ] ); ?>
						</p>
					<?php endif; ?>
			</div>
			<?php
		}

		$customer_details = ob_get_contents();
		ob_clean();

		// Get store data for receipt.
		$sold_by_lines = [];
		$company_vat   = Util::get_company_vat();

		if ( ! empty( $company_vat->name ) ) {
			$sold_by_lines[] = $company_vat->name;
		}

		if ( ! empty( $company_vat->formatted_address ) ) {
			$sold_by_lines[] = nl2br( $company_vat->formatted_address );
		}

		if ( ! empty( $company_vat->vat_number ) ) {
			/* translators: %s is the VAT number */
			$sold_by_lines[] = sprintf( __( 'EU VAT number: %s', 'edd-eu-vat' ), $company_vat->vat_number );
		}

		if ( ! empty( $company_vat->uk_vat_number ) ) {
			/* translators: %s is the VAT number */
			$sold_by_lines[] = sprintf( __( 'UK VAT number: %s', 'edd-eu-vat' ), $company_vat->uk_vat_number );
		}

		// Build store details HTML.
		if ( $sold_by_lines ) {
			?>
			<div class="edd-eu-vat-receipt-store-details edd-eu-vat-receipt-column edd_eu_vat-store-details">
				<h3><?php echo esc_html( apply_filters( 'edd_vat_purchase_receipt_sold_by_heading', __( 'Sold By', 'edd-eu-vat' ) ) ); ?></h3>
				<p><?php echo wp_kses( implode( '<br/>', $sold_by_lines ), [ 'br' => [] ] ); ?></p>
			</div>
			<?php
		}

		$store_details = ob_get_contents();
		ob_end_clean();

		// Allow filtering of the address html outputs
		$customer_details = apply_filters( 'edd_vat_purchase_receipt_customer', $customer_details, $customer_name, $customer_address, $payment_vat->vat_number );
		$store_details    = apply_filters( 'edd_vat_purchase_receipt_company', $store_details, $company_vat->name, $company_vat->formatted_address, $company_vat->vat_number );

		$receipt_invoice_html = '<div class="edd-eu-vat-receipt-container edd_eu_vat-address-container">' . $customer_details . $store_details . '</div>';

		// Enqueue purchase receipt styles
		wp_enqueue_style( 'edd-eu-vat-receipt' );

		return $output . $receipt_invoice_html;
	}
}
