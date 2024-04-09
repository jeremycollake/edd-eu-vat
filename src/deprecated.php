<?php

namespace Barn2\Plugin\EDD_VAT;

/**
 * @deprecated since 1.2.1 Renamed.
 */
class VAT_Checker {

	public static function check_vat( $vat_number, $country_code ) {
		_deprecated_function( __METHOD__, '1.2.1', 'Barn2\\Plugin\\EDD_VAT\\VAT_Checker_API::check_vat' );
		return VAT_Checker_API::check_vat( $vat_number, $country_code );
	}

}
