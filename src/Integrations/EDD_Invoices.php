<?php
namespace Barn2\Plugin\EDD_VAT\Integrations;

use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Registerable,
	Barn2\Plugin\EDD_VAT\Dependencies\Lib\Service,
	Barn2\Plugin\EDD_VAT\Util;

use function Barn2\Plugin\EDD_VAT\edd_eu_vat;

/**
 * Integrates the plugin with the EDD Invoices plugin.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class EDD_Invoices implements Registerable, Service {

	/**
	 * Holds our company details.
	 *
	 * @var Company_VAT
	 */
	protected $company;

	/**
	 * {@inheritdoc}
	 */
	public function register() {

		if ( ! class_exists( 'EDDInvoices' ) ) {
			return;
		}

		$this->company = Util::get_company_vat();

		add_filter( 'edd_template_paths', [ $this, 'register_template_stack' ] );

		$this->disable_default_templates();

		add_action( 'edd_invoices_invoice_contacts', [ $this, 'invoice_contacts' ] );
		add_action( 'edd_invoices_after_company_details', [ $this, 'add_company_details' ] );

		add_action( 'edd_invoices_invoice_items_table', [ $this, 'add_additional_info' ], 15 );
		add_action( 'edd_invoices_after_customer_details', [ $this, 'add_customer_vat' ] );
		add_action( 'edd_invoices_invoice_additional_info', [ $this, 'additional_info' ] );

	}

	/**
	 * Disable the original EDD Invoices templates actions.
	 *
	 * @return void
	 */
	public function disable_default_templates() {
		add_action(
			'after_setup_theme',
			function() {
				remove_action( 'edd_invoices_invoice_contacts', 'edd_invoices_do_invoice_contacts' );
				remove_action( 'edd_invoices_invoice_additional_info', 'edd_invoices_invoice_do_additional_info' );
			}
		);
	}

	/**
	 * Adds our custom templates directory to the stack.
	 * Priority is set to 59 so that it's detected before the EDD Invoices stack.
	 *
	 * @param array $paths Array of template directories.
	 * @return array
	 */
	public function register_template_stack( $paths ) {
		$paths[59] = edd_eu_vat()->get_template_path() . 'invoices/';

		return $paths;
	}

	/**
	 * Outputs the storefront and customer contact information.
	 *
	 * @since 1.2
	 * @param \EDD\Orders\Order|EDD_Payment $order The order/payment object.
	 * @return void
	 */
	public function invoice_contacts( $order ) {
		if ( Util::is_eu_payment( $order->id ) ) {
			edd_get_template_part( 'eu-invoice-contacts' );
		} else {
			edd_get_template_part( 'invoice-contacts' );
		}
	}

	/**
	 * Outputs the additional info section for the invoice.
	 *
	 * @param \EDD\Orders\Order|EDD_Payment $order The order/payment object.
	 * @return void
	 */
	public function additional_info( $order ) {
		edd_get_template_part( 'invoice-additional-info' );
	}

	/**
	 * Add website url and email address to the "invoices from" column.
	 *
	 * @param object $order
	 * @return void
	 */
	public function add_company_details( $order ) {
		$show_website = edd_get_option( 'edd_vat_show_website_address' ) === '1';
		$show_email   = edd_get_option( 'edd_vat_company_email', false );

		if ( $show_website || ! empty( $show_email ) ) : ?>
		<article style="padding:0; margin-top:18px;">
			<?php if ( ! empty( $show_email ) ) : ?>
			<div class="storefront__email">
				<a href="mailto:<?php echo esc_html( antispambot( $show_email ) ); ?>"><?php echo esc_html( $show_email ); ?></a>
			</div>
			<?php endif; ?>

			<?php if ( $show_website ) : ?>
			<div class="storefront__url">
				<a href="<?php echo esc_url( home_url() ); ?>"><?php echo esc_url( home_url() ); ?></a>
			</div>
			<?php endif; ?>
		</article>
			<?php
		endif;
	}

	/**
	 * Return our company name within the invoice
	 * instead of the original value.
	 *
	 * @param string $value
	 * @param string $key
	 * @param mixed $default
	 * @return string
	 */
	public function get_company_name( $value, $key, $default ) {
		if ( isset( $this->company->name ) ) {
			return esc_html( $this->company->name );
		}

		return $value;
	}

	/**
	 * Return our company address within the invoice
	 * instead of the original value.
	 *
	 * @param string $value
	 * @return string
	 */
	public function get_company_address( $value ) {

		if ( isset( $this->company->formatted_address ) ) {
			return wp_kses_post( $this->company->formatted_address );
		}

		return $value;

	}

	/**
	 * Return our company vat number within the invoice instead of the
	 * original value.
	 *
	 * @param string $value
	 * @return string
	 */
	public function get_company_vat( $value ) {
		if ( isset( $this->company->vat_number ) ) {
			return wp_kses_post( $this->company->vat_number );
		}

		return $value;
	}

	/**
	 * Add additional information to invoice table.
	 *
	 * @param object $order
	 * @return void
	 */
	public function add_additional_info( $order ) {
		$is_eu_payment = Util::is_eu_payment( $order->ID );
		$payment_vat   = Util::get_payment_vat( $order->ID );

		if ( ! $is_eu_payment ) {
			return;
		}

		$tax_rate = version_compare( EDD_VERSION, '3.0.0', '>=' ) ? $order->tax_rate : $order->tax_rate * 100;

		if ( Util::is_eu_payment( $order->ID ) && ( $payment_vat->is_reverse_charged || $order->tax > 0 ) ) :
			?>
			<div class="invoice-element">
			<?php if ( $payment_vat->is_reverse_charged ) : ?>
				<?php esc_html_e( 'VAT reverse charged', 'edd-eu-vat' ); ?>
			<?php elseif ( $order->tax > 0 ) : ?>
				<?php
					echo esc_html(
						sprintf(
						/* translators: %s is the VAT tax rate */
							__( 'VAT charged at %s%%', 'edd-eu-vat' ),
							$tax_rate
						)
					);
				?>
			<?php endif; ?>
			</div>
			<?php
		endif;
	}

	/**
	 * Add customer vat details inside the invoice details.
	 *
	 * @param object $order
	 * @return void
	 */
	public function add_customer_vat( $order ) {
		$is_eu_payment = Util::is_eu_payment( $order->ID );

		if ( ! $is_eu_payment ) {
			return;
		}

		$payment_vat         = Util::get_payment_vat( $order->ID );
		$billed_to_vat_lines = [];

		if ( ! empty( $payment_vat->vat_number ) ) {
			/* translators: %s is the VAT number */
			$billed_to_vat_lines[] = sprintf( __( 'VAT number: %s', 'edd-eu-vat' ), esc_html( $payment_vat->vat_number ) );

			if ( ! empty( $payment_vat->name ) ) {
				/* translators: %s is the registered company name */
				$billed_to_vat_lines[] = sprintf( __( 'Company name: %s', 'edd-eu-vat' ), esc_html( $payment_vat->name ) );
			}
			if ( ! empty( $payment_vat->address ) ) {
				/* translators: %s is the registered company address */
				$billed_to_vat_lines[] = sprintf( __( 'Address: %s', 'edd-eu-vat' ), esc_html( $payment_vat->address ) );
			}
		}

		if ( ! empty( $billed_to_vat_lines ) ) :
			?>
			<p class="edd-eu-vat-receipt-customer-vat">
				<strong><?php esc_html_e( 'VAT Details', 'edd-eu-vat' ); ?></strong><br/>
				<?php echo wp_kses( implode( '<br/>', $billed_to_vat_lines ), [ 'br' => [] ] ); ?>
			</p>
			<?php
		endif;
	}

}
