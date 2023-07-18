<?php
namespace Barn2\Plugin\EDD_VAT;

use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Registerable,
	Barn2\Plugin\EDD_VAT\Dependencies\Lib\Service,
	Barn2\Plugin\EDD_VAT\Util;

/**
 * Stores the current state of VAT in the EDD cart.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class Cart_VAT implements Registerable, Service {

	const SESSION_KEY = 'vat';

	/**
	 * The current VAT information.
	 *
	 * @var VAT_Check_Result
	 */
	private $vat_details;

	/**
	 * Whether the VAT is reverse charged in the cart.
	 *
	 * @var boolean
	 */
	private $is_reverse_charged = false;

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		add_action( 'edd_cart_contents_loaded_from_session', [ $this, 'restore' ] );
		add_action( 'wp', [ $this, 'check_billing_country' ] );
		add_action( 'edd_empty_cart', [ $this, 'clear' ] );
		add_action( 'shutdown', [ $this, 'save' ], 1 );
	}

	/**
	 * Checks a VAT number
	 *
	 * @param string $vat_number
	 * @param string $country_code
	 */
	public function check_vat_number( $vat_number, $country_code ) {
		$this->vat_details        = VAT_Checker_API::check_vat( $vat_number, $country_code );
		$this->is_reverse_charged = false;

		if ( $this->vat_details->is_valid() ) {
			$this->is_reverse_charged = Util::can_reverse_charge_vat( $country_code );
		}
	}

	/**
	 * Returns the VAT details result
	 *
	 * @return VAT_Check_Result
	 */
	public function get_vat_details() {
		return $this->vat_details;
	}

	/**
	 * Check whether the VAT has been reverse charged.
	 *
	 * @return bool
	 */
	public function is_reverse_charged() {
		return (bool) $this->is_reverse_charged;
	}

	/**
	 * Applies the VAT details if a cart is restored
	 *
	 * @param EDD_Cart $cart
	 */
	public function restore( $cart ) {
		if ( ! function_exists( '\EDD' ) ) {
			return;
		}
		$vat_session = \EDD()->session->get( self::SESSION_KEY );

		if ( ! $vat_session || ! isset( $vat_session['is_reverse_charged'], $vat_session['vat_check'] ) ) {
			return;
		}

		$this->is_reverse_charged = (bool) $vat_session['is_reverse_charged'];
		$this->vat_details        = null;

		edd_debug_log( 'EUVAT: cart restored with: ' . print_r( $vat_session, true ) );

		if ( ! empty( $vat_session['vat_check'] ) && is_array( $vat_session['vat_check'] ) ) {
			$vat_check_array = array_merge(
				array_fill_keys( [ 'vat_number', 'country_code', 'valid', 'name', 'address', 'error', 'consultation_number' ], '' ),
				$vat_session['vat_check']
			);

			if ( ! empty( $vat_check_array['vat_number'] ) ) {
				$vat_check                      = new VAT_Check_Result( $vat_check_array['vat_number'], $vat_check_array['country_code'] );
				$vat_check->valid               = $vat_check_array['valid'];
				$vat_check->name                = $vat_check_array['name'];
				$vat_check->address             = $vat_check_array['address'];
				$vat_check->error               = $vat_check_array['error'];
				$vat_check->consultation_number = $vat_check_array['consultation_number'];

				$this->vat_details = $vat_check;
			}
		}
	}

	/**
	 * Check if the billing country matches the saved VAT details and clear if not.
	 */
	public function check_billing_country() {
		if ( ! function_exists( '\edd_is_checkout' ) || ! edd_is_checkout() ) {
			return;
		}

		if ( empty( $this->vat_details ) ) {
			return;
		}

		$customer         = \EDD()->session->get( 'customer' );
		$selected_country = '';

		if ( $customer && ! empty( $customer['address']['country'] ) && '*' !== $customer['address']['country'] ) {
			$selected_country = $customer['address']['country'];
		} else {
			$selected_country = edd_get_shop_country();
		}

		// If selected country on checkout doesn't match saved VAT country, we clear the state.
		if ( $selected_country !== $this->vat_details->country_code ) {
			edd_debug_log( 'EUVAT: cleared VAT selected country on checkout doesn\'t match saved VAT country (' . $selected_country . ') - (' . $this->vat_details->country_code . ')' );
			$this->clear();
		}
	}

	/**
	 * Saves the VAT check to the cart session.
	 */
	public function save() {
		if ( function_exists( '\EDD' ) ) {
			\EDD()->session->set(
				self::SESSION_KEY,
				[
					'is_reverse_charged' => $this->is_reverse_charged(),
					'vat_check'          => (array) $this->get_vat_details(),
				]
			);
		}
	}

	/**
	 * Clear the VAT check from the cart session.
	 */
	public function clear() {
		if ( function_exists( '\EDD' ) ) {
			\EDD()->session->set( self::SESSION_KEY, null );
			$this->is_reverse_charged = false;
			$this->vat_details        = null;
		}
	}

}
