<?php

namespace Barn2\Plugin\EDD_VAT\Admin\Wizard\Steps;

use Barn2\Plugin\EDD_VAT\Dependencies\Setup_Wizard\Steps\Welcome;

/**
 * Welcome / License Step.
 *
 * @package   Barn2/edd-eu-vat
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class License_Verification extends Welcome {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_title( esc_html__('Welcome to Easy Digital Downloads EU VAT','edd-eu-vat' ) );
		$this->set_name( esc_html__( 'Welcome', 'edd-eu-vat' ) );
		$this->set_description( esc_html__( 'Start charging EU VAT in no time.', 'edd-eu-vat' ) );
		$this->set_tooltip( esc_html__( 'Use this setup wizard to enable EU VAT in your store. You can change these options later on the plugin settings page or by relaunching the setup wizard.', 'edd-eu-vat' ) );
	}
}
