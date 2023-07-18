<?php

namespace Barn2\Plugin\EDD_VAT\Admin\Wizard\Steps;

use Barn2\Plugin\EDD_VAT\Dependencies\Setup_Wizard\Steps\Cross_Selling,
	Barn2\Plugin\EDD_VAT\Dependencies\Setup_Wizard\Util as Wizard_Util;

/**
 * Upsell Step.
 *
 * @package   Barn2/edd-eu-vat
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Upsell extends Cross_Selling {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'More', 'edd-eu-vat' ) );
		$this->set_description( __( 'Enhance your store with these fantastic plugins from Barn2.', 'edd-eu-vat' ) );
		$this->set_title( esc_html__( 'Extra features', 'edd-eu-vat' ) );
	}
}
