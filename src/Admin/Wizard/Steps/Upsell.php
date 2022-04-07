<?php

namespace Barn2\Plugin\EDD_VAT\Admin\Wizard\Steps;

use Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Steps\Cross_Selling,
	Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Util as Wizard_Util;

/**
 * Upsell Step.
 *
 * @package   Barn2/edd-eu-vat
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Upsell extends Cross_Selling {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'More', 'edd-eu-vat' ) );
		$this->set_description(
			sprintf(
				// translators: %1$s: URL to All Access Pass page %2$s: URL to the KB about the upgrading process
				__( 'Enhance your store with these fantastic plugins from Barn2, or get them all by upgrading to an <a href="%1$s" target="_blank">All Access Pass<a/>! <a href="%2$s" target="_blank">(learn how here)</a>', 'edd-eu-vat' ),
				'https://barn2.com/wordpress-plugins/bundles/',
				'https://barn2.com/kb/how-to-upgrade-license/'
			)
		);
		$this->set_title( esc_html__( 'Extra features', 'edd-eu-vat' ) );
	}

	/**
	 * Query for upsells from the barn2 website and store them in a transient.
	 *
	 * This is just customised to send the right slug to the upsell API.
	 *
	 * @return void
	 */
	public function get_upsells() {
		$this->get_wizard()->set_as_completed();

		check_ajax_referer( 'barn2_setup_wizard_upsells_nonce', 'nonce' );

		$plugins   = [];
		$transient = get_transient( "barn2_wizard_{$this->get_plugin()->get_slug()}_upsells" );
		$license   = $this->get_wizard()->get_licensing()->get_license_key();

		if ( $transient ) {

			$plugins = $transient;

		} else {

			$args = [
				'plugin' => 'easy-digital-downloads-eu-vat'
			];

			if ( ! empty( $license ) ) {
				$args['license'] = $license;
			}

			$request = wp_remote_get(
				add_query_arg(
					$args,
					self::REST_URL
				)
			);

			$response = wp_remote_retrieve_body( $request );
			$response = json_decode( $response, true );

			if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
				if ( isset( $response['error_message'] ) ) {
					$this->send_error( sanitize_text_field( $response['error_message'] ) );
				} else {
					$this->send_error( __( 'Something went wrong while retrieving the list of products. Please try again later.', 'edd-eu-vat' ) );
				}
			}

			if ( isset( $response['success'] ) && isset( $response['upsells'] ) ) {
				set_transient( "barn2_wizard_{$this->get_plugin()->get_slug()}_upsells", Wizard_Util::clean( $response['upsells'] ), DAY_IN_SECONDS );
			}

			$plugins = $response['upsells'];

		}

		foreach ( $plugins as $index => $plugin ) {
			if ( $plugin['slug'] === 'all-access' ) {
				continue;
			}
			if ( is_plugin_active( "{$plugin['slug']}/{$plugin['slug']}.php" ) ) {
				unset( $plugins[ $index ] );
			}
		}

		wp_send_json_success(
			[
				'upsells' => $plugins,
				'license' => $license,
			]
		);
	}
}
