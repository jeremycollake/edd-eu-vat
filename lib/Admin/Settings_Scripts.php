<?php
namespace Barn2\VAT_Lib\Admin;

use Barn2\VAT_Lib\Registerable,
	Barn2\VAT_Lib\Plugin\Plugin;

/**
 * Registers the tooltip assets
 *
 * @package   Barn2\barn2-lib
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 * @version   1.0
 */
class Settings_Scripts implements Registerable {

	/**
	 * @var Plugin The plugin object.
	 */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function register() {
		add_action( 'admin_enqueue_scripts', [ $this, 'load_scripts' ], 5 );
	}

	public function load_scripts() {
		if ( ! wp_script_is( 'barn2-tiptip', 'registered' ) ) {
			wp_register_script(
				'barn2-tiptip',
				plugins_url( 'lib/assets/js/jquery-tiptip/jquery.tipTip.min.js', $this->plugin->get_file() ),
				[ 'jquery' ],
				$this->plugin->get_version(),
				true
			);
		}
	}

}
