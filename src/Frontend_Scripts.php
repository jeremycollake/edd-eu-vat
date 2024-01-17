<?php
namespace Barn2\Plugin\EDD_VAT;

use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Registerable;
use Barn2\Plugin\EDD_VAT\Util;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Util as Lib_Util;

/**
 * Loads the scripts and styles needed on the front-end.
 *
 * @package   Barn2\edd-eu-vat
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Frontend_Scripts implements Registerable {

	private $plugin_url;
	private $version;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_url
	 * @param float $version
	 */
	public function __construct( $plugin_url, $version ) {
		$this->plugin_url = trailingslashit( $plugin_url );
		$this->version    = $version;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ], 200 ); // after EDD Stripe JS
	}

	/**
	 * Registers the scripts and styles in WP.
	 */
	public function register_scripts() {
		$min = Lib_Util::get_script_suffix();

		wp_register_style( 'edd-eu-vat', $this->plugin_url . 'assets/css/edd-eu-vat.css', [], $this->version );
		wp_register_style( 'edd-eu-vat-receipt', $this->plugin_url . 'assets/css/edd-eu-vat-receipt.css', [], $this->version );

		wp_register_script( 'edd-eu-vat', $this->plugin_url . 'assets/js/edd-eu-vat-main.js', [ 'jquery' ], $this->version, true );

		wp_register_script( 'edd-eu-vat-debug', $this->plugin_url . 'assets/js/edd-eu-vat-debug.js', [ 'jquery' ], $this->version, true );

		$countries = Util::get_eu_countries();

		if ( apply_filters( 'edd_eu_vat_uk_hide_checkout_input', false ) ) {
			$countries = array_values( array_diff( $countries, [ 'GB' ] ) );
		}

		$script_params = [
			'countries'                      => $countries,
			'hide_edd_sl_notices'            => apply_filters( 'edd_vat_hide_edd_sl_upgrade_notices', true ),
			'debug_mode'                     => edd_is_debug_mode(),
			'messages'                       => [
				'vat_number_missing' => __( 'Please enter a VAT number.', 'edd-eu-vat' ),
				'country_missing'    => __( 'Please select a country.', 'edd-eu-vat' ),
				'ajax_error'         => __( 'There was an error trying to validate your VAT number, please try reloading the page or try again later.', 'edd-eu-vat' ),
			],
			'checkout_updated_cart_selector' => apply_filters( 'edd_vat_checkout_updated_cart_selector', '#edd_checkout_cart_form' ), // '#edd_checkout_cart_form
			'checkout_form_selector'         => apply_filters( 'edd_vat_checkout_form_selector', '#edd_checkout_cart_form' ),
		];

		wp_localize_script( 'edd-eu-vat', 'edd_eu_vat_params', apply_filters( 'edd_vat_script_params', $script_params ) );

		$purchase_confirm_params = [
			'nonce'      => wp_create_nonce( 'euvat-debug' ),
			'ajax'       => admin_url( 'admin-ajax.php' ),
			'payment_id' => isset( $_GET['id'] ) ? absint( $_GET['id'] ) : false,
		];

		wp_add_inline_script( 'edd-eu-vat-debug', 'const euvat_debug = ' . wp_json_encode( $purchase_confirm_params ), 'before' );
	}

	/**
	 * Enqueues the scripts and styles in WP.
	 */
	public function load_scripts() {
		if ( ( function_exists( 'edd_is_checkout' ) && edd_is_checkout() ) || apply_filters( 'edd_vat_force_load_scripts', false ) ) {
			wp_enqueue_style( 'edd-eu-vat' );
			wp_enqueue_script( 'edd-eu-vat' );
		}

		if ( function_exists( 'edd_is_success_page' ) && edd_is_success_page() ) {
			wp_enqueue_script( 'edd-eu-vat-debug' );
		}
	}
}
