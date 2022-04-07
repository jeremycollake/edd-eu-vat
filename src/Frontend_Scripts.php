<?php
namespace Barn2\Plugin\EDD_VAT;

use Barn2\VAT_Lib\Registerable,
	Barn2\Plugin\EDD_VAT\Util,
	Barn2\VAT_Lib\Util as Lib_Util;

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

		wp_register_style( 'edd-eu-vat', $this->plugin_url . "assets/css/edd-eu-vat.min.css", [], $this->version );
		wp_register_style( 'edd-eu-vat-receipt', $this->plugin_url . "assets/css/edd-eu-vat-receipt.min.css", [], $this->version );

		wp_register_script( 'edd-eu-vat', $this->plugin_url . "assets/js/edd-eu-vat{$min}.js", [ 'jquery' ], $this->version, true );

		$countries = Util::get_eu_countries();

		if ( apply_filters( 'edd_eu_vat_uk_hide_checkout_input', false ) ) {
			$countries = array_values( array_diff( $countries, [ 'GB' ] ) );
		}

		$script_params = [
			'countries'            => $countries,
			'hide_edd_sl_notices'  => apply_filters( 'edd_vat_hide_edd_sl_upgrade_notices', true ),
			'messages'             => [
				'vat_number_missing' => __( 'Please enter a VAT number.', 'edd-eu-vat' ),
				'country_missing'    => __( 'Please select a country.', 'edd-eu-vat' ),
				'ajax_error' => __('There was an error trying to validate your VAT number, please try reloading the page or try again later.', 'edd-eu-vat'),
			]
		];

		wp_localize_script( 'edd-eu-vat', 'edd_eu_vat_params', apply_filters( 'edd_vat_script_params', $script_params ) );
	}

	/**
	 * Enqueues the scripts and styles in WP.
	 */
	public function load_scripts() {
		if ( ( function_exists( 'edd_is_checkout' ) && edd_is_checkout() ) || apply_filters( 'edd_vat_force_load_scripts', false ) ) {
			wp_enqueue_style( 'edd-eu-vat' );
			wp_enqueue_script( 'edd-eu-vat' );
		}
	}

}
