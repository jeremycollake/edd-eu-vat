<?php
namespace Barn2\Plugin\EDD_VAT\Integrations;

use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Registerable;
use EDD_Payment;
use EDD_Subscription;

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

		add_filter( 'edd_recurring_purchase_data', [ $this, 'maybe_add_vat_flag_to_purchase_data' ], 10, 1 );
		add_filter( 'edd_recurring_subscription_recurring_amounts', [ $this, 'update_recurring_subscription_amounts' ], 10, 2 );
	}

	/**
	 * Add VAT details to EDD subscription payment.
	 *
	 * @param EDD_Payment $payment
	 * @param EDD_Subscription $subscription
	 */
	public function insert_subscription_payment( EDD_Payment $payment, EDD_Subscription $subscription ) {
		/**
		 * Filter the parent payment ID to use for the subscription payment.
		 * This allows other plugins to modify the payment ID if required.
		 *
		 * @param int $parent_payment_id The parent payment ID.
		 * @param EDD_Payment $payment The payment object.
		 * @param EDD_Subscription $subscription The subscription object.
		 * @return int
		 */
		$parent_payment_id = apply_filters( 'edd_vat_recurring_subscription_parent_payment_id', $subscription->parent_payment_id, $payment, $subscription );

		if ( empty( $parent_payment_id ) ) {
			return;
		}

		/**
		 * Filter the payment ID to use for the subscription payment.
		 * This allows other plugins to modify the payment ID if required.
		 *
		 * @param int $payment_id The payment ID.
		 * @param EDD_Payment $payment The payment object.
		 * @param EDD_Subscription $subscription The subscription object.
		 * @return int
		 */
		$payment_id = apply_filters( 'edd_vat_recurring_insert_subscription_payment_id', $payment->ID, $payment, $subscription );

		edd_update_payment_meta( $payment_id, '_edd_payment_vat_reverse_charged', edd_get_payment_meta( $parent_payment_id, '_edd_payment_vat_reverse_charged', true ) );
		edd_update_payment_meta( $payment_id, '_edd_payment_vat_is_eu', edd_get_payment_meta( $parent_payment_id, '_edd_payment_vat_is_eu', true ) );

		$vat_number = edd_get_payment_meta( $parent_payment_id, '_edd_payment_vat_number', true );

		if ( $vat_number ) {
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_number', $vat_number );
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_number_valid', edd_get_payment_meta( $parent_payment_id, '_edd_payment_vat_number_valid', true ) );
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_company_name', edd_get_payment_meta( $parent_payment_id, '_edd_payment_vat_company_name', true ) );
			edd_update_payment_meta( $payment_id, '_edd_payment_vat_company_address', edd_get_payment_meta( $parent_payment_id, '_edd_payment_vat_company_address', true ) );
		}
	}

	/**
	 * When VAT is applied to the parent order, mark it on cart items so VAT can be removed from recurring subscriptions.
	 * Filter for `edd_recurring_purchase_data`
	 *
	 * @param array $purchase_data the entire EDD checkout session, should already be populated with a payment key
	 */
	public function maybe_add_vat_flag_to_purchase_data( $purchase_data ) {

		if ( empty( $purchase_data['purchase_key'] ) ) {
			return $purchase_data;
		}

		$order = edd_get_order_by( 'payment_key', $purchase_data['purchase_key'] );
		if ( empty( $order ) || is_wp_error( $order ) ) {
			return $purchase_data;
		}

		$is_reverse_charged = filter_var( edd_get_payment_meta( $order->id, '_edd_payment_vat_reverse_charged', true ), FILTER_VALIDATE_BOOL );
		if ( ! $is_reverse_charged ) {
			return $purchase_data;
		}

		foreach( $purchase_data['cart_details'] as $cart_index => $cart_item ) {
			$purchase_data['cart_details'][$cart_index]['_vat_reverse_charged'] = $is_reverse_charged;
		}

		return $purchase_data;

	}

	/**
	 * Update subscription amounts to remove VAT if applicable.
	 * Filter for `edd_recurring_subscription_recurring_amounts`
	 *
	 * @param array $recurring_amounts the values of the recurring payment being returned
	 * @param array $item the cart item being setup as a recurring payment
	 */
	public function update_recurring_subscription_amounts( $recurring_amounts, $item ) {

		if ( ! empty( $item['_vat_reverse_charged'] ) ) {
			$recurring_amounts['amount'] -= ( $recurring_amounts['tax'] ?? 0 );
			$recurring_amounts['tax']     = 0;
		}

		return $recurring_amounts;
	}
}
