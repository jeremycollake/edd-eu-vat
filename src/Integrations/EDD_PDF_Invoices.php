<?php
namespace Barn2\Plugin\EDD_VAT\Integrations;

use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Registerable;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Service;
use Barn2\Plugin\EDD_VAT\Util;

/**
 * Integrates the plugin with the EDD PDF Invoices plugin.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class EDD_PDF_Invoices implements Registerable, Service {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		/**
		 * Register a callback which in turn adds a hook to the PDF Invoices template output.
		 * Adding it this way allows theme or plugins to use their own template callback.
		 *
		 * Note: this hook needs to fire before 'init' as PDF Invoices processes the 'generate_pdf_invoice' action on 'init' and kills execution afterwards.
		 */
		add_action( 'after_setup_theme', [ $this, 'register_template_action' ] );
	}

	/**
	 * Registers the EDD PDF Invoices template action
	 */
	public function register_template_action() {
		$template = $this->get_template();

		if ( apply_filters( 'edd_vat_register_pdf_template_callback', true, $template ) ) {
			add_action( "eddpdfi_pdf_template_{$template}", apply_filters( "edd_vat_pdf_template_callback_{$template}", [ $this, 'pdf_template' ] ), 11, 10 );
		}
	}

	/**
	 * PDF template callback.
	 *
	 * @param mixed $eddpdfi_pdf
	 * @param mixed $eddpdfi_payment
	 * @param mixed $eddpdfi_payment_meta
	 * @param mixed $eddpdfi_buyer_info
	 * @param mixed $eddpdfi_payment_gateway
	 * @param mixed $eddpdfi_payment_method
	 * @param mixed $address_line_2_line_height
	 * @param mixed $company_name
	 * @param mixed $eddpdfi_payment_date
	 * @param mixed $eddpdfi_payment_status
	 */
	public function pdf_template(
		$eddpdfi_pdf,
		$eddpdfi_payment,
		$eddpdfi_payment_meta,
		$eddpdfi_buyer_info,
		$eddpdfi_payment_gateway,
		$eddpdfi_payment_method,
		$address_line_2_line_height,
		$company_name,
		$eddpdfi_payment_date,
		$eddpdfi_payment_status
	) {
		global $edd_options;

		if ( ! Util::is_eu_payment( $eddpdfi_payment->ID ) ) {
			return;
		}

		// PDF Invoices sometimes passes a WP_Post object instead of EDD_Payment, in which case we need to convert.
		if ( $eddpdfi_payment instanceof \WP_Post ) {
			$eddpdfi_payment = edd_get_payment( $eddpdfi_payment );
		}

		// Get the payment VAT details.
		$payment_vat = Util::get_payment_vat( $eddpdfi_payment->ID );

		// Build lines array for PDF output.
		$lines = [];

		if ( ! empty( $payment_vat->vat_number ) ) {
			/* translators: %s: VAT number */
			$lines[] = sprintf( __( 'VAT number: %s', 'edd-eu-vat' ), esc_html( $payment_vat->vat_number ) );
		}

		if ( ! empty( $payment_vat->name ) ) {
			/* translators: %s: Company Name */
			$lines[] = sprintf( __( 'Company name: %s', 'edd-eu-vat' ), esc_html( $payment_vat->name ) );
		}

		if ( ! empty( $payment_vat->address ) ) {
			/* translators: %s: Company Address */
			$lines[] = sprintf( __( 'Address: %s', 'edd-eu-vat' ), esc_html( $payment_vat->address ) );
		}

		if ( $payment_vat->is_reverse_charged ) {
			$lines[] = '';
			$lines[] = __( 'VAT reverse charged', 'edd-eu-vat' );
		} elseif ( $eddpdfi_payment->tax > 0 ) {
			/* translators: %s is the VAT tax rate. */
			$lines[] = sprintf( __( 'VAT charged at %s%%', 'edd-eu-vat' ), $eddpdfi_payment->tax_rate * 100 );
		}

		$lines = apply_filters( 'edd_vat_pdf_invoices_vat_details', $lines, $payment_vat, $eddpdfi_payment );

		if ( $lines ) {
			$style = $this->get_style();

			$x             = $style['x'];
			$font          = $style['font'];
			$heading_color = $style['heading_color'];
			$text_color    = $style['text_color'];

			/*
			 * Cell params: width, height, text, border, next line, align, fill
			 * Multicell params: width, minimum height, text, border, align, fill, next line
			 *
			 * https://github.com/tecnickcom/TCPDF/blob/master/tcpdf.php
			 * http://www.fpdf.org/en/doc/cell.htm
			 * http://www.fpdf.org/en/doc/multicell.htm
			 */

			// Add spacing if there are addtional notes.
			if ( ! empty( $edd_options['eddpdfi_additional_notes'] ) ) {
				$eddpdfi_pdf->Ln( 8 );
			}

			// Add VAT heading.
			$eddpdfi_pdf->SetX( $x );
			$eddpdfi_pdf->SetFont( $font, '', 12 );
			$eddpdfi_pdf->SetTextColorArray( $heading_color );
			$eddpdfi_pdf->Cell( 0, 10, apply_filters( 'edd_vat_pdf_invoices_vat_heading', __( 'VAT Details', 'edd-eu-vat' ) ), 0, 1, 'L' );
			$eddpdfi_pdf->Ln( 0.5 );

			// Add VAT info.
			$text = implode( "\r\n", $lines );
			$eddpdfi_pdf->SetX( $x );
			$eddpdfi_pdf->SetTextColorArray( $text_color );
			$eddpdfi_pdf->SetFont( $font, '', 10 );
			$eddpdfi_pdf->setCellHeightRatio( 1.5 );
			$eddpdfi_pdf->MultiCell( 0, 10, $text, 0, 'L', false, 1 );
		}
	}

	/**
	 * Gets the style for the templates
	 *
	 * @return array
	 */
	private function get_style() {
		global $edd_options;

		// Default styles.
		$style = [
			'x'             => 30,
			'font'          => 'freesans',
			'heading_color' => [ 50, 50, 50 ],
			'text_color'    => [ 50, 50, 50 ],
		];

		$font_replace = isset( $edd_options['eddpdfi_enable_char_support'] );

		// Templates specific styles.
		$templates = [
			'default'     => [
				'x'             => 60,
				'font'          => $font_replace ? 'freesans' : 'Helvetica',
				'heading_color' => [ 50, 50, 50 ],
				'text_color'    => [ 50, 50, 50 ],
			],
			'blue_stripe' => [
				'x'             => 35,
				'font'          => $font_replace ? 'freeserif' : 'droidserif',
				'heading_color' => [ 149, 210, 236 ],
				'text_color'    => [ 50, 50, 50 ],
			],
			'lines'       => [
				'x'             => 35,
				'font'          => $font_replace ? 'freeserif' : 'droidserif',
				'heading_color' => [ 224, 65, 28 ],
				'text_color'    => [ 46, 11, 3 ],
			],
			'minimal'     => [
				'x'             => 21,
				'font'          => $font_replace ? 'freesans' : 'Helvetica',
				'heading_color' => [ 224, 65, 28 ],
				'text_color'    => [ 46, 11, 3 ],
			],
			'traditional' => [
				'x'             => 8,
				'font'          => $font_replace ? 'freeserif' : 'times',
				'heading_color' => [ 50, 50, 50 ],
				'text_color'    => [ 50, 50, 50 ],
			],
			'blue'        => [
				'x'             => 60,
				'font'          => $font_replace ? 'freesans' : 'opensans',
				'heading_color' => [ 8, 75, 110 ],
				'text_color'    => [ 7, 46, 66 ],
			],
			'green'       => [
				'x'             => 60,
				'font'          => $font_replace ? 'freesans' : 'opensans',
				'heading_color' => [ 8, 110, 39 ],
				'text_color'    => [ 7, 66, 28 ],
			],
			'orange'      => [
				'x'             => 60,
				'font'          => $font_replace ? 'freesans' : 'opensans',
				'heading_color' => [ 110, 54, 8 ],
				'text_color'    => [ 65, 66, 7 ],
			],
			'pink'        => [
				'x'             => 60,
				'font'          => $font_replace ? 'freesans' : 'opensans',
				'heading_color' => [ 110, 8, 82 ],
				'text_color'    => [ 66, 7, 51 ],
			],
			'purple'      => [
				'x'             => 60,
				'font'          => $font_replace ? 'freesans' : 'opensans',
				'heading_color' => [ 66, 8, 110 ],
				'text_color'    => [ 35, 7, 66 ],
			],
			'red'         => [
				'x'             => 60,
				'font'          => $font_replace ? 'freesans' : 'opensans',
				'heading_color' => [ 110, 8, 8 ],
				'text_color'    => [ 66, 7, 7 ],
			],
			'yellow'      => [
				'x'             => 60,
				'font'          => $font_replace ? 'freesans' : 'opensans',
				'heading_color' => [ 109, 110, 8 ],
				'text_color'    => [ 66, 38, 7 ],
			],
		];

		$template = $this->get_template();

		if ( isset( $templates[ $template ] ) ) {
			$style = $templates[ $template ];
		}

		return $style;
	}

	/**
	 * Retrieves the user selected template.
	 *
	 * @return string
	 */
	public function get_template() {
		global $edd_options;

		$template = 'default';

		if ( isset( $edd_options['eddpdfi_templates'] ) ) {
			$template = $edd_options['eddpdfi_templates'];
		}

		return apply_filters( 'edd_vat_pdf_invoice_template', $template );
	}
}
