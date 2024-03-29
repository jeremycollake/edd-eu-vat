<?php

namespace Barn2\Plugin\EDD_VAT\Admin\Wizard\Steps;

use Barn2\Plugin\EDD_VAT\Dependencies\Setup_Wizard\Api;
use Barn2\Plugin\EDD_VAT\Dependencies\Setup_Wizard\Step;
use Barn2\Plugin\EDD_VAT\Util;

/**
 * General Step.
 *
 * @package   Barn2/edd-eu-vat
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Base_Country extends Step {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->set_id( 'base-country' );
		$this->set_name( esc_html__( 'Reverse charge', 'edd-eu-vat' ) );
		$this->set_description( esc_html__( 'Allow customers from your home country to reverse charge the VAT.', 'edd-eu-vat' ) );
		$this->set_title( esc_html__( 'Reverse charge', 'edd-eu-vat' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_fields() {

		$base_country_reverse_charge_setting = '';

		$base_country_code = Util::get_country_for_api( edd_get_shop_country() );

		if ( $base_country_code ) {
			$base_country                        = edd_get_country_name( $base_country_code );
			$base_country_reverse_charge_setting = sprintf( ' (%s)', $base_country );
		}

		$fields = [
			'edd_vat_reverse_charge_base_country' => [
				'title'       => __( 'Reverse Charge in Base Country?', 'edd-eu-vat' ),
				'label'       => __( 'Yes', 'edd-eu-vat' ),
				/* translators: %s: EDD Base Country */
				'description' => sprintf( __( 'Allow the VAT reverse charge for customers based in your home country%s.', 'edd-eu-vat' ), $base_country_reverse_charge_setting ),
				'value'       => edd_get_option( 'edd_vat_reverse_charge_base_country' ) === '1' || edd_get_option( 'edd_vat_reverse_charge_base_country' ) === 'true',
				'type'        => 'checkbox',
			]
		];

		return $fields;

	}

	/**
	 * {@inheritdoc}
	 */
	public function submit( array $values ) {

		$reverse_charge_base_country = isset( $values['edd_vat_reverse_charge_base_country'] ) && ! empty( $values['edd_vat_reverse_charge_base_country'] )
		? $this->bool_to_edd_checkbox_string( $values['edd_vat_reverse_charge_base_country'] ) : false;

		edd_update_option( 'edd_vat_reverse_charge_base_country', $reverse_charge_base_country );

		return Api::send_success_response();

	}

	/**
	 * Converts bool to 'yes' or 'no'
	 *
	 * @param mixed $value
	 * @return string
	 */
	private function bool_to_edd_checkbox_string( $value ) {
		return ( empty( $value ) || $value === 'false' ) ? '0' : '1';
	}
}
