<?php

namespace Barn2\VAT_Lib;

use function Barn2\Plugin\WC_Fast_Cart\wfc;
use function Barn2\Plugin\WC_Product_Table\wpt;
use function Barn2\Plugin\WC_Protected_Categories\wpc;
use function Barn2\Plugin\WC_Quick_View_Pro\wqv;
use function Barn2\Plugin\WC_Restaurant_Ordering\wro;

/**
 * Utility functions for Barn2 plugins.
 *
 * @package   Barn2\barn2-lib
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 * @version   1.5.3
 */
class Util {

	const BARN2_URL          = 'https://barn2.com';
	const EDD_STORE_URL      = 'https://barn2.com';
	const KNOWLEDGE_BASE_URL = 'https://barn2.com';

	/**
	 * Formats a HTML link to a path on the Barn2 site.
	 *
	 * @param string $relative_path The path relative to https://barn2.com.
	 * @param string $link_text     The link text.
	 * @param boolean $new_tab      Whether to open the link in a new tab.
	 * @return string The hyperlink.
	 */
	public static function barn2_link( $relative_path, $link_text = '', $new_tab = false ) {
		if ( empty( $link_text ) ) {
			$link_text = __( 'Read more', 'edd-eu-vat' );
		}
		return self::format_link( self::barn2_url( $relative_path ), esc_html( $link_text ), $new_tab );
	}

	public static function barn2_url( $relative_path ) {
		return esc_url( trailingslashit( self::BARN2_URL ) . ltrim( $relative_path, '/' ) );
	}

	public static function format_barn2_link_open( $relative_path, $new_tab = false ) {
		return self::format_link_open( self::barn2_url( $relative_path ), $new_tab );
	}

	public static function format_link( $url, $link_text, $new_tab = false ) {
		return sprintf( '%1$s%2$s</a>', self::format_link_open( $url, $new_tab ), $link_text );
	}

	public static function format_link_open( $url, $new_tab = false ) {
		$target = $new_tab ? ' target="_blank"' : '';
		return sprintf( '<a href="%1$s"%2$s>', esc_url( $url ), $target );
	}

	/**
	 * Format a Barn2 store URL.
	 *
	 * @param string $relative_path The relative path
	 */
	public static function store_url( $relative_path ) {
		return self::EDD_STORE_URL . '/' . ltrim( $relative_path, ' /' );
	}

	public static function format_store_link( $relative_path, $link_text, $new_tab = true ) {
		return self::format_link( self::store_url( $relative_path ), $link_text, $new_tab );
	}

	public static function format_store_link_open( $relative_path, $new_tab = true ) {
		return self::format_link_open( self::store_url( $relative_path ), $new_tab );
	}

	public static function get_add_to_cart_url( $download_id, $price_id = 0, $discount_code = '' ) {
		$args = [
			'edd_action'  => 'add_to_cart',
			'download_id' => (int) $download_id
		];
		if ( $price_id ) {
			$args['edd_options[price_id]'] = (int) $price_id;
		}
		if ( $discount_code ) {
			$args['discount'] = $discount_code;
		}

		return self::store_url( '?' . http_build_query( $args ) );
	}

	/**
	 * Returns true if the current request is a WP admin request.
	 *
	 * @return bool true if the current page is in WP admin
	 */
	public static function is_admin() {
		return is_admin();
	}

	/**
	 * Returns true if the current request is a front-end request, e.g. viewing a page or post.
	 *
	 * @return bool true if the current page is front-end
	 */
	public static function is_front_end() {
		return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
	}

	/**
	 * Returns true if WooCommerce is active.
	 *
	 * @return bool true if active
	 */
	public static function is_woocommerce_active() {
		return class_exists( '\WooCommerce' );
	}

	/**
	 * Returns true if WooCommerce Product Addons is active.
	 *
	 * @return bool true if active
	 */
	public static function is_product_addons_active() {
		return class_exists( '\WC_Product_Addons' );
	}

	/**
	 * Returns true if EDD is active.
	 *
	 * @return bool true if active
	 */
	public static function is_edd_active() {
		return class_exists( '\Easy_Digital_Downloads' );
	}

	/**
	 * Returns true if Advanced Custom Fields or Advanced Custom Fields Pro is active.
	 *
	 * @return bool true if active
	 */
	public static function is_acf_active() {
		return class_exists( '\ACF' );
	}

	/**
	 * Returns true if the plugin instance returned by $function is an active Barn2 plugin.
	 *
	 * @since 1.5.3
	 * @param string $function The function that returns the plugin instance
	 * @return bool true if active
	 */
	public static function is_barn2_plugin_active( $function ) {
		if ( function_exists( $function ) ) {
			$instance = $function();

			return method_exists( (object) $instance, 'has_valid_license' ) && $instance->has_valid_license();
		}

		return false;
	}

	/**
	 * Returns true if WooCommerce Protected Categories is active and has a valid license.
	 *
	 * @deprecated 1.5.3 Use `is_barn2_plugin_active( '\Barn2\Plugin\WC_Protected_Categories\wpc' )` instead
	 * @return bool true if active
	 */
	public static function is_protected_categories_active() {
		return self::is_barn2_plugin_active( '\Barn2\Plugin\WC_Protected_Categories\wpc' );
	}

