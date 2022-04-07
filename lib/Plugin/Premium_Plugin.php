<?php
namespace Barn2\VAT_Lib\Plugin;

use Barn2\VAT_Lib\Registerable,
	Barn2\VAT_Lib\Util,
	Barn2\VAT_Lib\Plugin\License\EDD_Licensing,
	Barn2\VAT_Lib\Plugin\License\Plugin_License,
	Barn2\VAT_Lib\Plugin\Admin\Plugin_Updater,
	Barn2\VAT_Lib\Plugin\License\Admin\License_Key_Setting,
	Barn2\VAT_Lib\Plugin\License\Admin\License_Notices,
	Barn2\VAT_Lib\Plugin\License\License_Checker;

/**
 * Extends Simple_Plugin to add additional functions for premium plugins (i.e. with a license key).
 *
 * @package   Barn2\barn2-lib
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 * @version   1.2
 */
class Premium_Plugin extends Simple_Plugin implements Registerable, Licensed_Plugin {

	private $services = [];

	public function __construct( array $data ) {
		parent::__construct( array_merge( [
			'item_id'              => 0,
			'license_setting_path' => '',
			'legacy_db_prefix'     => ''
				], $data
		) );

		$this->data['license_setting_path'] = ltrim( $this->data['license_setting_path'], '/' );

		$this->services['license']         = new Plugin_License( $this->get_id(), EDD_Licensing::instance(), $this->get_legacy_db_prefix() );
		$this->services['plugin_updater']  = new Plugin_Updater( $this, EDD_Licensing::instance() );
		$this->services['license_checker'] = new License_Checker( $this->get_file(), $this->get_license() );
		$this->services['license_setting'] = new License_Key_Setting( $this->get_license(), $this->is_woocommerce(), $this->is_edd() );
		$this->services['license_notices'] = new License_Notices( $this );
	}

	public function register() {
		Util::register_services( $this->services );
	}

	/**
	 * Get the item ID for the plugin, usually the EDD Download ID.
	 *
	 * @return int The item ID
	 * @deprecated since 1.2 Replaced by Simple_Plugin::get_id()
	 */
	public function get_item_id() {
		return (int) $this->data['item_id'];
	}

	public function get_license() {
		return $this->services['license'];
	}

	public function get_license_setting() {
		return $this->services['license_setting'];
	}

	public function has_valid_license() {
		return $this->get_license()->is_valid();
	}

	public function get_license_page_url() {
		// Default to plugin settings URL if there's no license setting path.
		return ! empty( $this->data['license_setting_path'] ) ?
			admin_url( $this->data['license_setting_path'] ) :
			parent::get_settings_page_url();
	}

	public function get_legacy_db_prefix() {
		return $this->data['legacy_db_prefix'];
	}

}
