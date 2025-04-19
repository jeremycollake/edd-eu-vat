<?php
namespace Barn2\Plugin\EDD_VAT;

use Exception;

/**
 * Handles HMRC API interactions.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class HMRC {

	/**
	 * The client ID for HMRC API authentication
	 *
	 * @var string
	 */
	private $client_id;

	/**
	 * The client secret for HMRC API authentication
	 *
	 * @var string
	 */
	private $client_secret;

	/**
	 * The OAuth2 token endpoint URL for HMRC API
	 *
	 * @var string
	 */
	private $oauth2_url;

	/**
	 * The OAuth2 grant type (typically 'client_credentials')
	 *
	 * @var string
	 */
	private $grant_type;

	/**
	 * The OAuth2 scope required for VAT checking (typically 'read:vat')
	 *
	 * @var string
	 */
	private $scope;

	/**
	 * The URL endpoint for checking VAT numbers via HMRC API
	 *
	 * @var string
	 */
	private $check_vat_number_url;

	/**
	 * Constructor. Initializes the HMRC API client by loading configuration settings.
	 */
	public function __construct() {
		$this->load_configuration();
	}

	/**
	 * Get the client ID for HMRC API authentication
	 *
	 * @return string
	 */
	public function get_client_id() {
		return $this->client_id;
	}

	/**
	 * Get the client secret for HMRC API authentication
	 *
	 * @return string
	 */
	public function get_client_secret() {
		return $this->client_secret;
	}

	/**
	 * Get the OAuth2 token endpoint URL
	 *
	 * @return string
	 */
	public function get_oauth2_url() {
		return $this->oauth2_url;
	}

	/**
	 * Get the OAuth2 grant type
	 *
	 * @return string
	 */
	public function get_grant_type() {
		return $this->grant_type;
	}

	/**
	 * Get the OAuth2 scope
	 *
	 * @return string
	 */
	public function get_scope() {
		return $this->scope;
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
	 * Set the client ID for HMRC API authentication
	 *
	 * @param string $client_id The client ID to set
	 * @return void
	 */
	public function set_client_id( $client_id = null ) {
		if ( $client_id ) {
			$this->client_id = $client_id;
		} else {
			$this->client_id = edd_get_option( 'edd_vat_hmrc_client_id' );
		}
	}

	/**
	 * Set the client secret for HMRC API authentication
	 *
	 * @param string $client_secret The client secret to set
	 * @return void
	 */
	public function set_client_secret( $client_secret = null ) {
		if ( $client_secret ) {
			$this->client_secret = $client_secret;
		} else {
			$this->client_secret = edd_get_option( 'edd_vat_hmrc_client_secret' );
		}
	}

	/**
	 * Set the OAuth2 token endpoint URL
	 *
	 * @return void
	 */
	public function set_oauth2_url() {
		$domain           = defined( 'EUVAT_HMRC_TEST' ) && EUVAT_HMRC_TEST === true ? 'test-api.service.hmrc.gov.uk' : 'api.service.hmrc.gov.uk';
		$this->oauth2_url = "https://{$domain}/oauth/token";
	}

	/**
	 * Set the OAuth2 grant type
	 *
	 * @return void
	 */
	public function set_grant_type() {
		/**
		 * Filters the OAuth2 grant type used for HMRC API authentication.
		 *
		 * @param string $grant_type The grant type. Default 'client_credentials'.
		 * @return string
		 */
		$this->grant_type = apply_filters( 'edd_vat_hmrc_grant_type', 'client_credentials' );
	}

	/**
	 * Set the OAuth2 scope
	 *
	 * @return void
	 */
	public function set_scope() {
		/**
		 * Filters the OAuth2 scope used for HMRC API authentication.
		 *
		 * @param string $scope The scope. Default 'read:vat'.
		 * @return string
		 */
		$this->scope = apply_filters( 'edd_vat_hmrc_scope', 'read:vat' );
	}

	/**
	 * Set the VAT number checking endpoint URL
	 *
	 * @return void
	 */
	public function set_check_vat_number_url() {
		$domain                     = defined( 'EUVAT_HMRC_TEST' ) && EUVAT_HMRC_TEST === true ? 'test-api.service.hmrc.gov.uk' : 'api.service.hmrc.gov.uk';
		$this->check_vat_number_url = "https://{$domain}/organisations/vat/check-vat-number/lookup";
	}

	/**
	 * Load the configuration from the database
	 *
	 * @return void
	 */
	public function load_configuration() {
		$this->set_client_id();
		$this->set_client_secret();
		$this->set_oauth2_url();
		$this->set_grant_type();
		$this->set_scope();
		$this->set_check_vat_number_url();
	}

	/**
	 * Get an access token from HMRC.
	 *
	 * @return string
	 * @throws Exception
	 */
	private function get_access_token() {
		$args = [
			'body'    => [
				'grant_type'    => $this->grant_type,
				'client_id'     => $this->client_id,
				'client_secret' => $this->client_secret,
				'scope'         => $this->scope,
			],
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
		];

		$response = wp_remote_post( $this->oauth2_url, $args );

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Error obtaining access token: ' . esc_html( $response->get_error_message() ) );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code !== 200 ) {
			throw new Exception( 'Error obtaining access token. Status code: ' . esc_html( $status_code ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $body['access_token'] ) ) {
			return $body['access_token'];
		}

		throw new Exception( 'Error obtaining access token: Invalid response format' );
	}

	/**
	 * Check a VAT number using HMRC's API.
	 *
	 * @param string $vat_number
	 * @return array
	 * @throws Exception If there is an error checking the VAT number.
	 */
	public function check_vat_number( $vat_number ) {
		$access_token = $this->get_access_token();

		$args = [
			'headers' => [
				'Accept'        => 'application/vnd.hmrc.2.0+json',
				'Authorization' => 'Bearer ' . $access_token,
			],
		];

		$response = wp_remote_get(
			"{$this->check_vat_number_url}/{$vat_number}",
			$args
		);

		return $response;
	}
}
