<?php
/**
 * The main file for the Easy Digital Downloads EU VAT plugin. Included by WordPress during the bootstrap process.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 *
 * @wordpress-plugin
 * Plugin Name:         Easy Digital Downloads - EU VAT
 * Plugin URI:          https://barn2.com/wordpress-plugins/easy-digital-downloads-eu-vat/
 * Version:             1.5.23
 * Description:         Adds EU VAT support to Easy Digital Downloads.
 * Author:              Barn2 Plugins
 * Author URI:          https://barn2.com
 * Text Domain:         edd-eu-vat
 * Domain Path:         /languages
 * Requires at least:   6.0
 * Tested up to:        6.4.2
 * Requires PHP:        7.4
 *
 * Copyright:           Barn2 Media Ltd
 * License:             GNU General Public License v3.0
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.html
 */
namespace Barn2\Plugin\EDD_VAT;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PLUGIN_VERSION = '1.5.23';
const PLUGIN_FILE    = __FILE__;

// Include autoloader.
require_once __DIR__ . '/vendor/autoload.php';

// Helper function to access the shared plugin instance.
function edd_eu_vat() {
	return Plugin_Factory::create( PLUGIN_FILE, PLUGIN_VERSION );
}

// Load the plugin.
edd_eu_vat()->register();
