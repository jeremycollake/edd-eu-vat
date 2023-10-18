<?php

namespace Barn2\Plugin\EDD_VAT\Admin;

use Barn2\Plugin\EDD_VAT\Util as EDD_VATUtil;
use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Registerable,
	Barn2\Plugin\EDD_VAT\Dependencies\Lib\Service,
	Barn2\Plugin\EDD_VAT\Dependencies\Lib\Util,
	Barn2\Plugin\EDD_VAT\Dependencies\Lib\Plugin\Licensed_Plugin,
	Barn2\Plugin\EDD_VAT\Dependencies\Lib\Plugin\License\Admin\License_Setting;

/**
 * Registrations of the EDD settings page.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class Settings implements Registerable, Service {

	/**
	 * The licensed plugin object.
	 *
	 * @var Licensed_Plugin
	 */
	private $plugin;

	/**
	 * The license setting object.
	 *
	 * @var License_Setting
	 */
	private $license_setting;

	/**
	 * Constructor
	 *
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( Licensed_Plugin $plugin ) {
		$this->plugin          = $plugin;
		$this->license_setting = $plugin->get_license_setting();
	}

	/**
	 * Register hooks and filters
	 */
	public function register() {
		add_action( 'admin_footer', [ $this, 'custom_settings_header' ] );
		add_action( 'edd_settings_tab_bottom_extensions_vat', [ $this, 'add_promo' ] );
		add_filter( 'edd_settings_sections_extensions', [ $this, 'add_settings_section' ], 20 );
		add_filter( 'edd_settings_extensions', [ $this, 'add_settings' ] );
		add_filter( 'edd_settings_extensions-vat_sanitize', [ $this, 'save_license_key' ] );
	}

	/**
	 * Output a customised settings header
	 */
	public function custom_settings_header() {
		$screen = get_current_screen();

		if ( ! $screen || $screen->id !== 'download_page_edd-settings' || ! isset( $_GET['section'] ) || $_GET['section'] !== 'vat' ) {
			return;
		}
		?>
		<style>
			p.barn2-support-links { font-size: 13px !important; }

			.barn2-plugins-promo, #wpfooter { display: none }
			@media only screen and (min-width : 1200px) {
				.edd-settings-content table:nth-of-type(2) {
					float: left;
					width: 70%;
				}

				.barn2-plugins-promo {
					display: block;
					float: right;
					width: 28%;
					margin-left: 2%;
				}
			}
		</style>

		<script>
			jQuery( '.barn2-wiz-restart-btn' ).on( 'click', function( e ) {
				return confirm( '<?php echo esc_html( sprintf( __( 'Warning: This will overwrite your existing settings for %s. Are you sure you want to continue?', 'edd-eu-vat' ), $this->plugin->get_name() ) ); ?>' );
			});
		</script>
		<?php
	}

	/**
	 * Outputs the plugin promo
	 */
	public function add_promo() {
		do_action( 'barn2_after_plugin_settings', $this->plugin->get_id() );
	}

	/**
	 * Register the VAT settings section.
	 *
	 * @param array $sections
	 * @return array
	 */
	public function add_settings_section( $sections ) {
		$sections['vat'] = __( 'EU VAT', 'edd-eu-vat' );
		return $sections;
	}

	/**
	 * Register the VAT settings.
	 *
	 * @param array $settings
	 * @return array
	 */
	public function add_settings( $settings ) {

		$vat_settings = [
			'vat' => [
				[
					'id'   => 'edd_vat_settings',
					'name' => '<h3>' . __( 'EU VAT', 'edd-eu-vat' ) . '</h3>',
					'desc' => $this->get_help_links(),
					'type' => 'descriptive_text'
				],
				$this->license_setting->get_license_key_setting(),
				$this->license_setting->get_license_override_setting(),
				[
					'id'   => 'edd_vat_purchase_receipt',
					'name' => __( 'Order Details Page', 'edd-eu-vat' ),
					'desc' => __( 'Include VAT information on the order details page.', 'edd-eu-vat' ),
					'type' => 'checkbox',
				],
				[
					'id'   => 'edd_vat_reverse_charge_base_country',
					'name' => __( 'Reverse Charge in Home Country?', 'edd-eu-vat' ),
					'desc' => __( 'If your store is based in the EU or the UK, check this to reverse charge VAT for customers with a valid VAT number who are in your home country.', 'edd-eu-vat' ),
					'type' => 'checkbox',
				],
				[
					'id'   => 'edd_vat_show_website_address',
					'name' => __( 'Show website address?', 'edd-eu-vat' ),
					'desc' => __( 'Check this box if you would like your website address to be shown on the invoice.', 'edd-eu-vat' ),
					'type' => 'checkbox',
				],
				[
					'id'   => 'edd_vat_address_header',
					'name' => '<h3>' . __( 'Company Information', 'edd-eu-vat' ) . '</h3>',
					'desc' => __( 'The following information about your company will be displayed on the order details page and purchase receipt email.', 'edd-eu-vat' ),
					'type' => 'descriptive_text'
				],
				[
					'id'   => 'edd_vat_company_name',
					'name' => __( 'Company Name', 'edd-eu-vat' ),
					'desc' => __( 'Enter your company name.', 'edd-eu-vat' ),
					'type' => 'text',
				],
				[
					'id'   => 'edd_vat_company_email',
					'name' => __( 'Company Email', 'edd-eu-vat' ),
					'desc' => __( 'Enter the email address that will appear on the invoice.', 'edd-eu-vat' ),
					'type' => 'text',
				],
				[
					'id'   => 'edd_vat_number',
					'name' => __( 'EU VAT Number', 'edd-eu-vat' ),
					'desc' => __( 'Enter the registered EU VAT number of your company.', 'edd-eu-vat' ),
					'type' => 'text',
				],
				[
					'id'   => 'edd_uk_vat_number',
					'name' => __( 'UK VAT Number', 'edd-eu-vat' ),
					'desc' => __( 'Enter the registered UK VAT number of your company.', 'edd-eu-vat' ),
					'type' => 'text',
				],
				[
					'id'   => 'edd_vat_address_line_1',
					'name' => __( 'Address Line 1', 'edd-eu-vat' ),
					'desc' => __( 'Enter line 1 of your company\'s registered VAT address.', 'edd-eu-vat' ),
					'type' => 'text',
				],
				[
					'id'   => 'edd_vat_address_line_2',
					'name' => __( 'Address Line 2', 'edd-eu-vat' ),
					'desc' => __( 'Enter line 2 of your company\'s registered VAT address if required.', 'edd-eu-vat' ),
					'type' => 'text',
				],
				[
					'id'   => 'edd_vat_address_city',
					'name' => __( 'City / State', 'edd-eu-vat' ),
					'desc' => __( 'Enter the city / state of your company\'s registered VAT address.', 'edd-eu-vat' ),
					'type' => 'text',
				],
				[
					'id'   => 'edd_vat_address_code',
					'name' => __( 'Zip / Postal Code', 'edd-eu-vat' ),
					'desc' => __( 'Enter the zip / postal code of your company\'s registered VAT address.', 'edd-eu-vat' ),
					'type' => 'text',
				],
				[
					'id'          => 'edd_vat_address_invoice',
					'name'        => __( 'Country', 'edd-eu-vat' ),
					'desc'        => __( 'Select the country where your business is based. This will be used on invoices.', 'edd-eu-vat' ),
					'type'        => 'select',
					'options'     => edd_get_country_list(),
					'chosen'      => true,
					'placeholder' => __( 'Select a country', 'edd-eu-vat' ),
					'std'         => edd_get_shop_country(),
					'data'        => [
						'nonce' => wp_create_nonce( 'edd-country-field-nonce' )
					],
				],
				[
					'id'          => 'edd_vat_address_country',
					'name'        => __( 'Country of VAT registration', 'edd-eu-vat' ),
					'desc'        => __( 'Select the country of your company\'s registered VAT address.', 'edd-eu-vat' ),
					'type'        => 'select',
					'options'     => EDD_VATUtil::get_country_list(),
					'chosen'      => true,
					'placeholder' => __( 'Select a country', 'edd-eu-vat' ),
					'tooltip_title' => __( 'Country setup', 'edd-eu-vat' ),
					'tooltip_desc' => __( 'Select the country that issued your company’s VAT number. If you are based in Northern Ireland and have a VAT number beginning with XI then you should select “Northern Ireland”. If your VAT number begins with EU then you should select “EU MOSS Number”', 'edd-eu-vat' ),
					'std'         => edd_get_shop_country(),
					'data'        => [
						'nonce' => wp_create_nonce( 'edd-country-field-nonce' )
					],
				],
			]
		];

		// Remove settings if the plugin isn't enabled. These settings can't be used anywhere else.
		if ( ! class_exists( '\EDDInvoices' ) ) {
			$to_remove = [ 'edd_vat_show_website_address', 'edd_vat_company_email' ];

			foreach ( $vat_settings['vat'] as $key => $setting ) {
				if ( isset( $setting['id'] ) && in_array( $setting['id'], $to_remove, true ) ) {
					unset( $vat_settings['vat'][$key] );
				}
			}
		}

		return array_merge( $settings, $vat_settings );
	}

	/**
	 * Save the license key when the plugin settings are saved.
	 *
	 * @param array $input The EDD settings to save (will not include the license key).
	 * @return array The sanitized settings.
	 */
	public function save_license_key( $input ) {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return $input;
		}

		$this->license_setting->save_posted_license_key();

		return $input;
	}

	/**
	 * Gets the Barn2 Help links
	 *
	 * @return string
	 */
	private function get_help_links() {
		return sprintf(
			'<p class="barn2-support-links">%s | %s | %s</p>',
			Util::format_link( $this->plugin->get_documentation_url(), __( 'Documentation', 'edd-eu-vat' ) ),
			Util::format_link( $this->plugin->get_support_url(), __( 'Support', 'edd-eu-vat' ) ),
			sprintf(
				'<a class="barn2-wiz-restart-btn" href="%s">%s</a>',
				add_query_arg( [ 'page' => $this->plugin->get_slug() . '-setup-wizard' ], admin_url( 'admin.php' ) ),
				__( 'Setup wizard', 'edd-eu-vat' )
			)
		);
	}

}
