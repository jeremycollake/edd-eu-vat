<?php
namespace Barn2\Plugin\EDD_VAT\Integrations;

use Barn2\VAT_Lib\Registerable,
	EDD_Payment,
	EDD_Subscription;

/**
 * Handles integration with the EDD Recurring plugin.
 *
 * @package   Barn2\edd-eu-vat
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class EDD_Recurring implements Registerable {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		// Add VAT to EDD subscription payment.
		add_action( 'edd_recurring_add_subscription_payment', [ $this, 'insert_subscription_payment' ], 5, 2 );
	}

	/**
	 * Add VAT details to EDD subscription payment.
	 *
	 * @param EDD_Payment $payment
	 * @param EDD_Subscription $subscription
	 */
	public function insert_subscription_payment( EDD_Payment $payment, EDD_Subscription $subscription ) {

		if ( empty( $subscription->parent_payment_id ) ) {
			return;
		}

		$payment_id = $payment->ID;

		edd_update_payment_meta( $payment_id, '_edd_payment_vat_reverse_charged', edd_get_payment_meta( $subscription->parent_payment_id, '_edd_payment_vat_reverse_charged', true ) );
		edd_update_payment_meta( $payment_id, '_edd_payment_vat_is_eu', edd_get_payment_meta( $subscription->parent_payment_id, '_edd_payment_vat_is_eu', true ) );

		$vat_number = edd_get_payment_meta( $subscription->parent_payment_id, '_edd_payment_vat_number', true );

		if ( $vat_number ) {
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_number', $vat_number );
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_number_valid', edd_get_payment_meta( $subscription->parent_payment_id, '_edd_payment_vat_number_valid', true ) );
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_company_name', edd_get_payment_meta( $subscription->parent_payment_id, '_edd_payment_vat_company_name', true ) );
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_company_address', edd_get_payment_meta( $subscription->parent_payment_id, '_edd_payment_vat_company_address', true ) );
		}
	}

}
