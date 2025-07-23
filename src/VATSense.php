<?php
namespace Barn2\Plugin\EDD_VAT;

use Exception;

/**
 * Handles Vatsense API interactions for VAT validation.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class VATSense {

	/**
	 * The API key for Vatsense API authentication
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * The URL endpoint for checking VAT numbers via Vatsense API
	 *
	 * @var string
	 */
	private $check_vat_number_url;

	/**
	 * Constructor. Initializes the Vatsense API client by loading configuration settings.
	 */
	public function __construct() {
		$this->load_configuration();
	}

	/**
	 * Get the API key for Vatsense API authentication
	 *
	 * @return string
	 */
	public function get_api_key() {
		return $this->api_key;
	}

	/**
	 * Get the VAT number checking endpoint URL
	 *
	 * @return string
	 */
	public function get_check_vat_number_url() {
		return $this->check_vat_number_url;
	}

	/**
	 * Set the API key for Vatsense API authentication
	 *
	 * @param string $api_key The API key to set
	 * @return void
	 */
	public function set_api_key( $api_key = null ) {
		if ( $api_key ) {
			$this->api_key = $api_key;
		} elseif ( defined( 'EUVAT_VATSENSE_API_KEY' ) ) {
			$this->api_key = EUVAT_VATSENSE_API_KEY;
		} else {
			$this->api_key = edd_get_option( 'edd_vat_vatsense_api_key' );
		}
	}

	/**
	 * Set the VAT number checking endpoint URL
	 *
	 * @return void
	 */
	public function set_check_vat_number_url() {
		$this->check_vat_number_url = 'https://api.vatsense.com/1.0/validate';
	}

	/**
	 * Load the configuration from the database
	 *
	 * @return void
	 */
	public function load_configuration() {
		$this->set_api_key();
		$this->set_check_vat_number_url();
	}

	/**
	 * Check a VAT number using Vatsense API.
	 *
	 * @param string $vat_number The VAT number to check
	 * @param string $requester_vat_number Optional. Your own VAT number to get a consultation number in the response
	 * @return array The API response
	 * @throws Exception If there is an error checking the VAT number.
	 */
	public function check_vat_number( $vat_number, $requester_vat_number = '' ) {
		if ( empty( $this->api_key ) ) {
			throw new Exception( 'Vatsense API key is not configured' );
		}

		$args = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( 'user:' . $this->api_key ), // phpcs:ignore
			],
			'timeout' => 30,
		];

		$query_args = [
			'vat_number' => $vat_number,
		];

		// Add requester VAT number if provided and it's a UK VAT number
		if ( ! empty( $requester_vat_number ) && strpos( $requester_vat_number, 'GB' ) === 0 ) {
			$query_args['requester_vat_number'] = $requester_vat_number;
		}

		// Vatsense API requires the country code to be included in the VAT number
		$url = add_query_arg( $query_args, $this->check_vat_number_url );

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Error checking VAT number: ' . esc_html( $response->get_error_message() ) );
		}

		return $response;
	}
}
