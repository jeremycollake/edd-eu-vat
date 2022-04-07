<?php
namespace Barn2\Plugin\EDD_VAT;

use Barn2\Plugin\EDD_VAT\Company_VAT;

/**
 * Utility functions for the VAT plugin.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class Util {

	private static $eu_vat_rates;

	/**
	 * Retrieves an array of the EU VAT rates keyed by country code.
	 *
	 * @return array
	 */
	public static function get_eu_vat_rates() {
		if ( null === self::$eu_vat_rates ) {
			self::$eu_vat_rates = apply_filters(
				'edd_vat_current_eu_vat_rates',
				[
					'AT' => 20.0, // Austria
					'BE' => 21.0, // Belgium
					'BG' => 20.0, // Bulgaria
					'CY' => 19.0, // Cyprus
					'CZ' => 21.0, // Czech Republic
					'DE' => 19.0, // Germany
					'DK' => 25.0, // Denmark
					'EE' => 20.0, // Estonia
					'ES' => 21.0, // Spain
					'FI' => 24.0, // Finland
					'FR' => 20.0, // France
					'GB' => 20.0, // United Kingdom
					'GR' => 24.0, // Greece
					'HR' => 25.0, // Croatia
					'HU' => 27.0, // Hungary
					'IE' => 23.0, // Ireland
					'IT' => 22.0, // Italy
					'LT' => 21.0, // Lithuania
					'LU' => 17.0, // Luxembourg
					'LV' => 21.0, // Latvia
					'MT' => 18.0, // Malta
					'NL' => 21.0, // Netherlands
					'PL' => 23.0, // Poland
					'PT' => 23.0, // Portugal
					'RO' => 19.0, // Romania
					'SI' => 22.0, // Slovenia
					'SK' => 20.0, // Slovakia
					'SE' => 25.0, // Sweden
				]
			);
		}

		return self::$eu_vat_rates;
	}

	/**
	 * Retrieves an array of the EU country codes.
	 *
	 * @return string[]
	 */
	public static function get_eu_countries() {
		return array_keys( self::get_eu_vat_rates() );
	}

	/**
	 * Get a VAT rate for a specific country code.
	 *
	 * @param string $country
	 * @return float|int
	 */
	public static function get_vat_rate( $country ) {
		$vat_rates = self::get_eu_vat_rates();

		if ( array_key_exists( $country, $vat_rates ) ) {
			return (float) $vat_rates[ $country ];
		}

		return 0;
	}

	/**
	 * Retrieves an array of EU VAT number prefixes keyed by country code.
	 *
	 * @return string[]
	 */
	public static function get_eu_vat_number_prefixes() {
		$eu_countries    = self::get_eu_countries();
		$eu_vat_prefixes = array_combine( $eu_countries, $eu_countries );

		// Greek VAT numbers begin with EL, not GR.
		$eu_vat_prefixes['GR'] = 'EL';

		return $eu_vat_prefixes;
	}

	/**
	 * Checks whether reverse charge is supported in a specific country.
	 *
	 * @param string $country_code
	 * @return mixed
	 */
	public static function can_reverse_charge_vat( $country_code ) {
		$eu_countries = self::get_eu_countries();

		$reverse_charge_in_base_country = edd_get_option( 'edd_vat_reverse_charge_base_country', false ) || apply_filters( 'edd_vat_reverse_charge_vat_in_base_country', false );

		/**
		 * If we're applying VAT in the base country (i.e. not reverse charging), remove it from countries list.
		 *
		 * @link https://europa.eu/youreurope/business/taxation/vat/cross-border-vat/index_en.htm#withintheeusellgoodsanotherbusiness
		 */
		if ( ! $reverse_charge_in_base_country ) {
			$base_country = edd_get_option( 'edd_vat_address_country', edd_get_shop_country() );
			$eu_countries = array_diff( $eu_countries, [ $base_country ] );
		}

		return apply_filters( 'edd_vat_can_reverse_charge_vat', in_array( $country_code, $eu_countries, true ), $country_code );
	}

	/**
	 * Retrieves a Payment_VAT object
	 *
	 * @param mixed $payment_id
	 * @return Payment_VAT
	 */
	public static function get_payment_vat( $payment_id ) {
		$payment_vat = new Payment_VAT();

		$payment_vat->vat_number          = edd_get_payment_meta( $payment_id, '_edd_payment_vat_number', true );
		$payment_vat->name                = edd_get_payment_meta( $payment_id, '_edd_payment_vat_company_name', true );
		$payment_vat->address             = edd_get_payment_meta( $payment_id, '_edd_payment_vat_company_address', true );
		$payment_vat->is_vat_number_valid = (bool) edd_get_payment_meta( $payment_id, '_edd_payment_vat_number_valid', true );
		$payment_vat->is_eu_payment       = (bool) edd_get_payment_meta( $payment_id, '_edd_payment_vat_is_eu', true );
		$payment_vat->consultation_number = edd_get_payment_meta( $payment_id, '_edd_payment_vat_consultation_number', true );

		// Back-compat: old EDD VAT plugin used meta key '_edd_payment_vat_reversed_charged'
		$vat_reverse_charged = edd_get_payment_meta( $payment_id, '_edd_payment_vat_reversed_charged', true );

		if ( '' === $vat_reverse_charged ) {
			$vat_reverse_charged = edd_get_payment_meta( $payment_id, '_edd_payment_vat_reverse_charged', true );
		}
		$payment_vat->is_reverse_charged = (bool) $vat_reverse_charged;

		return $payment_vat;
	}

	/**
	 * Check if a given payment is from an EU country
	 *
	 * @param int $payment_id
	 * @return boolean
	 */
	public static function is_eu_payment( $payment_id ) {
		$is_eu = (bool) edd_get_payment_meta( $payment_id, '_edd_payment_vat_is_eu', true );

		// backwards compatiblity for pre plugin installation payments
		if ( ! $is_eu ) {
			$payment_meta = edd_get_payment_meta( $payment_id );
			$eu_countries = self::get_eu_countries();

			if ( isset( $payment_meta['user_info']['address']['country'] ) && in_array( $payment_meta['user_info']['address']['country'], $eu_countries, true ) ) {
				$is_eu = true;
			}
		}

		return $is_eu;
	}

	/**
	 * Fetches all settings related to Company VAT
	 *
	 * @return Company_VAT
	 */
	public static function get_company_vat() {
		$company_vat = new Company_VAT();

		$company_vat->vat_number    = edd_get_option( 'edd_vat_number' );
		$company_vat->uk_vat_number = edd_get_option( 'edd_uk_vat_number' );
		$company_vat->name          = edd_get_option( 'edd_vat_company_name' );

		$company_vat->address = [
			'line1'   => edd_get_option( 'edd_vat_address_line_1' ),
			'line2'   => edd_get_option( 'edd_vat_address_line_2' ),
			'city'    => edd_get_option( 'edd_vat_address_city' ),
			'zip'     => edd_get_option( 'edd_vat_address_code' ),
			'country' => edd_get_option( 'edd_vat_address_country' ),
		];

		$company_vat->formatted_address = self::format_edd_address( $company_vat->address );

		return $company_vat;
	}

	/**
	 * Formats and EDD address array for output
	 *
	 * @param array $address
	 * @return string
	 */
	public static function format_edd_address( $address ) {
		$defaults = [
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'state'   => '',
			'zip'     => '',
			'country' => ''
		];

		$address = wp_parse_args( $address, $defaults );

		$address_lines  = [ $address['line1'], $address['line2'] ];
		$city_zip_state = [ $address['city'], $address['zip'] ];

		if ( $address['country'] && $address['state'] ) {
			$city_zip_state[] = edd_get_state_name( $address['country'], $address['state'] );
		}
		$address_lines[] = implode( ' ', array_filter( $city_zip_state ) );

		if ( $address['country'] ) {
			$address_lines[] = edd_get_country_name( $address['country'] );
		}

		$formatted_address = implode( "\n", array_filter( $address_lines ) );
		$formatted_address = apply_filters( 'edd_vat_address_format', $formatted_address, $address );

		return $formatted_address;
	}

	/**
	 * Checks whether the EDD plugin is active.
	 *
	 * @return bool
	 */
	public static function is_edd_active() {
		return class_exists( '\Easy_Digital_Downloads' );
	}

}
