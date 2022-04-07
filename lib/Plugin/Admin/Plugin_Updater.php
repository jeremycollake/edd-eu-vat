<?php
namespace Barn2\VAT_Lib\Plugin\Admin;

use Barn2\VAT_Lib\Registerable,
	Barn2\VAT_Lib\Plugin\Licensed_Plugin,
	Barn2\VAT_Lib\Plugin\License\License_API;

/**
 * Handles plugin update checks for our EDD plugins.
 *
 * @package   Barn2\barn2-lib
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Plugin_Updater implements Registerable {

	/**
	 * @var Licensed_Plugin The plugin we're managing updates for.
	 */
	private $plugin;

	/**
	 * @var License_API The licensing API to fetch plugin updates from.
	 */
	private $license_api;

	/**
	 * @var string Internal key used for caching
	 */
	private $cache_key = null;

	public function __construct( Licensed_Plugin $plugin, License_API $license_api ) {
		$this->plugin      = $plugin;
		$this->license_api = $license_api;
	}

	public function register() {
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ] );
			add_filter( 'plugins_api', [ $this, 'get_plugin_details' ], 10, 3 );
			add_action( 'in_plugin_update_message-' . $this->plugin->get_basename(), [ $this, 'update_available_notice' ], 10, 2 );
		}
	}

	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * This function dives into the update API just when WordPress creates its update array,
	 * then adds a custom API call and injects the custom plugin data retrieved from the API.
	 * It is reassembled from parts of the native WordPress plugin update code.
	 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
	 *
	 * @param array   $transient_data Plugin update object built by WordPress.
	 * @return array Modified update array with custom plugin data.
	 */
	public function check_update( $transient_data ) {
		global $pagenow;

		if ( ! is_object( $transient_data ) ) {
			$transient_data = new \stdClass;
		}

		if ( 'plugins.php' == $pagenow && is_multisite() ) {
			return $transient_data;
		}

		$basename = $this->plugin->get_basename();

		// First check if plugin info already exists in the WP transient.
		if ( ! empty( $transient_data->response ) && ! empty( $transient_data->response[$basename] ) ) {
			return $transient_data;
		}

		$latest_version = $this->get_latest_version();

		if ( false !== $latest_version && isset( $latest_version->new_version ) ) {
			if ( version_compare( $this->plugin->get_version(), $latest_version->new_version, '<' ) ) {
				$transient_data->response[$basename] = $this->format_version_info_for_plugin_update( $latest_version );
			}

			$transient_data->last_checked       = time();
			$transient_data->checked[$basename] = $this->plugin->get_version();
		}

		return $transient_data;
	}

	/**
	 * Updates information on the "View version x.x details" page with custom data.
	 *
	 * @param mixed   $data
	 * @param string  $action
	 * @param object  $args
	 * @return object $data
	 */
	public function get_plugin_details( $data, $action = '', $args = null ) {
		if ( 'plugin_information' !== $action ) {
			return $data;
		}

		if ( ! isset( $args->slug ) || ( $args->slug !== $this->plugin->get_slug() ) ) {
			return $data;
		}

		if ( ! ( $version_info = $this->get_latest_version() ) ) {
			return $data;
		}

		return $this->format_version_info_for_plugin_details_modal( $version_info );
	}

	public function update_available_notice( $plugin_data, $response ) {
		// Add note about license key if no automatic update available (i.e. no update package).
		if ( empty( $response->package ) ) {
			$license_page        = $this->plugin->get_license_page_url();
			$settings_link_open  = $license_page ? '<a href="' . esc_url( $license_page ) . '">' : '';
			$settings_link_close = $license_page ? '</a>' : '';

			printf( ' <em>%s</em>', sprintf(
					__( 'Activate %1$syour license key%2$s to enable updates.', 'edd-eu-vat' ),
					$settings_link_open,
					$settings_link_close
			) );
		}
	}

	/**
	 * Remove unrequired properties from EDD version info when storing in the 'update_plugins' transient.
	 *
	 * @param object $version_info The version info from EDD
	 * @return object The updated version info
	 */
	private function format_version_info_for_plugin_update( $version_info ) {
		unset( $version_info->name, $version_info->homepage, $version_info->version, $version_info->stable_version, $version_info->download_link,
			$version_info->sections, $version_info->rating, $version_info->num_ratings, $version_info->last_updated );

		// Make sure the plugin property is set to the plugin's name/location. See issue 1463 on Software Licensing's GitHub repo.
		$version_info->plugin = $this->plugin->get_basename();

		// Add an ID for the update details.
		$version_info->id = 'barn2-plugin-' . $this->plugin->get_id();

		// Check the license before returning.
		return $this->maybe_disable_automatic_update( $version_info );
	}

	private function format_version_info_for_plugin_details_modal( $version_info ) {
		return $this->maybe_disable_automatic_update( $version_info );
	}

	private function maybe_disable_automatic_update( $version_info ) {
		if ( empty( $version_info->package ) ) {
			return $version_info;
		}

		// Prevent automatic plugin update if license is invalid. Clearing the package URL will do this.
		if ( ! $this->plugin->get_license()->is_valid() || ! apply_filters( 'barn2_plugin_allow_automatic_update', true, $this->plugin ) ) {
			$version_info->package = '';
		}
		return $version_info;
	}

	private function get_latest_version() {
		// First check the cache.
		$version_info = $this->get_cached_version_info();

		if ( false === $version_info ) {
			// Nothing in cache, so get latest version from API.
			$api_result = $this->license_api->get_latest_version(
				$this->plugin->get_license()->get_license_key(),
				$this->plugin->get_id(),
				$this->plugin->get_license()->get_active_url(),
				$this->plugin->get_slug(),
				$this->is_beta_testing()
			);

			if ( $api_result->success ) {
				$version_info = $api_result->response;
				$this->set_cached_version_info( $version_info );
			}
		}

		return $version_info;
	}

	private function get_cached_version_info() {
		$cache = get_transient( $this->get_cache_key() );
		return $cache ? $cache : false;
	}

	private function set_cached_version_info( $version_info ) {
		// We cache the version info for 4 hours, to reduce the number of API requests.
		set_transient( $this->get_cache_key(), $version_info, 4 * HOUR_IN_SECONDS );
	}

	private function get_cache_key() {
		if ( null === $this->cache_key ) {
			$this->cache_key = 'barn2_plugin_update_' . md5( serialize( $this->plugin->get_id() . $this->plugin->get_license()->get_license_key() . $this->is_beta_testing() ) );
		}

		return $this->cache_key;
	}

	private function is_beta_testing() {
		return apply_filters( 'barn2_plugin_is_beta_testing_' . $this->plugin->get_slug(), false );
	}

}
