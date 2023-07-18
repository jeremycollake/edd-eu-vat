<?php

namespace Barn2\Plugin\EDD_VAT\Admin;

use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Registerable,
	Barn2\Plugin\EDD_VAT\Dependencies\Lib\Service,
	Barn2\Plugin\EDD_VAT\Util;

/**
 * Adds VAT based email merge tags for use in EDD Emails
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class Email_Tags implements Registerable, Service {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		// Register our VAT email tags
		add_filter( 'edd_email_tags', [ $this, 'register_tags' ] );
		add_filter( 'edd_email_preview_template_tags', [ $this, 'handle_preview' ] );
	}

	/**
	 * Registers the email tags
	 *
	 * @param  array $tags The email tags.
	 * @return array $tags
	 */
	public function register_tags( $tags ) {
		return array_merge(
			$tags,
			[
				[
					'tag'         => 'vat',
					'description' => __( 'The VAT rate and amount charged, falls back to {tax} if not an EU payment', 'edd-eu-vat' ),
					'function'    => [ $this, 'get_eu_vat' ],
				],
				[
					'tag'         => 'vat_number',
					'description' => __( 'The customer\'s VAT number if provided', 'edd-eu-vat' ),
					'function'    => [ $this, 'get_vat_number' ],
				],
				[
					'tag'         => 'company_name',
					'description' => __( 'Your company name', 'edd-eu-vat' ),
					'function'    => [ $this, 'get_company_name' ],
				],
				[
					'tag'         => 'company_vat_number',
					'description' => __( 'The registered VAT number for your company', 'edd-eu-vat' ),
					'function'    => [ $this, 'get_company_vat_number' ],
				],
				[
					'tag'         => 'company_address',
					'description' => __( 'The registered address for your company', 'edd-eu-vat' ),
					'function'    => [ $this, 'get_company_address' ],
				],
			]
		);
	}

	/**
	 * Handles preview of email tags
	 *
	 * @param string $message The email message
	 * @return string $message
	 */
	public function handle_preview( $message ) {
		$message = str_replace( '{vat}', '$0.00 [VAT reverse charged]', $message );
		$message = str_replace( '{vat_number}', 'DE123456789', $message );
		$message = str_replace( '{company_name}', $this->get_company_name(), $message );
		$message = str_replace( '{company_vat_number}', $this->get_company_vat_number(), $message );
		$message = str_replace( '{company_address}', $this->get_company_address(), $message );

		return $message;
	}

	/**
	 * Get the EU VAT if applicable
	 *
	 * @param int $payment_id
	 * @return string
	 */
	public function get_eu_vat( $payment_id ) {
		$tax = edd_currency_filter( edd_format_amount( edd_get_payment_tax( $payment_id ) ) );

		if ( ! Util::is_eu_payment( $payment_id ) ) {
			html_entity_decode( $tax, ENT_COMPAT, 'UTF-8' );
		}

		$payment             = new \EDD_Payment( $payment_id );
		$vat_reverse_charged = $payment->get_meta( '_edd_payment_vat_reverse_charged' );

		if ( $vat_reverse_charged ) {
			$tax .= sprintf( ' [%s]', __( 'VAT reverse charged', 'edd-eu-vat' ) );
		} elseif ( $payment->tax_rate ) {
			$tax .= sprintf( ' (%s%%)', $payment->tax_rate * 100 );
		}

		return html_entity_decode( $tax, ENT_COMPAT, 'UTF-8' );
	}

	/**
	 * Get customer VAT Number
	 *
	 * @param int $payment_id
	 * @return string
	 */
	public function get_vat_number( $payment_id ) {
		$payment = new \EDD_Payment( $payment_id );
		return $payment->get_meta( '_edd_payment_vat_number' );
	}

	/**
	 * Get company name.
	 *
	 * @return string
	 */
	public function get_company_name() {
		return edd_get_option( 'edd_vat_company_name' );
	}

	/**
	 * Get company VAT Numbers.
	 *
	 * @return string
	 */
	public function get_company_vat_number() {
		$vat_numbers = array_filter(
			[
				edd_get_option( 'edd_vat_number' ),
				edd_get_option( 'edd_uk_vat_number' )
			]
		);

		return implode( ' / ', $vat_numbers );
	}

	/**
	 * Get company address.
	 *
	 * @return string $output
	 */
	public function get_company_address() {
		$address = [
			'line_1'  => edd_get_option( 'edd_vat_address_line_1' ),
			'line_2'  => edd_get_option( 'edd_vat_address_line_2' ),
			'city'    => edd_get_option( 'edd_vat_address_city' ),
			'zip'     => edd_get_option( 'edd_vat_address_code' ),
			'country' => Util::get_country_for_address(),
		];

		$output = Util::format_edd_address( $address );

		return $output;
	}

}
