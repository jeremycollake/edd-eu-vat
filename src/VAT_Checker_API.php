<?php
namespace Barn2\Plugin\EDD_VAT;

use Barn2\Plugin\EDD_VAT\Util;

/**
 * Handles calls to the VIES VAT API to fetch company VAT information.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class VAT_Checker_API {

	/**
	 * API URL - must be http as VIES doesn't work over https! (Seems it's now supported)
	 */
	const API_URL = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

	/**
	 * HMRC API URL
	 */
	const HMRC_URL = 'https://api.service.hmrc.gov.uk/organisations/vat/check-vat-number/lookup';

	/**
	 * Check a VAT number against the supplied country code.
	 *
	 * @param   string $vat_number The VAT number to check.
	 * @param   string $country_code The country code.
	 * @return  VAT_Check_Result
	 */
	public static function check_vat( $vat_number, $country_code ) {
		$result = new VAT_Check_Result( $vat_number, $country_code );

		if ( ! $vat_number ) {
			$result->error = VAT_Check_Result::NO_VAT_NUMBER;
			return $result;
		}

		if ( ! $country_code ) {
			$result->error = VAT_Check_Result::NO_COUNTRY_CODE;
			return $result;
		}

		// Check country is in the EU.
		if ( ! in_array( $country_code, Util::get_eu_countries(), true ) ) {
			$result->error = VAT_Check_Result::INVALID_COUNTRY_CODE;
			return $result;
		}

		// Sanitize VAT number (remove white space, etc).
		$vat_number = str_replace( [ ' ', '.', '-' ], '', strtoupper( $vat_number ) );

		// Check prefix.
		$eu_vat_prefixes        = Util::get_eu_vat_number_prefixes();
		$vat_prefix_for_country = $eu_vat_prefixes[ $country_code ];
		$vat_number_prefix      = substr( $vat_number, 0, 2 );

		// If prefix is a valid VAT prefix but doesn't match selected country, return an error.
		if ( in_array( $vat_number_prefix, $eu_vat_prefixes, true ) && $vat_prefix_for_country !== $vat_number_prefix ) {
			$result->error = VAT_Check_Result::VAT_NUMBER_INVALID_FOR_COUNTRY;
			return $result;
		}

		// Strip country code if VAT number starts with it.
		if ( $vat_prefix_for_country === $vat_number_prefix ) {
			$vat_number = substr( $vat_number, 2 );
		}

		// Use HMRC for UK, otherwise VIES for EU
		if ( $country_code === 'GB' ) {
			$result = self::hmrc_request( $result, $vat_number );
		} else {
			$result = self::vies_request(
				$result,
				$vat_number,
				$country_code,
				$vat_prefix_for_country
			);
		}

		// Catch all for invalid VAT number if no error set.
		if ( ! $result->is_valid() && empty( $result->error ) ) {
			$result->error = VAT_Check_Result::INVALID_VAT_NUMBER;
		}

		return apply_filters( 'edd_vat_number_check', $result, $vat_number, $country_code );
	}

	/**
	 * Makes a request to the VIES API
	 *
	 * @param VAT_Check_Result $result
	 * @param string $vat_number
	 * @param string $country_code
	 * @param string $vat_prefix_for_country
	 * @param string $requester_country_code
	 * @param string $requester_vat_number
	 * @return VAT_Check_Result
	 */
	private static function vies_request( $result, $vat_number, $country_code, $vat_prefix_for_country, $bypass = false ) {
		try {
			// Create the SOAP client for VIES API call.
			$client = new \SoapClient( self::API_URL );

			// API parameters.
			$parameters = [
				'countryCode' => $vat_prefix_for_country,
				'vatNumber'   => $vat_number,
			];

			$requester_country_code = self::get_requester_country_code();
			$requester_vat_number   = self::get_requester_vat_number();

			// Reset parameters if bypassed so that it doesn't block checkout.
			if ( $bypass ) {
				$requester_country_code = false;
				$requester_vat_number   = false;
			}

			if ( ! empty( $requester_country_code ) && ! empty( $requester_vat_number ) ) {
				$parameters['requesterCountryCode'] = $requester_country_code;
				$parameters['requesterVatNumber']   = $requester_vat_number;
				$response                           = $client->checkVatApprox( $parameters );
			} else {
				$response = $client->checkVat( $parameters );
			}

			$result->valid = filter_var( $response->valid, FILTER_VALIDATE_BOOLEAN );

			// 3 dashes are returned in name and address field if VAT number is invalid.
			if ( isset( $response->name ) && '---' !== $response->name ) {
				$result->name = $response->name;
			} elseif ( isset( $response->traderName ) && '---' !== $response->traderName ) {
				$result->name = $response->traderName;
			}

			$address = [];

			if ( isset( $response->address ) && '---' !== $response->address ) {
				$address   = explode( "\n", $response->address );
				$address[] = $country_code;
			} elseif ( isset( $response->traderAddress ) && '---' !== $response->traderAddress ) {
				$address   = explode( "\n", $response->traderAddress );
				$address[] = $country_code;
			}

			$result->address = implode( apply_filters( 'edd_vat_check_result_address_separator', ', ' ), array_filter( $address ) );

			if ( isset( $response->requestIdentifier ) ) {
				$result->consultation_number = $response->requestIdentifier;
			}
		} catch ( \Exception $exception ) {
			if ( $exception->getMessage() === 'INVALID_REQUESTER_INFO' ) {
				return self::vies_request( $result, $vat_number, $country_code, $vat_prefix_for_country, true );
			}

			// Handle error.
			$result->error = $exception->getMessage();
		}

		// Translate common errors.
		if ( 'MS_UNAVAILABLE' === $result->error ) {
			$result->error = VAT_Check_Result::API_ERROR;
		} elseif ( 'INVALID_INPUT' === $result->error ) {
			$result->error = VAT_Check_Result::INVALID_INPUT;
		} elseif ( 'MS_MAX_CONCURRENT_REQ' === $result->error ) {
			$result->error = VAT_Check_Result::MS_MAX_CONCURRENT_REQ;
		}

		return $result;
	}

	/**
	 * Get the store's VAT country code.
	 *
	 * @return string
	 */
	private static function get_requester_country_code() {
		return edd_get_option( 'edd_vat_address_country', '' );
	}

	/**
	 * Get the store's VAT number without the country code.
	 *
	 * @return string
	 */
	private static function get_requester_vat_number() {
		$vat_number             = edd_get_option( 'edd_vat_number', '' );
		$vat_number_prefix      = substr( $vat_number, 0, 2 );
		$vat_prefix_for_country = self::get_requester_country_code();

		if ( $vat_prefix_for_country === $vat_number_prefix ) {
			$vat_number = substr( $vat_number, 2 );
		}

		return $vat_number;
	}

	/**
	 * Makes a request to the HMRC API
	 *
	 * @param VAT_Check_Result $result
	 * @param string $vat_number
	 * @return VAT_Check_Result
	 */
	private static function hmrc_request( $result, $vat_number ) {
		$url = sprintf( '%1$s/%2$s/', self::HMRC_URL, $vat_number );

		$request_args = [
			'headers'    => [
				'Accept'       => 'application/json',
				'Content-type' => 'application/json',
			],
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36',
			// HMRC API doesn't like certain user agents so we force this
		];

		$request = wp_remote_get( $url, $request_args );

		if ( is_wp_error( $request ) ) {
			$result->error = VAT_Check_Result::API_ERROR;
			return $result;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $request ), true );
		$response_code = wp_remote_retrieve_response_code( $request );

		// We have a success with valid structure
		if ( $response_code === 200 && isset( $response_body['target']['vatNumber'] ) ) {
			$result->valid = true;

			if ( isset( $response_body['target']['name'] ) && ! empty( $response_body['target']['name'] ) ) {
				$result->name = $response_body['target']['name'];
			}

			if ( isset( $response_body['target']['address'] ) && ! empty( $response_body['target']['address'] ) ) {
				$result->address = implode( apply_filters( 'edd_vat_check_result_address_separator', ', ' ), array_filter( $response_body['target']['address'] ) );
			}
		}

		// HMRC API can return 404 or 400 response code for an invalid VAT number
		if ( $response_code === 400 && isset( $response_body['code'] ) && $response_body['code'] === 'INVALID_REQUEST' ) {
			// 'INVALID_REQUEST' here represents an invalid VAT number as opposed to 'BAD_REQUEST' for an actual bad request
			$result->error = VAT_Check_Result::INVALID_VAT_NUMBER;
		} elseif ( $response_code === 404 && isset( $response_body['code'] ) && $response_body['code'] === 'NOT_FOUND' ) {
			// 'NOT_FOUND' here represents an invalid VAT number as opposed to 'MATCHING_RESOURCE_NOT_FOUND' for an actual 404
			$result->error = VAT_Check_Result::INVALID_VAT_NUMBER;
		} elseif ( in_array( $response_code, [ 400, 401, 403, 404, 406, 429, 500, 501, 503, 504 ], true ) ) {
			// Handle other API errors
			$result->error = VAT_Check_Result::API_ERROR;
		}

		return $result;
	}
}
