<?php

namespace Barn2\Plugin\EDD_VAT;

/**
 * Stores the result of a VAT number check.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class VAT_Check_Result {

	const NO_VAT_NUMBER                  = 1;
	const NO_COUNTRY_CODE                = 2;
	const INVALID_VAT_NUMBER             = 3;
	const INVALID_COUNTRY_CODE           = 4;
	const VAT_NUMBER_INVALID_FOR_COUNTRY = 5;
	const INVALID_INPUT                  = 6;
	const API_ERROR                      = 7;

	/**
	 * The VAT number.
	 *
	 * @var string
	 */
	public $vat_number = '';

	/**
	 * The two letter country code (e.g. US).
	 *
	 * @var string
	 */
	public $country_code = '';

	/**
	 * If the VAT number is valid.
	 *
	 * @var boolean true
	 */
	public $valid = false;

	/**
	 * The company name, if VAT number is valid.
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * The company address, if VAT number is valid.
	 *
	 * @var string
	 */
	public $address = '';

	/**
	 * Request Identifier parameter returned by the api
	 * if store VAT details are correct.
	 *
	 * @var string
	 */
	public $consultation_number = '';

	/**
	 * The error code if there was an error.
	 *
	 * @var string
	 */
	public $error;

	/**
	 * Constructs a new result object for the supplied VAT number and country code.
	 *
	 * @param string $vat_number The VAT number the result applies to.
	 * @param string $country_code The two letter country code.
	 */
	public function __construct( $vat_number, $country_code ) {
		$this->vat_number   = $vat_number;
		$this->country_code = $country_code;
	}

	/**
	 * Is the VAT number valid?
	 */
	public function is_valid() {
		return (bool) $this->valid;
	}

	/**
	 * Convert result to string.
	 */
	public function __toString() {
		$result = [ $this->name, $this->address, $this->error ];
		return apply_filters( 'edd_vat_check_result_to_string', implode( "\r\n", array_filter( $result ) ), $this );
	}

	/**
	 * Reset the status of the result.
	 *
	 * @return self
	 */
	public function reset() {
		$this->valid = false;
		$this->error = '';

		return $this;
	}

}
