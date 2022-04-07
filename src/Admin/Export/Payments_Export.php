<?php

namespace Barn2\Plugin\EDD_VAT\Admin\Export;

use Barn2\VAT_Lib\Registerable,
	Barn2\VAT_Lib\Service;

/**
 * Handles the VAT info on the CSV payments export.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class Payments_Export implements Registerable, Service {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		add_filter( 'edd_export_csv_cols_payments', [ $this, 'edd_export_csv_cols_payments' ] );
		add_filter( 'edd_export_get_data_payments', [ $this, 'edd_export_get_data_payments' ] );
	}

	/**
	 * Easy Digital Downloads export CSV columns payments.
	 *
	 * @param string[] $cols
	 * @return string[] $cols
	 */
	public function edd_export_csv_cols_payments( $cols ) {
		$cols['vat_number'] = __( 'VAT Number', 'edd-eu-vat' );

		return $cols;
	}

	/**
	 * Easy Digital Downloads export get data payments.
	 *
	 * @param array $data
	 * @return array $data
	 */
	public function edd_export_get_data_payments( $data ) {
		foreach ( $data as $i => $payment ) {
			if ( ! empty( $payment['id'] ) ) {
				$data[ $i ]['vat_number'] = edd_get_payment_meta( $payment['id'], '_edd_payment_vat_number', true );
			}
		}

		return $data;
	}

}
