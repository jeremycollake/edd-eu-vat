<?php

namespace Barn2\Plugin\EDD_VAT\Admin\Wizard;

use Barn2\Plugin\EDD_VAT\Admin\Wizard\Steps,
	Barn2\VAT_Lib\Plugin\License\EDD_Licensing,
	Barn2\VAT_Lib\Plugin\License\Plugin_License,
	Barn2\VAT_Lib\Plugin\Licensed_Plugin,
	Barn2\VAT_Lib\Registerable,
	Barn2\VAT_Lib\Util as Lib_Util;

/**
 * Main Setup Wizard Loader
 *
 * @package   Barn2/edd-eu-vat
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Setup_Wizard implements Registerable {

	private $plugin;
	private $wizard;

	/**
	 * Constructor.
	 *
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( Licensed_Plugin $plugin ) {

		$this->plugin = $plugin;

		$steps = [
			new Steps\License_Verification(),
			new Steps\Company_Information(),
			new Steps\Base_Country(),
			new Steps\Upsell(),
			new Steps\Completed(),
		];

		$wizard = new Wizard( $this->plugin, $steps, false );

		$wizard->configure(
			[
				'skip_url'        => admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions&section=vat' ),
				'license_tooltip' => esc_html__( 'The licence key is contained in your order confirmation email.', 'edd-eu-vat' ),
			]
		);

		$wizard->add_edd_api( EDD_Licensing::class );
		$wizard->add_license_class( Plugin_License::class );

		$script_dependencies = Lib_Util::get_script_dependencies( $this->plugin, 'admin/wizard.min.js' );
		$wizard->set_non_wc_asset(
			$plugin->get_dir_url() . 'assets/js/admin/wizard.min.js',
			$script_dependencies['dependencies'],
			$script_dependencies['version']
		);

		$wizard->add_custom_asset(
			$plugin->get_dir_url() . 'assets/js/admin/wizard-custom.min.js',
			Lib_Util::get_script_dependencies( $this->plugin, 'admin/wizard-custom.min.js' )
		);

		$this->wizard = $wizard;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$this->wizard->boot();
	}

}
