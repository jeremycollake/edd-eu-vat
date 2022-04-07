<?php

namespace Barn2\VAT_Lib\Plugin;

/**
 * Something which listens for plugin activation or deactivation events.
 *
 * @package   Barn2\barn2-lib
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
interface Plugin_Activation_Listener {

	public function on_activate();

	public function on_deactivate();

}