	/**
	 * Returns true if WooCommerce Product Table is active and has a valid license.
	 *
	 * @deprecated 1.5.3 Use `is_barn2_plugin_active( '\Barn2\Plugin\WC_Product_Table\wpt' )` instead
	 * @return bool true if active
	 */
	public static function is_product_table_active() {
		return self::is_barn2_plugin_active( '\Barn2\Plugin\WC_Product_Table\wpt' );
	}

	/**
	 * Returns true if WooCommerce Quick View Pro is active and has a valid license.
	 *
	 * @deprecated 1.5.3 Use `is_barn2_plugin_active( '\Barn2\Plugin\WC_Quick_View_Pro\wqv' )` instead
	 * @return bool true if active
	 */
	public static function is_quick_view_pro_active() {
		return self::is_barn2_plugin_active( '\Barn2\Plugin\WC_Quick_View_Pro\wqv' );
	}

	/**
	 * Returns true if WooCommerce Restaurant Ordering is active and has a valid license.
	 *
	 * @deprecated 1.5.3 Use `is_barn2_plugin_active( '\Barn2\Plugin\WC_Restaurant_Ordering\wro' )` instead
	 * @return bool true if active
	 */
	public static function is_restaurant_ordering_active() {
		return self::is_barn2_plugin_active( '\Barn2\Plugin\WC_Restaurant_Ordering\wro' );
	}

	/**
	 * Returns true if WooCommerce Fast Cart is active and has a valid license.
	 *
	 * @deprecated 1.5.3 Use `is_barn2_plugin_active( '\Barn2\Plugin\WC_Fast_Cart\wfc' )` instead
	 * @return bool true if active
	 */
	public static function is_fast_cart_active() {
		return self::is_barn2_plugin_active( '\Barn2\Plugin\WC_Fast_Cart\wfc' );
	}

	/**
	 * Get the script suffix used when registering/queuing JS and CSS, based on SCRIPT_DEBUG.
	 *
	 * @return string Returns '' or '.min'
	 */
	public static function get_script_suffix() {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}

	/**
	 * Register the Services in the given array.
	 *
	 * @param array $services The services to register
	 */
	public static function register_services( $services ) {
		array_map(
			function ( $service ) {
				if ( ( $service instanceof Conditional ) && ! $service->is_required() ) {
					return;
				}
				if ( $service instanceof Registerable ) {
					$service->register();
				}
				if ( $service instanceof Schedulable ) {
					$service->schedule();
				}
			},
			$services
		);
	}

	/**
	 * Format a Barn2 store URL.
	 *
	 * @param string $relative_path The relative path
	 * @deprecated 1.5 Renamed store_url
	 */
	public static function format_store_url( $relative_path ) {
		return self::store_url( $relative_path );
	}

	/**
	 * Retrieves an array of internal WP dependencies for bundled JS files.
	 *
	 * @param Barn2\VAT_Lib\Plugin $plugin
	 * @param string $filename
	 * @return array
	 */
	public static function get_script_dependencies( $plugin, $filename ) {
		$script_dependencies_file = $plugin->get_dir_path() . 'assets/js/wp-dependencies.json';
		$script_dependencies      = file_exists( $script_dependencies_file ) ? file_get_contents( $script_dependencies_file ) : false;

		if ( $script_dependencies === false ) {
			return [];
		}

		$script_dependencies = json_decode( $script_dependencies, true );

		if ( ! isset( $script_dependencies[ $filename ] ) ) {
			return [];
		}

		return $script_dependencies[ $filename ];
	}

	/**
	 * Create a page and store the ID in an option. (adapted from WooCommerce)
	 *
	 * @param mixed  $slug Slug for the new page.
	 * @param string $option Option name to store the page's ID.
	 * @param string $page_title (default: '') Title for the new page.
	 * @param string $page_content (default: '') Content for the new page.
	 * @param int    $post_parent (default: 0) Parent for the new page.
	 * @return int page ID.
	 */
	public static function create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
		global $wpdb;

		$slug = esc_sql( $slug );
		$option_value = get_option( $option );

		if ( $option_value > 0 ) {
			$page_object = get_post( $option_value );

			if ( $page_object && 'page' === $page_object->post_type && ! in_array( $page_object->post_status, [ 'pending', 'trash', 'future', 'auto-draft' ], true ) ) {
				// Valid page is already in place.
				return $page_object->ID;
			}
		}

		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$shortcode        = str_replace( [ '<!-- wp:shortcode -->', '<!-- /wp:shortcode -->' ], '', $page_content );
			$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$shortcode}%" ) );
		} else {
			// Search for an existing page with the specified page slug.
			$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
		}

		if ( $valid_page_found ) {
			if ( $option ) {
				update_option( $option, $valid_page_found );
			}
			return $valid_page_found;
		}

		// Search for a matching valid trashed page.
		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
		} else {
			// Search for an existing page with the specified page slug.
			$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
		}

		if ( $trashed_page_found ) {
			$page_id   = $trashed_page_found;
			$page_data = [
				'ID'          => $page_id,
				'post_status' => 'publish',
			];
			wp_update_post( $page_data );
		} else {
			$page_data = [
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_name'      => $slug,
				'post_title'     => $page_title,
				'post_content'   => $page_content,
				'post_parent'    => $post_parent,
				'comment_status' => 'closed',
			];
			$page_id   = wp_insert_post( $page_data );
		}

		if ( $option ) {
			update_option( $option, $page_id );
		}

		return $page_id;
	}
}
