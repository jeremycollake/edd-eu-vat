<?php

namespace Barn2\Plugin\EDD_VAT\Admin\Wizard;

use Barn2\Plugin\EDD_VAT\Admin\Wizard\Steps,
	Barn2\Plugin\EDD_VAT\Dependencies\Lib\Plugin\License\EDD_Licensing,
	Barn2\Plugin\EDD_VAT\Dependencies\Lib\Plugin\License\Plugin_License,
	Barn2\Plugin\EDD_VAT\Dependencies\Lib\Plugin\Licensed_Plugin,
	Barn2\Plugin\EDD_VAT\Dependencies\Lib\Registerable,
	Barn2\Plugin\EDD_VAT\Dependencies\Lib\Util as Lib_Util;
use Barn2\Plugin\EDD_VAT\Dependencies\Setup_Wizard\Setup_Wizard as Wizard;

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

		$this->wizard = $wizard;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$this->wizard->boot();
		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_footer', [ $this, 'custom_styling' ] );
	}

	/**
	 * Additional inline styling required for a specific input of the wizard.
	 *
	 * @return void
	 */
	public function custom_styling() {
		$screen = get_current_screen();

		if ( $screen->id !== 'toplevel_page_edd-eu-vat-setup-wizard' ) {
			return;
		}

		?>
		<style>
			.barn2-wizard-input.with-separator {
				margin: 0 -24px 1.5rem;
				padding: 24px 24px 0;
				border-top: 1px solid rgba(0, 0, 0, 0.1);
			}
		</style>
		<?php
	}

}
