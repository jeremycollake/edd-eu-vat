<?php

namespace Barn2\Plugin\EDD_VAT\Admin\Wizard\Steps;

use Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Steps\Ready;

/**
 * Completed Step.
 *
 * @package   Barn2/edd-eu-vat
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Completed extends Ready {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'Ready', 'edd-eu-vat' ) );
		$this->set_title( esc_html__( 'Complete Setup', 'edd-eu-vat' ) );
		$this->set_description( esc_html__( 'Congratulations, you have finished setting up the plugin! Your customers from EU countries will now automatically be charged the correct rate of VAT, and can reverse charge the VAT on the checkout if they have a valid European VAT number.', 'edd-eu-vat' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function submit() {
		check_ajax_referer( 'barn2_setup_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->send_error( esc_html__( 'You are not authorized.', 'edd-eu-vat' ) );
		}

		update_option( 'edd_vat_plugin_setup_completed', true );
	}
}
