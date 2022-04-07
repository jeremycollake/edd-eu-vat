<?php

namespace Barn2\Plugin\EDD_VAT\Admin\Wizard;

use Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Interfaces\Restartable;
use Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Setup_Wizard;

/**
 * Main Setup Wizard Loader
 *
 * @package   Barn2/edd-eu-vat
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Wizard extends Setup_Wizard {

	/**
	 * Custom enqueue assets method for Non-WC wizards.
	 *
	 * @param string $hook
	 */
	public function enqueue_assets( $hook ) {

		if ( $hook !== 'toplevel_page_' . $this->get_slug() ) {
			return;
		}

		$slug = 'b2-wizard-nonwc-app';

		$styling_dependencies = [ 'wp-components' ];

		$custom_asset = $this->get_custom_asset();

		if ( isset( $custom_asset['url'] ) ) {
			if ( isset( $custom_asset['dependencies'] ) && ! isset( $custom_asset['dependencies']['dependencies'] ) ) {
				$custom_asset_dependencies = $custom_asset['dependencies'];
			} else {
				$custom_asset_dependencies = $custom_asset['dependencies']['dependencies'];
			}

			if ( empty( $custom_asset_dependencies ) || ! is_array( $custom_asset_dependencies ) ) {
				wp_die( 'Custom asset dependencies should not be empty and should be an array.' );
			}

			wp_enqueue_script( "{$slug}_custom_asset", $custom_asset['url'], $custom_asset_dependencies, 1, true );

			wp_add_inline_script( "{$slug}_custom_asset", 'const barn2_setup_wizard = ' . wp_json_encode( $this->get_js_args() ), 'before' );
		}

		wp_enqueue_style( 'b2-wc-components', $this->get_library_url() . 'resources/wc-vendor/components.css', \false, $this->get_non_wc_version() );

		wp_enqueue_style( $slug, $this->get_library_url() . 'build/main.css', $styling_dependencies, \filemtime( $this->get_library_path() . '/build/main.css' ) );

		wp_enqueue_script( $slug, $this->get_non_wc_asset(), $this->get_non_wc_dependencies(), $this->get_non_wc_version(), \true );

	}
}
