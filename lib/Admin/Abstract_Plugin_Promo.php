<?php

namespace Barn2\VAT_Lib\Admin;

use Barn2\VAT_Lib\Plugin\Plugin;
use Barn2\VAT_Lib\Util;

/**
 * Abstract class to handle the plugin promo sidebar used in most Barn2 plugins.
 *
 * @package   Barn2\barn2-lib
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 * @version   1.0
 */
abstract class Abstract_Plugin_Promo {

	/**
	 * @var Plugin The plugin object.
	 */
	protected $plugin;

	/**
	 * @var string The content of the plugin promo.
	 */
	private $promo_content = null;

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin The plugin object.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
	}

	/**
	 * Retrieve the complete promo sidebar to insert into the settings page.
	 *
	 * @return string The promo sidebar.
	 */
	protected function get_promo_sidebar() {
		$promo_content = $this->get_promo_content();

		if ( ! empty( $promo_content ) ) {
			// Promo content is sanitized via barn2_kses_post.
			// phpcs:ignore WordPress.Security.EscapeOutput
			return '<div id="barn2_plugins_promo" class="barn2-plugins-promo-wrapper">' . Util::barn2_kses_post( $promo_content ) . '</div>';
		}

		return '';
	}

	/**
	 * Retrieve the plugin promo content from the API.
	 *
	 * @return string The promo sidebar content.
	 */
	protected function get_promo_content() {
		if ( null !== $this->promo_content ) {
			return $this->promo_content;
		}

		$plugin_id = $this->plugin->get_id();

		$review_content = get_transient( 'barn2_plugin_review_banner_' . $plugin_id );
		$promo_content  = get_transient( 'barn2_plugin_promo_' . $plugin_id );

		if ( false === $review_content ) {
			$review_content_url = Util::barn2_url( '/wp-json/barn2/v2/pluginpromo/' . $plugin_id . '?_=' . gmdate( 'mdY' ) );
			$review_content_url = add_query_arg(
				[
					'source'   => urlencode( get_bloginfo( 'url' ) ),
					'template' => 'review_request',
				],
				$review_content_url
			);

			$review_response = wp_remote_get(
				$review_content_url,
				[
					'sslverify' => defined( 'WP_DEBUG' ) && WP_DEBUG ? false : true,
				]
			);

			if ( 200 !== wp_remote_retrieve_response_code( $review_response ) ) {
				$review_content = '';
			} else {
				$review_content = json_decode( wp_remote_retrieve_body( $review_response ) );
				set_transient( 'barn2_plugin_review_banner_' . $plugin_id, $review_content, 7 * DAY_IN_SECONDS );
			}
		}

		if ( false === $promo_content ) {
			$promo_content_url = Util::barn2_url( '/wp-json/barn2/v2/pluginpromo/' . $plugin_id . '?_=' . gmdate( 'mdY' ) );
			$plugin_dir        = WP_PLUGIN_DIR;
			$current_plugins   = get_plugins();
			$barn2_installed   = [];

			foreach ( $current_plugins as $slug => $data ) {
				if ( false !== stripos( $data['Author'], 'Barn2 Plugins' ) ) {

					if ( is_readable( "$plugin_dir/$slug" ) ) {
						$plugin_contents = file_get_contents( "$plugin_dir/$slug" );

						if ( preg_match( '/namespace ([0-9A-Za-z_\\\]+);/', $plugin_contents, $namespace ) ) {
							$classname = $namespace[1] . '\Plugin';

							if ( class_exists( $classname ) && defined( "$classname::ITEM_ID" ) ) {
								if ( $id = ( $classname::ITEM_ID ?? null ) ) {
									$barn2_installed[] = $id;
								}
							}
						}
					}
				}
			}

			if ( $barn2_installed ) {
				$promo_content_url = add_query_arg( 'plugins_installed', implode( ',', $barn2_installed ), $promo_content_url );
			}

			$promo_content_url = add_query_arg( 'source', urlencode( get_bloginfo( 'url' ) ), $promo_content_url );

			$promo_response = wp_remote_get(
				$promo_content_url,
				[
					'sslverify' => defined( 'WP_DEBUG' ) && WP_DEBUG ? false : true,
				]
			);

			if ( 200 !== wp_remote_retrieve_response_code( $promo_response ) ) {
				$promo_content = '';
			} else {
				$promo_content = json_decode( wp_remote_retrieve_body( $promo_response ) );
				set_transient( 'barn2_plugin_promo_' . $plugin_id, $promo_content, 7 * DAY_IN_SECONDS );
			}
		}

		$this->promo_content = $review_content . $promo_content;
		return $this->promo_content;
	}

	/**
	 * Load the plugin promo stylesheet.
	 *
	 * @return void
	 */
	public function load_styles() {
		wp_enqueue_style( 'barn2-plugins-promo', plugins_url( 'lib/assets/css/admin/plugin-promo.min.css', $this->plugin->get_file() ), [], $this->plugin->get_version(), 'all' );
	}

}
