<?php

namespace Barn2\Plugin\EDD_VAT\Admin\Wizard\Steps;

use Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Step;

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
		$this->set_name( esc_html__( 'Base Country', 'edd-eu-vat' ) );
		$this->set_description( esc_html__( ' Choose whether to allow customers from your home country to reverse charge the VAT.', 'edd-eu-vat' ) );
		$this->set_title( esc_html__( 'Base Country', 'edd-eu-vat' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_fields() {

		$base_country_reverse_charge_setting = '';

		$base_country_code = edd_get_option( 'edd_vat_address_country', edd_get_shop_country() );

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
				'value'       => edd_get_option( 'edd_vat_reverse_charge_base_country' ),
				'type'        => 'checkbox',
			]
		];

		return $fields;

	}

	/**
	 * {@inheritdoc}
	 */
	public function submit() {

		check_ajax_referer( 'barn2_setup_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->send_error( esc_html__( 'You are not authorized.', 'edd-eu-vat' ) );
		}

		$values = $this->get_submitted_values();

		$reverse_charge_base_country = isset( $values['edd_vat_reverse_charge_base_country'] ) && ! empty( $values['edd_vat_reverse_charge_base_country'] )
		? $this->bool_to_edd_checkbox_string( $values['edd_vat_reverse_charge_base_country'] ) : false;

		edd_update_option( 'edd_vat_reverse_charge_base_country', $reverse_charge_base_country );

		wp_send_json_success();

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
