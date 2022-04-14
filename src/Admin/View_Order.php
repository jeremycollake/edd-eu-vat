<?php

namespace Barn2\Plugin\EDD_VAT\Admin;

use Barn2\VAT_Lib\Registerable,
	Barn2\VAT_Lib\Service;

/**
 * Handles display of VAT info on the Order details in the admin.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class View_Order implements Registerable, Service {

	private $template_path;

	/**
	 * Constructor
	 *
	 * @param string $template_path
	 */
	public function __construct( $template_path ) {
		$this->template_path = trailingslashit( $template_path );
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		// Easy Digital Downlaods - 3.0 Order details section
		add_filter( 'edd_get_order_details_sections', [ $this, 'add_order_details_section' ], 10, 1 );

		// Easy Digital Downloads - Payment view details
		if ( version_compare( EDD_VERSION, '3.0', '<' ) ) {
			add_action( 'edd_view_order_details_billing_after', [ $this, 'view_order_details_billing_after' ] );
		}

		// Easy Digital Downloads - Edited purchase
		add_action( 'edd_updated_edited_purchase', [ $this, 'updated_edited_purchase' ] );
	}

	/**
	 * Adds a section to the new Order Details metabox
	 *
	 * @param mixed $sections
	 * @return array $sections
	 */
	public function add_order_details_section( $sections ) {
		$sections[] = [
			'id'       => 'vat',
			'label'    => __( 'VAT', 'edd-eu-vat' ),
			'icon'     => 'id-alt',
			'callback' => [ $this, 'call_vat_template' ],
		];

		return $sections;
	}

	/**
	 * View order details billing after.
	 *
	 * @param mixed $payment_id
	 */
	public function view_order_details_billing_after( $payment_id ) {
		include $this->template_path . 'payment-postbox-vat.php';
	}

	/**
	 * Calls the old template into the new order details section (EDD 3.0)
	 *
	 * @param mixed $order
	 */
	public function call_vat_template( $order ) {
		$this->view_order_details_billing_after( $order->id );
	}

	/**
	 * Easy Digital Downloads updated edited purchase.
	 *
	 * @param int|string $payment_id
	 */
	public function updated_edited_purchase( $payment_id ) {
		$vat_number              = isset( $_POST['edd-payment-vat-number'] ) ? sanitize_text_field( $_POST['edd-payment-vat-number'] ) : '';
		$vat_number_valid        = isset( $_POST['edd-payment-vat-number-valid'] ) ? true : false;
		$vat_reverse_charged     = isset( $_POST['edd-payment-vat-reverse-charged'] ) ? true : false;
		$vat_company_name        = isset( $_POST['edd-payment-vat-company-name'] ) ? sanitize_text_field( $_POST['edd-payment-vat-company-name'] ) : '';
		$vat_company_address     = isset( $_POST['edd-payment-vat-company-address'] ) ? sanitize_text_field( $_POST['edd-payment-vat-company-address'] ) : '';
		$vat_consultation_number = isset( $_POST['edd-payment-vat-consultation-number'] ) ? sanitize_text_field( $_POST['edd-payment-vat-consultation-number'] ) : '';

		// Store the data on the payment meta
		edd_update_payment_meta( $payment_id, '_edd_payment_vat_number', $vat_number );
		edd_update_payment_meta( $payment_id, '_edd_payment_vat_number_valid', $vat_number_valid );
		edd_update_payment_meta( $payment_id, '_edd_payment_vat_reverse_charged', $vat_reverse_charged );
		edd_update_payment_meta( $payment_id, '_edd_payment_vat_company_name', $vat_company_name );
		edd_update_payment_meta( $payment_id, '_edd_payment_vat_company_address', $vat_company_address );
		edd_update_payment_meta( $payment_id, '_edd_payment_vat_consultation_number', $vat_consultation_number );
	}

}
