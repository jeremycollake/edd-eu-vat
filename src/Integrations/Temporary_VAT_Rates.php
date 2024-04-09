<?php
namespace Barn2\Plugin\EDD_VAT\Integrations;

use DateTimeImmutable;
use DateTimeZone;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Service;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Registerable;

/**
 * Temporary VAT rate adjustments.
 *
 * @package   Barn2\edd-eu-vat
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Temporary_VAT_Rates implements Service, Registerable {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		add_filter( 'edd_vat_current_eu_vat_rates', [ $this, 'adjust_rates' ], 5 );
	}

	/**
	 * Adjusts the EU VAT rates based on temporary vat rates set in 'get_temporary_rates'.
	 *
	 * @param array $vat_rates
	 * @return array $vat_rates
	 */
	public function adjust_rates( $vat_rates ) {
		foreach ( self::get_temporary_rates() as $country_code => $rate_info ) {
			if ( isset( $vat_rates[ $country_code ] ) ) {
				$timezone = new DateTimeZone( $rate_info['timezone'] );

				// We use the current time in the rate's timezone for comparison (not the EDD store time), as the temporary rate period will be relative to that country.
				$local_time = new DateTimeImmutable( 'now', $timezone );

				$rate_start_time = new DateTimeImmutable( $rate_info['from'], $timezone );
				$rate_end_time   = isset( $rate_info['to'] ) ? new DateTimeImmutable( $rate_info['to'], $timezone ) : null;

				if ( $local_time > $rate_start_time && ( ! $rate_end_time || $local_time < $rate_end_time ) ) {
					$vat_rates[ $country_code ] = $rate_info['rate'];
				}
			}
		}

		return $vat_rates;
	}

	/**
	 * Retrieves the temporary VAT rates
	 *
	 * @return array
	 */
	private static function get_temporary_rates() {
		return apply_filters(
			'edd_vat_temporary_eu_vat_rates',
			[
				'DE' => [
					'rate'     => 16.0,
					'timezone' => 'Europe/Berlin',
					'from'     => '2020-07-01',
					'to'       => '2021-01-01',
				],
				'IE' => [
					'rate'     => 21.0,
					'timezone' => 'Europe/Dublin',
					'from'     => '2020-09-01',
					'to'       => '2021-03-01',
				],
				'LU' => [
					'rate'     => 16.0,
					'timezone' => 'Europe/Luxembourg',
					'from'     => '2023-01-01',
					'to'       => '2023-12-31',
				],
			]
		);
	}
}
