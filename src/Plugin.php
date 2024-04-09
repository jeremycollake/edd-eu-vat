<?php
namespace Barn2\Plugin\EDD_VAT;

use Barn2\Plugin\EDD_VAT\Admin\Plugin_Setup;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Registerable;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Translatable;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Service_Provider;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Plugin\Premium_Plugin;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Util as Lib_Util;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Admin\Notices;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Service;
use Barn2\Plugin\EDD_VAT\Admin\Wizard\Setup_Wizard;

/**
 * The main plugin class. Responsible for loading the plugin.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class Plugin extends Premium_Plugin implements Registerable, Translatable, Service_Provider {

	const NAME    = 'Easy Digital Downloads EU VAT';
	const ITEM_ID = 146171;

	/**
	 * Constructs and initalizes an EDD VAT plugin instance.
	 *
	 * @param string $file The main plugin __FILE__
	 * @param string $version The current plugin version
	 */
	public function __construct( $file = null, $version = '1.0' ) {
		parent::__construct(
			[
				'name'               => self::NAME,
				'item_id'            => self::ITEM_ID,
				'version'            => $version,
				'file'               => $file,
				'is_edd'             => true,
				'settings_path'      => 'edit.php?post_type=download&page=edd-settings&tab=extensions&section=vat',
				'documentation_path' => 'kb-categories/edd-eu-vat-kb/',
				'legacy_db_prefix'   => 'edd_eu_vat',
			]
		);

		// Add core service to handle plugin setup.
		$this->add_service( 'plugin_setup', new Plugin_Setup( $this->get_file(), $this ), true );
	}

	/**
	 * Registers the plugin with WordPress.
	 */
	public function register() {
		parent::register();

		add_action( 'plugins_loaded', [ $this, 'load_services' ] );
		add_action( 'plugins_loaded', [ $this, 'register_services' ], 20 );
		add_action( 'init', [ $this, 'load_textdomain' ] );
	}

	/**
	 * Setup the plugin services.
	 */
	public function load_services() {
		// Check if EDD Easy Digital Downloads is active.
		if ( ! Lib_Util::is_edd_active() ) {
			return;
		}

		// Always create the admin service.
		if ( Lib_Util::is_admin() ) {
			$this->add_service( 'admin', new Admin\Admin_Controller( $this ) );
		}

		if ( ! extension_loaded( 'soap' ) ) {
			$this->add_missing_soap_extension_warning();
		}

		$this->add_service( 'setup_wizard', new Setup_Wizard( $this ) );

		// Only create these services if license is valid.
		if ( $this->has_valid_license() ) {
			$services                     = [];
			$services['cart_vat']         = new Cart_VAT();
			$services['checkout_handler'] = new Checkout_Handler( $services['cart_vat'], $this->get_template_path() );
			$services['purchase_receipt'] = new Purchase_Receipt();
			$services['scripts']          = new Frontend_Scripts( $this->get_dir_url(), $this->get_version() );
			$services['email_tags']       = new Admin\Email_Tags();

			// Integrations
			$services['recurring']       = new Integrations\EDD_Recurring();
			$services['invoices']        = new Integrations\EDD_Invoices();
			$services['pdf_invoices']    = new Integrations\EDD_PDF_Invoices();
			$services['temporary_rates'] = new Integrations\Temporary_VAT_Rates();

			foreach ( $services as $service_key => $service ) {
				$this->add_service( $service_key, $service );
			}

			if ( Lib_Util::is_admin() ) {
				$admin_services = [
					'view_order'      => new Admin\View_Order( $this->get_template_path() ),
					'payments_export' => new Admin\Export\Payments_Export(),
					'vat_export'      => new Admin\Export\VAT_Payments_Export( $this->get_template_path() ),
					'ec_sales_export' => new Admin\Export\VAT_EC_Sales_Payments_Export( $this->get_template_path() ),
				];

				foreach ( $admin_services as $service_key => $service ) {
					$this->add_service( $service_key, $service );
				}
			}
		}
	}

	/**
	 * Load textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'edd-eu-vat', false, $this->get_slug() . '/languages' );
	}

	/**
	 * Gets the template directory file path
	 *
	 * @return string
	 */
	public function get_template_path() {
		return $this->get_dir_path() . 'views/';
	}

	/**
	 * Adds EDD requirement notice.
	 */
	public function add_missing_edd_notice() {
		$notices = new Notices();
		$notices->add(
			'edd_eu_vat_edd_inactive',
			false,
			sprintf(
				/* translators: %1$s: HTML <a href> EDD WordPress.org link %2$s: HTML </a> */
				__( 'Please install and activate %1$sEasy Digital Downloads%2$s in order to use the EU VAT extension.', 'edd-eu-vat' ),
				Lib_Util::format_link_open( 'https://wordpress.org/plugins/easy-digital-downloads/', true ),
				'</a>'
			),
			[
				'type'       => 'error',
				'capability' => 'install_plugins',
			]
		);
		$notices->boot();
	}

	/**
	 * Adds PHP SOAP extension requirement notice.
	 */
	private function add_missing_soap_extension_warning() {
		$notices = new Notices();
		$notices->add(
			'edd_eu_vat_soap_missing',
			false,
			__( 'Easy Digital Downloads EU VAT requires the PHP Soap extension to be installed in order to validate EU VAT numbers.', 'edd-eu-vat' ),
			[
				'type'       => 'warning',
				'capability' => 'install_plugins',
			]
		);
		$notices->boot();
	}
}
