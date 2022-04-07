<?php
namespace Barn2\Plugin\EDD_VAT;

use Barn2\VAT_Lib\Registerable,
	Barn2\VAT_Lib\Service,
	Barn2\Plugin\EDD_VAT\Util;

/**
 * Handles the VAT integration on the EDD Checkout.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class Checkout_Handler implements Registerable, Service {

	/**
	 * The current VAT state of the cart.
	 *
	 * @var Cart_VAT
	 */
	private $cart_vat;

	/**
	 * The full path to the plugin templates.
	 *
	 * @var string
	 */
	private $template_path;

	/**
	 * Constructor
	 *
	 * @param Cart_VAT $cart_vat
	 * @param string $template_path
	 */
	public function __construct( Cart_VAT $cart_vat, $template_path ) {
		$this->cart_vat      = $cart_vat;
		$this->template_path = trailingslashit( $template_path );
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		// Register action for VAT field placement - this allows themes/plugins to override.
		add_action( 'init', [ $this, 'register_vat_field_action' ] );

		// AJAX actions.
		add_action( 'wp_ajax_edd_vat_check', [ $this, 'ajax_vat_check' ] );
		add_action( 'wp_ajax_nopriv_edd_vat_check', [ $this, 'ajax_vat_check' ] );

		add_action( 'wp_ajax_edd_recalculate_taxes', [ $this, 'ajax_edd_recalculate_taxes' ], 5 );
		add_action( 'wp_ajax_nopriv_edd_recalculate_taxes', [ $this, 'ajax_edd_recalculate_taxes' ], 5 );

		// Tax filters.
		add_filter( 'edd_tax_rate', [ $this, 'tax_rate' ], 500, 3 );
		add_filter( 'edd_cart_tax', [ $this, 'cart_tax' ] );
		add_filter( 'edd_cart_item_price', [ $this, 'cart_item_price_includes_tax' ], 1000, 3 );

		// Process VAT on checkout.
		add_action( 'edd_checkout_error_checks', [ $this, 'checkout_error_checks' ], 10, 2 );

		// Add VAT to EDD payment.
		add_action( 'edd_insert_payment', [ $this, 'insert_payment' ], 10, 2 );
	}

	/**
	 * Registers the action which output the VAT field.
	 */
	public function register_vat_field_action() {
		add_action( apply_filters( 'edd_vat_checkout_vat_field_location', 'edd_cc_billing_bottom' ), [ $this, 'display_vat_field' ] );
	}

	/**
	 * Display VAT field on checkout (default location: below the billing address).
	 */
	public function display_vat_field() {
		if ( ! $this->should_display_vat_field() ) {
			return;
		}

		$vat_details      = $this->cart_vat->get_vat_details();
		$vat_required     = edd_field_is_required( 'edd_vat_number' );
		$vat_number       = ! empty( $vat_details->vat_number ) ? $vat_details->vat_number : '';
		$vat_check_result = $this->get_vat_check_result();

		ob_start();
		include $this->template_path . 'checkout-vat-field.php';

		echo apply_filters( 'edd_vat_checkout_vat_field_html', ob_get_clean(), $vat_details, $this->cart_vat->is_reverse_charged() );
	}

	/**
	 * AJAX action for VAT number check.
	 */
	public function ajax_vat_check() {
		// Verify nonce
		$nonce          = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
		$nonce_verified = wp_verify_nonce( $nonce, 'edd-checkout-address-fields' );

		if ( false === $nonce_verified ) {
			return false;
		}

		// Check cart not empty
		if ( ! edd_get_cart_contents() ) {
			return false;
		}

		// Handle VAT number
		$this->handle_vat_input();

		// Get updated cart
		ob_start();
		edd_checkout_cart();
		$cart = ob_get_clean();

		// Build response
		$response = [
			'html'             => $cart,
			'tax_raw'          => edd_get_cart_tax(),
			'tax'              => html_entity_decode( edd_cart_tax( false ), ENT_COMPAT, 'UTF-8' ),
			'tax_rate_raw'     => edd_get_tax_rate(),
			'tax_rate'         => html_entity_decode( edd_get_formatted_tax_rate(), ENT_COMPAT, 'UTF-8' ),
			'total'            => html_entity_decode( edd_cart_total( false ), ENT_COMPAT, 'UTF-8' ),
			'total_raw'        => edd_get_cart_total(),
			'vat_check_result' => $this->get_vat_check_result()
		];

		// Send
		wp_send_json( $response );
	}

	/**
	 * Recalculate taxes via AJAX
	 */
	public function ajax_edd_recalculate_taxes() {
		$selected_country = isset( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '';

		if ( empty( $selected_country ) ) {
			$selected_country = edd_get_shop_country();
		}

		// Clear the VAT state if the country has changed.
		if ( ! empty( $this->cart_vat->get_vat_details()->country_code ) && $selected_country !== $this->cart_vat->get_vat_details()->country_code ) {
			$this->cart_vat->clear();
		}
	}

	/**
	 * Filter in the EU VAT rates into EDD tax rates.
	 *
	 * @param mixed $rate
	 * @param string $country
	 * @param string $state
	 * @return mixed $rate
	 */
	public function tax_rate( $rate, $country, $state ) {
		if ( $this->cart_vat->is_reverse_charged() ) {
			$rate = apply_filters( 'edd_vat_reverse_charge_vat_rate', 0.00, $country, $state );
		} elseif ( apply_filters( 'edd_vat_apply_eu_vat_automatically', true ) ) {
			$edd_tax_rates = edd_get_tax_rates();

			if ( ! is_array( $edd_tax_rates ) ) {
				$edd_tax_rates = [];
			}

			// Pluck all tax rates from EDD settings, keyed by country code.
			$edd_tax_rates_by_country = wp_list_pluck( $edd_tax_rates, 'rate', 'country' );

			// If a tax rate is set for the specified country in the settings [Downloads -> Settings -> Taxes], we don't modify the $rate passed to this function.
			// This means the rate in the settings will be used. If no tax rate has been set, we calculate it ourselves.
			if ( ! array_key_exists( $country, $edd_tax_rates_by_country ) ) {
				$rate = (float) Util::get_vat_rate( $country ) / 100;
			}
		}

		return $rate;
	}

	/**
	 * Gets the cart tax label.
	 *
	 * @param string $cart_tax
	 * @return string $cart_tax
	 */
	public function cart_tax( $cart_tax ) {
		$tax_rate = edd_get_tax_rate();

		if ( $this->cart_vat->is_reverse_charged() ) {
			$cart_tax = apply_filters(
				'edd_vat_cart_label_reverse_charged',
				/* translators: %s is the formatted tax amount */
				sprintf( __( '[VAT reverse charged] %s', 'edd-eu-vat' ), $cart_tax ),
				$cart_tax
			);
		} elseif ( $tax_rate ) {
			$tax_percent    = $tax_rate * 100;
			$formatted_rate = $tax_percent === round( $tax_percent ) ? sprintf( '%.0f', $tax_percent ) : sprintf( '%.1f', $tax_percent );

			$cart_tax = apply_filters(
				'edd_vat_cart_label_tax_rate',
				sprintf( '[%1$s%%] %2$s', $formatted_rate, $cart_tax ),
				$cart_tax,
				$formatted_rate
			);
		}

		return $cart_tax;
	}

	/**
	 * Subtract VAT from cart item total if price includes tax.
	 *
	 * @param mixed $total
	 * @param mixed $download_id
	 * @param mixed $options
	 * @return mixed
	 */
	public function cart_item_price_includes_tax( $total, $download_id, $options ) {
		if ( ! edd_prices_include_tax() ) {
			return $total;
		}

		if ( ! $this->cart_vat->is_reverse_charged() ) {
			return $total;
		}

		if ( empty( $this->cart_vat->get_vat_details()->country_code ) ) {
			return $total;
		}

		$country_code = $this->cart_vat->get_vat_details()->country_code;
		$tax_rate     = (float) Util::get_vat_rate( $country_code ) / 100;

		$total = $total / ( 1 + $tax_rate );

		return $total;
	}

	/**
	 * Handle VAT checks on checkout if necessary.
	 *
	 * @param boolean|array $valid_data
	 * @param array $post_data
	 */
	public function checkout_error_checks( $valid_data, $post_data ) {
		$vat_details      = $this->cart_vat->get_vat_details();
		$saved_vat_number = $vat_details ? $vat_details->vat_number : '';

		if ( empty( $post_data['vat_number'] ) ) {
			// If submitted VAT number is empty but we have previously checked a VAT number, we need to refresh.
			if ( $saved_vat_number ) {
				$this->handle_vat_input();
			}
		} else {
			// Otherwise check submitted VAT number matches what we have saved.
			$vat_number = sanitize_text_field( $post_data['vat_number'] );

			if ( $vat_number !== $saved_vat_number ) {
				$this->handle_vat_input();
			}
		}
	}

	/**
	 * Add VAT details to EDD payment.
	 *
	 * @param int $payment_id
	 * @param array $payment_data
	 */
	public function insert_payment( $payment_id, $payment_data ) {
		edd_update_payment_meta( $payment_id, '_edd_payment_vat_reverse_charged', $this->cart_vat->is_reverse_charged() );

		$country_code = ! empty( $payment_data['user_info']['address']['country'] ) ? $payment_data['user_info']['address']['country'] : false;
		edd_update_payment_meta( $payment_id, '_edd_payment_vat_is_eu', in_array( $country_code, Util::get_eu_countries(), true ) );

		if ( $this->cart_vat->get_vat_details() ) {
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_number', $this->cart_vat->get_vat_details()->vat_number );
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_number_valid', $this->cart_vat->get_vat_details()->is_valid() );
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_company_name', $this->cart_vat->get_vat_details()->name );
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_company_address', $this->cart_vat->get_vat_details()->address );
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_consultation_number', $this->cart_vat->get_vat_details()->consultation_number );
		}
	}

	/**
	 * Handle VAT input on the checkout.
	 */
	private function handle_vat_input() {
		if ( empty( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		$vat_number   = sanitize_text_field( $_POST['vat_number'] );
		$country_code = sanitize_text_field( $_POST['billing_country'] );

		// Check the submitted VAT number.
		$this->cart_vat->check_vat_number( $vat_number, $country_code );
	}

	/**
	 * Gets the VAT check result html.
	 *
	 * @return string
	 */
	private function get_vat_check_result() {
		$vat_details = $this->cart_vat->get_vat_details();

		if ( ! $vat_details ) {
			return '';
		}

		if ( $vat_details->is_valid() ) {
			if ( $this->cart_vat->is_reverse_charged() ) {
				$result_text = __( 'VAT number valid - reverse charge applied.', 'edd-eu-vat' );
			} else {
				/* translators: %s: Country name */
				$result_text = sprintf( __( 'Your VAT number is valid, but we cannot apply the reverse charge in %s.', 'edd-eu-vat' ), edd_get_country_name( $vat_details->country_code ) );
			}
		} else {
			$result_text = self::vat_error_code_to_string( $vat_details->error );
		}

		$result_text = apply_filters( 'edd_vat_check_result_text', $result_text, $vat_details );

		$result_class = $vat_details->is_valid() ? 'edd-vat-check-success' : 'edd-vat-check-error';
		$result_class = apply_filters( 'edd_vat_check_result_class', $result_class, $vat_details );

		ob_start();
		include $this->template_path . 'checkout-vat-result.php';

		return apply_filters( 'edd_vat_checkout_vat_result_html', ob_get_clean(), $vat_details, $this->cart_vat->is_reverse_charged() );
	}

	/**
	 * Determines whether the VAT field should be displayed.
	 *
	 * @return boolean
	 */
	private function should_display_vat_field() {
		$show_vat_field = ( did_action( 'edd_purchase_form_top' ) && ! did_action( 'edd_purchase_form_bottom' ) ) || edd_is_checkout();
		return apply_filters( 'edd_vat_checkout_display_vat_field', $show_vat_field );
	}

	/**
	 * Gets an error message given an VAT_Check_Result code.
	 *
	 * @param int $code
	 * @return string
	 */
	private static function vat_error_code_to_string( $code ) {
		switch ( $code ) {
			case VAT_Check_Result::NO_VAT_NUMBER:
				$error = __( 'Please enter a VAT number.', 'edd-eu-vat' );
				break;
			case VAT_Check_Result::NO_COUNTRY_CODE:
				$error = __( 'Please select a country.', 'edd-eu-vat' );
				break;
			case VAT_Check_Result::INVALID_VAT_NUMBER:
				$error = __( 'The VAT number is invalid.', 'edd-eu-vat' );
				break;
			case VAT_Check_Result::INVALID_COUNTRY_CODE:
				$error = __( 'The VAT number applies to EU countries only.', 'edd-eu-vat' );
				break;
			case VAT_Check_Result::VAT_NUMBER_INVALID_FOR_COUNTRY:
				$error = __( 'Your billing country must match the country for the VAT number.', 'edd-eu-vat' );
				break;
			case VAT_Check_Result::INVALID_INPUT:
				$error = __( 'The country or VAT number is invalid.', 'edd-eu-vat' );
				break;
			case VAT_Check_Result::API_ERROR:
				$error = __( 'We\'re having trouble checking your VAT number. Please try again or contact our support team.', 'edd-eu-vat' );
				break;
			default:
				$error = $code;
		}
		return apply_filters( 'edd_vat_error_code_to_string', $error, $code );
	}

}
