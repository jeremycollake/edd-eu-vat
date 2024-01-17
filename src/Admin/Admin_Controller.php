<?php

namespace Barn2\Plugin\EDD_VAT\Admin;

use Barn2\Plugin\EDD_VAT\Admin\Wizard\Setup_Wizard;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Registerable;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Service;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Util;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Plugin\Admin\Admin_Links;
use Barn2\Plugin\EDD_VAT\Dependencies\WPTRT\AdminNotices\Notices;

/**
 * Main admin class. Responsible for setting up the admin services.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class Admin_Controller implements Registerable, Service {

	private $services = [];

	/**
	 * Constructor
	 *
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( Licensed_Plugin $plugin ) {
		$this->services = [
			new Admin_Links( $plugin ),
		];

		if ( Util::is_edd_active() ) {
			$this->services[] = new Settings( $plugin );
		}
	}

	/**
	 * Register the hooks and filters.
	 */
	public function register() {
		Util::register_services( $this->services );

		add_action( 'admin_init', [ $this, 'add_notices' ] );
	}

	/**
	 * Registers admin notices
	 */
	public function add_notices() {
		$notices = new Notices();

		if ( function_exists( '\edd_use_taxes' ) && ! edd_use_taxes() ) {
			$notices->add(
				'edd_eu_vat_taxes',
				false,
				sprintf(
					/* translators: %1$s: HTML <strong> %2$s: HTML </strong> %3$s: HTML <a href> - tax settings page  %4$s: HTML </a> */
					__( '%1$sEasy Digital Downloads%2$s - Please %3$senable taxes%4$s in order to use the EU VAT extension.', 'edd-eu-vat' ),
					'<strong>',
					'</strong>',
					'<a href="' . admin_url( 'edit.php?post_type=download&page=edd-settings&tab=taxes' ) . '">',
					'</a>'
				),
				[
					'type'       => 'error',
					'capability' => 'manage_shop_settings',
				]
			);
			$notices->boot();
		}
	}
}
