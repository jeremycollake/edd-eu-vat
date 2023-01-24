<?php

namespace Barn2\Plugin\EDD_VAT\Admin\Wizard\Steps;

use Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Api;
use Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Step;

/**
 * General Step.
 *
 * @package   Barn2/edd-eu-vat
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Company_Information extends Step {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->set_id( 'company-info' );
		$this->set_name( esc_html__( 'Company Information', 'edd-eu-vat' ) );
		$this->set_description( esc_html__( ' Enter your company details which will be displayed on the order details page and purchase receipt email.', 'edd-eu-vat' ) );
		$this->set_title( esc_html__( 'Company Information', 'edd-eu-vat' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_fields() {

		$fields = [
			'edd_vat_company_name'    => [
				'value'       => edd_get_option( 'edd_vat_company_name', '' ),
				'label'       => __( 'Company Name', 'edd-eu-vat' ),
				'description' => __( 'Enter your company name.', 'edd-eu-vat' ),
				'type'        => 'text',
			],
			'edd_vat_number'          => [
				'value'       => edd_get_option( 'edd_vat_number', '' ),
				'label'       => __( 'EU VAT Number', 'edd-eu-vat' ),
				'description' => __( 'Enter the registered EU VAT number of your company.', 'edd-eu-vat' ),
				'type'        => 'text',
			],
			'edd_uk_vat_number'       => [
				'value'       => edd_get_option( 'edd_uk_vat_number', '' ),
				'label'       => __( 'UK VAT Number', 'edd-eu-vat' ),
				'description' => __( 'Enter the registered UK VAT number of your company.', 'edd-eu-vat' ),
				'type'        => 'text',
			],
			'edd_vat_address_line_1'  => [
				'value'       => edd_get_option( 'edd_vat_address_line_1', '' ),
				'label'       => __( 'Address Line 1', 'edd-eu-vat' ),
				'description' => __( 'Enter line 1 of your company\'s registered VAT address.', 'edd-eu-vat' ),
				'type'        => 'text',
			],
			'edd_vat_address_line_2'  => [
				'value'       => edd_get_option( 'edd_vat_address_line_2', '' ),
				'label'       => __( 'Address Line 2', 'edd-eu-vat' ),
				'description' => __( 'Enter line 2 of your company\'s registered VAT address if required.', 'edd-eu-vat' ),
				'type'        => 'text',
			],
			'edd_vat_address_country' => [
				'label'       => __( 'Country', 'edd-eu-vat' ),
				'description' => __( 'Select the country of your company\'s registered VAT address.', 'edd-eu-vat' ),
				'type'        => 'select',
				'searchable'  => true,
				'options'     => $this->format_edd_coutries_for_select(),
				'placeholder' => __( 'Select a country', 'edd-eu-vat' ),
				'value'       => edd_get_option( 'edd_vat_address_country' ) ?: edd_get_shop_country(),
			],
			'edd_vat_address_city'    => [
				'value'       => edd_get_option( 'edd_vat_address_city', '' ),
				'label'       => __( 'City / State', 'edd-eu-vat' ),
				'description' => __( 'Enter the city / state of your company\'s registered VAT address.', 'edd-eu-vat' ),
				'type'        => 'text',
			],
			'edd_vat_address_code'    => [
				'value'       => edd_get_option( 'edd_vat_address_code', '' ),
				'label'       => __( 'Zip / Postal Code', 'edd-eu-vat' ),
				'description' => __( 'Enter the zip / postal code of your company\'s registered VAT address.', 'edd-eu-vat' ),
				'type'        => 'text',
			],
		];

		return $fields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function submit( array $values ) {

		$edd_vat_company_name = isset( $values['edd_vat_company_name'] ) && ! empty( $values['edd_vat_company_name'] )
		? $values['edd_vat_company_name'] : false;

		$edd_vat_number = isset( $values['edd_vat_number'] ) && ! empty( $values['edd_vat_number'] )
		? $values['edd_vat_number'] : false;

		$edd_uk_vat_number = isset( $values['edd_uk_vat_number'] ) && ! empty( $values['edd_uk_vat_number'] )
		? $values['edd_uk_vat_number'] : false;

		$edd_vat_address_line_1 = isset( $values['edd_vat_address_line_1'] ) && ! empty( $values['edd_vat_address_line_1'] )
		? $values['edd_vat_address_line_1'] : false;

		$edd_vat_address_line_2 = isset( $values['edd_vat_address_line_2'] ) && ! empty( $values['edd_vat_address_line_2'] )
		? $values['edd_vat_address_line_2'] : false;

		$edd_vat_address_city = isset( $values['edd_vat_address_city'] ) && ! empty( $values['edd_vat_address_city'] )
		? $values['edd_vat_address_city'] : false;

		$edd_vat_address_code = isset( $values['edd_vat_address_code'] ) && ! empty( $values['edd_vat_address_code'] )
		? $values['edd_vat_address_code'] : false;

		$edd_vat_address_country = isset( $values['edd_vat_address_country'] ) && ! empty( $values['edd_vat_address_country'] )
		? $values['edd_vat_address_country'] : edd_get_shop_country();

		edd_update_option( 'edd_vat_company_name', $edd_vat_company_name );
		edd_update_option( 'edd_vat_number', $edd_vat_number );
		edd_update_option( 'edd_uk_vat_number', $edd_uk_vat_number );
		edd_update_option( 'edd_vat_address_line_1', $edd_vat_address_line_1 );
		edd_update_option( 'edd_vat_address_line_2', $edd_vat_address_line_2 );
		edd_update_option( 'edd_vat_address_city', $edd_vat_address_city );
		edd_update_option( 'edd_vat_address_code', $edd_vat_address_code );
		edd_update_option( 'edd_vat_address_country', $edd_vat_address_country );

		return Api::send_success_response();

	}

	/**
	 * Formats the EDD countries for the wizard select.
	 *
	 * @return array $select_countries
	 */
	private function format_edd_coutries_for_select() {
		$edd_countries = edd_get_country_list();

		$select_countries = array_map(
			function( $country_code, $country_name ) {
				return [
					'value'   => $country_code,
					'label' => html_entity_decode( $country_name ),
				];
			},
			array_keys( $edd_countries ),
			array_values( $edd_countries )
		);

		return $select_countries;
	}
}
