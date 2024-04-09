<?php

namespace Barn2\Plugin\EDD_VAT;

/**
 * Factory to return the shared plugin instance.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class Plugin_Factory {

	private static $plugin = null;

	/**
	 * Creates or returns the plugin instance.
	 *
	 * @param string $file
	 * @param float $version
	 * @return Plugin
	 */
	public static function create( $file, $version ) {
		if ( null === self::$plugin ) {
			self::$plugin = new Plugin( $file, $version );
		}
		return self::$plugin;
	}

}
