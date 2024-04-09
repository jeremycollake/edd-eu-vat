<?php

use Barn2\Plugin\EDD_VAT\Util;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * Handles VAT Payments batch export.
 *
 * We don't namespace this class as there is an encoding issue with \
 * when EDD creates the download URL response and passes back the class name
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class Batch_VAT_EC_Sales_Payments_Export extends \EDD_Batch_Export {
	// phpcs:enable

	/**
	 * Export type key
	 *
	 * @var string
	 */
	public $export_type = 'eu_vat_ec_sales';

	/**
	 * Steps per batch
	 *
	 * @var integer
	 */
	protected $step_count = 30;

	/**
	 * Set the CSV columns
	 *
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = [
			'country'    => __( 'EU Country Code', 'edd-eu-vat' ),
			'vat_number' => __( 'VAT Registration Number', 'edd-eu-vat' ),
			'amount'     => __( 'Value of Supplies', 'edd-eu-vat' ),
			'indicator'  => __( 'Indicator', 'edd-eu-vat' ),
		];

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @return array $data The step data for the CSV file
	 */
	public function get_data() {
		$data = [];

		$args = [
			'number' => $this->step_count,
			'page'   => $this->step,
			'status' => [ 'publish', 'complete', 'edd_subscription' ],
		];

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = [
				[
					'after'     => gmdate( 'Y-n-d 00:00:00', strtotime( $this->start ) ),
					'before'    => gmdate( 'Y-n-d 23:59:59', strtotime( $this->end ) ),
					'inclusive' => true
				]
			];
		}

		$payments = edd_get_payments( $args );

		$eu_countries = Util::get_eu_countries();
		$base_country = Util::get_country_for_address();
		$eu_countries = array_diff( $eu_countries, [ $base_country ] );

		if ( $payments ) {
			foreach ( $payments as $payment ) {
				$payment = new \EDD_Payment( $payment->ID );

				if ( ! isset( $payment->address['country'] ) ) {
					continue;
				}

				// Don't include sales for countries outside the EU.
				if ( ! in_array( $payment->address['country'], $eu_countries, true ) ) {
					continue;
				}

				// Don't include sales which have tax > 0 (i.e. those which are not reverse charged).
				if ( ! empty( (float) $payment->tax ) && (float) $payment->tax > 0 ) {
					continue;
				}

				// Don't include sale if there's no VAT number (EC Sales List records sales to VAT-registered businesses only).
				$tax_number = edd_get_payment_meta( $payment->ID, '_edd_payment_vat_number', true );
				if ( empty( $tax_number ) ) {
					continue;
				}

				$data[] = [
					'country'    => $payment->address['country'],
					'vat_number' => apply_filters( 'edd_vat_export_vat_ec_sales_vat_number', str_replace( $payment->address['country'], '', $tax_number ), $payment->ID, $payment ),
					'amount'     => $this->round_payment_amount( $payment ),
					'indicator'  => 3,
				];
			}

			return $data;
		}

		return false;
	}

	/**
	 * Print the CSV rows for the current step
	 *
	 * @return string|false
	 */
	public function print_csv_rows() {
		$step_data   = $this->get_data();
		$total_steps = $this->get_total_steps();
		$cache_key   = 'edd_eu_vat_ec_sales_export_' . $this->export_key;

		if( ! $total_steps ) {
			return false;
		}

		// handle our batch steps
		if ( $this->step === $total_steps ) {
			// FiNAL OR ONLY STEP: Do a final totals addition if necessary, and then print the CSV rows.
			$row_data = '';
			$cols     = $this->get_csv_cols();

			$final_data = ( $total_steps === 1 ) ? $step_data : $this->add_step_data_to_cache( $step_data, $cache_key );

			ksort( $final_data );
			$data = [];

			foreach ( $final_data as $row ) {
				$data[] = [
					'country'    => $row['country'],
					'vat_number' => $row['vat_number'],
					'amount'     => $row['amount'],
					'indicator'  => $row['indicator'],
				];
			}

			// Build the CSV rows
			foreach ( $data as $row ) {
				$i = 1;
				foreach ( $row as $col_id => $column ) {
					// Make sure the column is valid
					if ( array_key_exists( $col_id, $cols ) ) {
						$row_data .= '"' . addslashes( preg_replace( '/\"/', "'", $column ) ) . '"';
						$row_data .= $i === count( $cols ) ? '' : ',';
						$i ++;
					}
				}
				$row_data .= "\r\n";
			}

			$this->stash_step_data( $row_data );

			// Remove our temp cache
			delete_option( $cache_key );

			return false;
		} elseif ( $this->step < 2 ) {
			// FIRST STEP (of many): Setup temp cache and store data
			update_option( $cache_key, $step_data );

			return true;
		} else {
			// INTERMEDIATE: STEP just cache the totals
			$this->add_step_data_to_cache( $step_data, $cache_key );

			return true;
		}

		return false;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @return int
	 */
	public function get_percentage_complete() {
		$args = [
			'start-date' => $this->start ? gmdate( 'n/d/Y', strtotime( $this->start ) ) : null,
			'end-date'   => $this->end ? gmdate( 'n/d/Y', strtotime( $this->end ) ) : null,
		];

		$all_payments_count = edd_count_payments( $args );

		$total      = $all_payments_count->edd_subscription + $all_payments_count->publish + $all_payments_count->complete;
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->step_count * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->start      = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end        = isset( $request['end'] ) ? sanitize_text_field( $request['end'] ) : '';
		$this->export_key = isset( $request['export-key'] ) ? sanitize_text_field( $request['export-key'] ) : '';
	}

	/**
	 * Helper function to choose how to round payments
	 *
	 * @param \EDD_Payment $payment
	 * @return int
	 */
	private function round_payment_amount( $payment ) {
		$mode = apply_filters( 'edd_vat_export_vat_ec_sales_amount_rounding', 'default' );

		$mode_function_map = [
			'round_up'   => 'ceil',
			'round_down' => 'floor',
			'default'    => 'round',
		];

		if ( ! in_array( $mode, array_keys( $mode_function_map ), true ) ) {
			$mode = 'default';
		}

		return call_user_func( $mode_function_map[ $mode ], apply_filters( 'edd_vat_export_vat_ec_sales_amount', $payment->total, $payment->ID, $payment ) );
	}

	/**
	 * Adds step data to a temporary wp_option for caching
	 *
	 * @param array $step_data
	 * @param string $cache_key
	 *
	 * @return array $saved_data
	 */
	private function add_step_data_to_cache( $step_data, $cache_key ) {
		$saved_data = get_option( $cache_key );

		foreach ( $step_data as $data ) {
			$saved_data[] = [
				'country'    => $data['country'],
				'amount'     => $data['amount'],
				'vat_number' => $data['vat_number'],
				'indicator'  => $data['indicator'],
			];
		}

		update_option( $cache_key, $saved_data );

		return $saved_data;
	}

	/**
	 * Gets the total step count
	 *
	 * @return int $steps
	 */
	private function get_total_steps() {
		$args = [
			'start-date' => $this->start ? gmdate( 'n/d/Y', strtotime( $this->start ) ) : null,
			'end-date'   => $this->end ? gmdate( 'n/d/Y', strtotime( $this->end ) ) : null,
		];

		$all_payments_count = edd_count_payments( $args );

		$total = $all_payments_count->edd_subscription + $all_payments_count->publish + $all_payments_count->complete;

		if ( $total < 0 ) {
			return 0;
		}

		$steps = ceil( $total / $this->step_count );

		return (int) $steps;
	}

}
