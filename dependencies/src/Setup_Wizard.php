<?php

/**
 * @package   Barn2\setup-wizard
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard;

use Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Interfaces\Bootable;
use Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Interfaces\Restartable;
use Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Steps\Cross_Selling;
/**
 * Create a setup wizard for a given plugin.
 */
class Setup_Wizard implements Bootable
{
    /**
     * Plugin instance.
     *
     * @var Plugin
     */
    private $plugin;
    /**
     * List of steps available for this wizard.
     *
     * @var array
     */
    private $steps = [];
    /**
     * Setup wizard slug.
     *
     * @var string
     */
    private $slug;
    /**
     * Determine if the library is in dev mode.
     * When in dev mode, this will hold the __FILE__ path
     *
     * @var boolean|string
     */
    private $dev_mode;
    /**
     * List of arguments and configuration settings sent to the react app.
     *
     * @var array
     */
    private $js_args = [];
    /**
     * Holds the EDD_Licensing class.
     *
     * @var object
     */
    private $edd_api;
    /**
     * Holds the Plugin_License class.
     *
     * @var object
     */
    private $plugin_license;
    /**
     * Specify the hook to use to which the restart button will be attached.
     *
     * @var string
     */
    private $restart_hook;
    /**
     * Url and path to the custom script that may be needed to extend the react app.
     *
     * @var array
     */
    private $custom_asset_url = [];
    /**
     * Determine if WooCommerce is available within the environment.
     *
     * @var boolean
     */
    private $woocommerce = \true;
    /**
     * URL to the custom base app .js file used when the library is not being used with WC.
     *
     * @var string
     */
    private $non_wc_app_url = null;
    /**
     * Array of dependencies of the non-woocommerce asset file.
     *
     * @var array
     */
    private $non_wc_deps = [];
    /**
     * Version string of the non-woocommerce asset file.
     *
     * @var string
     */
    private $non_wc_version = null;
    /**
     * Holds the custom path to the library.
     *
     * @var string
     */
    private $lib_path;
    /**
     * Holds the custom url to the library.
     *
     * @var string
     */
    private $lib_url;
    /**
     * Configure a new plugin setup wizard.
     *
     * @param object $plugin instance of plugin
     * @param array $steps list of steps to add to the wizard
     */
    public function __construct($plugin, $steps = [], $woocommerce = \true)
    {
        $this->plugin = $plugin;
        $this->slug = $this->plugin->get_slug() . '-setup-wizard';
        $this->woocommerce = $woocommerce;
        if (!empty($steps)) {
            $this->add_steps($steps);
        }
    }
    /**
     * Manually override the library path.
     * Useful when developing new features for the library.
     *
     * @param string $path
     * @return self
     */
    public function set_lib_path(string $path)
    {
        $this->lib_path = $path;
        return $this;
    }
    /**
     * Manually override the library url.
     * Useful when developing new features for the library.
     *
     * @param string $url
     * @return self
     */
    public function set_lib_url(string $url)
    {
        $this->lib_url = $url;
        return $this;
    }
    /**
     * Determine whether or not WooCommerce is available within the environment.
     * When WC is not available we have to load assets in a different way
     * because @woocommerce/components package uses a global window.wc variable
     * on the frontend which is not available without WooCommerce.
     *
     * @return boolean
     */
    public function has_woocommerce()
    {
        return (bool) $this->woocommerce;
    }
    /**
     * Get the slug of the wizard.
     *
     * @return void
     */
    public function get_slug()
    {
        return $this->slug;
    }
    /**
     * Set or unset the library dev mode.
     *
     * @param boolean $value path to the __FILE__
     * @return Setup_Wizard
     */
    public function set_dev_mode($value)
    {
        $this->dev_mode = $value;
        return $this;
    }
    /**
     * Determine if the library is in dev mode.
     *
     * @return boolean
     */
    public function is_dev_mode()
    {
        return !empty($this->dev_mode);
    }
    /**
     * Configure the barn2_setup_wizard js object for the react app.
     *
     * @param array $args
     * @return Setup_Wizard
     */
    public function configure($args = [])
    {
        $defaults = ['plugin_name' => $this->plugin->get_name(), 'plugin_slug' => $this->plugin->get_slug(), 'plugin_product_id' => $this->plugin::ITEM_ID, 'skip_url' => admin_url(), 'license_tooltip' => '', 'utm_id' => '', 'premium_url' => '', 'completed' => $this->is_completed()];
        $args = wp_parse_args($args, $defaults);
        $args['ajax'] = esc_url(admin_url('admin-ajax.php'));
        $args['nonce'] = wp_create_nonce('barn2_setup_wizard_nonce');
        $args['nonce_upsells'] = wp_create_nonce('barn2_setup_wizard_upsells_nonce');
        $this->js_args = $args;
        return $this;
    }
    /**
     * Assign a Plugin_License and an EDD_Licensing class to the setup wizard.
     *
     * @param string $plugin_license_class full class path to the barn2lib Plugin_License class.
     * @param string $edd_licensing_class full class path to the barn2lib EDD_Licensing class.
     * @return Setup_Wizard
     */
    public function add_license_class(string $plugin_license_class)
    {
        $this->plugin_license = new $plugin_license_class($this->plugin->get_id(), $this->edd_api);
        return $this;
    }
    /**
     * Get the Plugin_License class.
     *
     * @return object
     */
    public function get_licensing()
    {
        return $this->plugin_license;
    }
    /**
     * Assign an EDD_Licensing class to the setup wizard.
     *
     * @param string $edd_licensing_class full class path to the barn2lib EDD_Licensing class.
     * @return Setup_Wizard
     */
    public function add_edd_api(string $edd_licensing_class)
    {
        $this->edd_api = $edd_licensing_class::instance();
        return $this;
    }
    /**
     * Get the EDD_Licensing class assigned to the setup wizard.
     *
     * @return object
     */
    public function get_edd_api()
    {
        return $this->edd_api;
    }
    /**
     * Get the list of arguments for the barn2_setup_wizard js object.
     *
     * @return array
     */
    public function get_js_args()
    {
        return $this->js_args;
    }
    /**
     * Specify the hook to use to which the restart button will be attached.
     *
     * @param string $hook
     * @return void
     */
    public function set_restart_hook(string $hook)
    {
        $this->restart_hook = $hook;
        return $this;
    }
    /**
     * Get the hook to use to which the restart button will be attached.
     *
     * @return string
     */
    public function get_restart_hook()
    {
        return $this->restart_hook;
    }
    /**
     * Add a step to the process.
     *
     * @param Step $step single instance of Step
     * @return Setup_Wizard
     */
    public function add(Step $step)
    {
        $step->with_plugin($this->plugin);
        $step->set_fields();
        $this->steps[] = $step;
        return $this;
    }
    /**
     * Add multiple steps to the process.
     *
     * @param array $steps
     * @return Setup_Wizard
     */
    public function add_steps(array $steps)
    {
        foreach ($steps as $step) {
            if (!$step instanceof Step) {
                continue;
            }
            $step->with_plugin($this->plugin);
            $step->set_fields();
            $this->steps[] = $step;
        }
        return $this;
    }
    /**
     * Set the url to the .js file built without the WC webpack extraction tool.
     *
     * @param string $url
     * @param array $deps array of dependencies
     * @param string $version version number of the file
     * @return void
     */
    public function set_non_wc_asset($url, $deps = [], $version = null)
    {
        $this->non_wc_app_url = $url;
        $this->non_wc_deps = $deps;
        $this->non_wc_version = $version;
        return $this;
    }
    /**
     * Get the url to the .js file built without the WC webpack extraction tool.
     *
     * @return string
     */
    public function get_non_wc_asset()
    {
        return $this->non_wc_app_url;
    }
    /**
     * Get the array of dependencies configure for non-wc asset.
     *
     * @return array
     */
    public function get_non_wc_dependencies()
    {
        return $this->non_wc_deps;
    }
    /**
     * Get the version string for the non-wc asset file.
     *
     * @return string
     */
    public function get_non_wc_version()
    {
        return $this->non_wc_version;
    }
    /**
     * URL and path to the custom script that is loaded together with the react app.
     *
     * @param string $url
     * @param array $dependencies Use the `get_script_dependencies` method from the barn2 lib Util class to retrieve the array of dependencies.
     * @return Setup_Wizard
     */
    public function add_custom_asset(string $url, array $dependencies)
    {
        $this->custom_asset_url = ['url' => $url, 'dependencies' => $dependencies];
        return $this;
    }
    /**
     * Get the custom asset url.
     *
     * @return string
     */
    public function get_custom_asset()
    {
        return $this->custom_asset_url;
    }
    /**
     * Determine if the wizard was once completed.
     *
     * @return boolean
     */
    public function is_completed()
    {
        return (bool) get_option("{$this->get_slug()}_completed");
    }
    /**
     * Mark the wizard as completed.
     *
     * @return void
     */
    public function set_as_completed()
    {
        update_option("{$this->get_slug()}_completed", \true);
    }
    /**
     * Create list of configuration values of steps used by the react app.
     *
     * @return array
     */
    private function get_steps_configuration()
    {
        $config = [];
        /** @var Step $step */
        foreach ($this->steps as $step) {
            if ($step instanceof Cross_Selling) {
                $item_id = $this->plugin::ITEM_ID;
                $is_pass = get_option("barn2_plugin_{$item_id}_license_is_pass");
                if ($is_pass) {
                    $step->set_hidden(\true);
                }
            }
            $config[] = ['key' => $step->get_id(), 'label' => $step->get_name(), 'description' => $step->get_description(), 'heading' => $step->get_title(), 'tooltip' => $step->get_tooltip(), 'fields' => $step->get_fields(), 'hidden' => $step->is_hidden()];
        }
        return $config;
    }
    public function get_steps()
    {
        return $this->steps;
    }
    /**
     * Boot the setup wizard.
     *
     * @return void
     */
    public function boot()
    {
        // Merge the fields configuration to the js arguments.
        $this->js_args = \array_merge($this->js_args, ['steps' => $this->get_steps_configuration()]);
        // Merge license details if needed.
        if ($this->edd_api) {
            $this->js_args = \array_merge($this->js_args, ['license_key' => $this->get_licensing()->get_license_key(), 'license_status' => $this->get_licensing()->get_status(), 'license_status_text' => $this->get_licensing()->get_status_help_text()]);
        }
        // Hook into WP.
        add_action('admin_menu', [$this, 'register_admin_page']);
        add_filter('admin_body_class', [$this, 'admin_page_body_class']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets'], 20);
        add_action('admin_head', [$this, 'admin_head']);
        if ($this instanceof Restartable) {
            add_action("wp_ajax_barn2_wizard_{$this->plugin->get_slug()}_on_restart", [$this, 'on_restart']);
        }
        // Hook all steps into WP.
        if (!empty($this->steps) && \is_array($this->steps)) {
            /** @var Step $step */
            foreach ($this->steps as $step) {
                $step->with_wizard($this);
                $step->boot();
            }
        }
        // Merge initial values into the js config.
        $this->js_args = \array_merge($this->js_args, ['initial_values' => $this->get_steps_values()]);
        // Attach the restart button if specified.
        if (!empty($this->get_restart_hook())) {
            add_action($this->get_restart_hook(), [$this, 'add_restart_btn']);
        }
    }
    /**
     * Get the intial values of all fields in the setup wizard.
     *
     * @return array
     */
    public function get_steps_values()
    {
        $values = [];
        /** @var Step $step */
        foreach ($this->steps as $step) {
            $fields = $step->get_fields();
            if (!empty($fields)) {
                foreach ($fields as $key => $field) {
                    if ($field['type'] === 'checkbox') {
                        continue;
                    }
                    $disallowed = ['title', 'heading', 'list', 'image'];
                    if (\in_array($field['type'], $disallowed)) {
                        continue;
                    }
                    $values[$key] = isset($field['value']) ? $field['value'] : '';
                }
            }
        }
        return $values;
    }
    /**
     * Register a new page in the dashboard menu.
     *
     * @return void
     */
    public function register_admin_page()
    {
        $menu_slug = $this->get_slug();
        $page_title = \sprintf(__('%s setup wizard', 'edd-eu-vat'), $this->plugin->get_name());
        add_menu_page($page_title, $page_title, 'manage_options', $menu_slug, [$this, 'render_setup_wizard_page']);
    }
    /**
     * Hide the setup wizard page from the menu.
     *
     * @return void
     */
    public function admin_head()
    {
        remove_menu_page($this->get_slug());
    }
    /**
     * Render the element to which the react app will attach itself.
     *
     * @return void
     */
    public function render_setup_wizard_page()
    {
        echo '<div id="root"></div>';
    }
    /**
     * Setup custom classes for the body tag when viewing the setup wizard page.
     *
     * @param string $class
     * @return string
     */
    public function admin_page_body_class($class)
    {
        $screen = get_current_screen();
        if ($screen->id !== 'toplevel_page_' . $this->get_slug()) {
            return $class;
        }
        $class .= ' barn2-setup-wizard-page woocommerce-page woocommerce_page_wc-admin woocommerce-onboarding woocommerce-profile-wizard__body woocommerce-admin-full-screen is-wp-toolbar-disabled';
        return $class;
    }
    /**
     * Get the url to the library.
     *
     * @return string
     */
    public function get_library_url()
    {
        $url = trailingslashit(plugin_dir_url($this->plugin->get_file())) . 'dependencies/';
        if ($this->is_dev_mode()) {
            $url = trailingslashit(plugin_dir_url($this->dev_mode));
        }
        if (!empty($this->lib_url)) {
            return $this->lib_url;
        }
        return $url;
    }
    /**
     * Get the path to the library.
     *
     * @return string
     */
    public function get_library_path()
    {
        $path = trailingslashit(plugin_dir_path($this->plugin->get_file())) . 'dependencies/';
        if ($this->is_dev_mode()) {
            $path = trailingslashit(plugin_dir_path($this->dev_mode));
        }
        if (!empty($this->lib_path)) {
            return $this->lib_path;
        }
        return $path;
    }
    /**
     * Enqueue required assets.
     *
     * @param string $hook
     * @return void
     */
    public function enqueue_assets($hook)
    {
        if ($hook !== 'toplevel_page_' . $this->get_slug()) {
            return;
        }
        $slug = $this->get_slug();
        if (!$this->has_woocommerce() && !empty($this->get_non_wc_asset())) {
            $slug = 'b2-wizard-nonwc-app';
        }
        $script_path = 'build/main.js';
        $script_asset_path = $this->get_library_path() . 'build/main.asset.php';
        $script_asset = \file_exists($script_asset_path) ? require $script_asset_path : ['dependencies' => [], 'version' => \filemtime($script_path)];
        $script_url = $this->get_library_url() . $script_path;
        if (!$this->has_woocommerce()) {
            $script_asset['dependencies'] = $this->remove_wc_dependencies($script_asset['dependencies']);
        }
        wp_register_script($slug, $script_url, $script_asset['dependencies'], $script_asset['version'], \true);
        $styling_dependencies = ['wp-components', 'wc-components'];
        if (!$this->has_woocommerce()) {
            $styling_dependencies = ['wp-components'];
            wp_enqueue_style('b2-wc-components', $this->get_library_url() . 'resources/wc-vendor/components.css', \false, $script_asset['version']);
        }
        wp_register_style($slug, $this->get_library_url() . 'build/main.css', $styling_dependencies, \filemtime($this->get_library_path() . '/build/main.css'));
        $custom_asset = $this->get_custom_asset();
        if (isset($custom_asset['url'])) {
            if (isset($custom_asset['dependencies']) && !isset($custom_asset['dependencies']['dependencies'])) {
                $custom_asset_dependencies = $custom_asset['dependencies'];
            } else {
                $custom_asset_dependencies = $custom_asset['dependencies']['dependencies'];
            }
            if (empty($custom_asset_dependencies) || !\is_array($custom_asset_dependencies)) {
                wp_die('Custom asset dependencies should not be empty and should be an array.');
            }
            wp_enqueue_script("{$slug}_custom_asset", $custom_asset['url'], $custom_asset_dependencies, 1, \true);
        }
        if ($this->has_woocommerce()) {
            wp_enqueue_script($slug);
        }
        wp_enqueue_style($slug);
        if (!$this->has_woocommerce() && !empty($this->get_non_wc_asset())) {
            wp_enqueue_script('b2-wizard-nonwc-app', $this->get_non_wc_asset(), $this->get_non_wc_dependencies(), $this->get_non_wc_version(), \true);
        }
        if (isset($custom_asset['url'])) {
            wp_add_inline_script("{$slug}_custom_asset", 'const barn2_setup_wizard = ' . \json_encode($this->get_js_args()), 'before');
        } else {
            wp_add_inline_script($slug, 'const barn2_setup_wizard = ' . \json_encode($this->get_js_args()), 'before');
        }
    }
    /**
     * Attach the restart wizard button.
     *
     * @return void
     */
    public function add_restart_btn()
    {
        $url = add_query_arg(['page' => $this->get_slug()], admin_url('admin.php'));
        ?>
		<div class="barn2-setup-wizard-restart">
			<hr>
			<h3><?php 
        esc_html_e('Setup wizard', 'edd-eu-vat');
        ?></h3>
			<p><?php 
        esc_html_e('If you need to access the setup wizard again, please click on the button below.', 'edd-eu-vat');
        ?></p>
			<a href="<?php 
        echo esc_url($url);
        ?>" class="button barn2-wiz-restart-btn"><?php 
        esc_html_e('Setup wizard', 'edd-eu-vat');
        ?></a>
			<hr>
		</div>

		<style>
			.barn2-wiz-restart-btn {
				margin-bottom: 1rem !important;
			}
		</style>

		<script>
			jQuery( '.barn2-wiz-restart-btn' ).on( 'click', function( e ) {
				return confirm( '<?php 
        echo esc_html(\sprintf(__('Warning: This will overwrite your existing settings for %s. Are you sure you want to continue?', 'edd-eu-vat'), $this->plugin->get_name()));
        ?>' );
			});
		</script>
		<?php 
    }
    /**
     * Add a restart link next to the settings page docs and support link.
     *
     * @param string $wc_section_id
     * @param string $title_option_id
     * @return void
     */
    public function add_restart_link(string $wc_section_id, string $title_option_id)
    {
        add_filter("woocommerce_get_settings_{$wc_section_id}", function ($settings) use($title_option_id) {
            $url = add_query_arg(['page' => $this->get_slug()], admin_url('admin.php'));
            $title_setting = wp_list_filter($settings, ['id' => $title_option_id]);
            if ($title_setting && isset($title_setting[\key($title_setting)]['desc'])) {
                $desc = $title_setting[\key($title_setting)]['desc'];
                $p_closing_tag = \strrpos($desc, '</p>');
                $new_desc = \substr_replace($desc, ' | <a class="barn2-wiz-restart-btn" href="' . esc_url($url) . '">' . esc_html__('Setup wizard', 'edd-eu-vat') . '</a>', $p_closing_tag, 0);
                $settings[\key($title_setting)]['desc'] = $new_desc;
            }
            return $settings;
        });
        add_action('admin_footer', function () use($wc_section_id) {
            $screen = get_current_screen();
            if ($screen->id === 'woocommerce_page_wc-settings' && isset($_GET['tab']) && ($_GET['tab'] === $wc_section_id || 'products' === $_GET['tab'] && isset($_GET['section']) && $_GET['section'] === $wc_section_id)) {
                ?>
				<script>
					jQuery( '.barn2-wiz-restart-btn' ).on( 'click', function( e ) {
						return confirm( '<?php 
                echo esc_html(\sprintf(__('Warning: This will overwrite your existing settings for %s. Are you sure you want to continue?', 'edd-eu-vat'), $this->plugin->get_name()));
                ?>' );
					});
				</script>
					<?php 
            }
        });
    }
    /**
     * Remove all WooCommerce related scripts from the list
     * of scripts dependencies when enqueing assets.
     *
     * @param array $deps
     * @return array
     */
    private function remove_wc_dependencies($deps)
    {
        foreach ($deps as $key => $dep) {
            if (\strpos($dep, 'wc') === 0) {
                unset($deps[$key]);
            }
        }
        return $deps;
    }
}
