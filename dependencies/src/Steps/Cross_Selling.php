<?php

/**
 * @package   Barn2\setup-wizard
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Steps;

use Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Step;
use Barn2\Plugin\EDD_VAT\Dependencies\Barn2\Setup_Wizard\Util;
/**
 * Handles the cross selling step of the wizard.
 */
class Cross_Selling extends Step
{
    // URL of the api from where upsells are pulled from.
    const REST_URL = 'https://barn2.com/wp-json/upsell/v1/get/';
    /**
     * Initialize the step.
     */
    public function __construct()
    {
        $this->set_id('more');
    }
    /**
     * {@inheritdoc}
     */
    public function setup_fields()
    {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        add_action("wp_ajax_barn2_wizard_{$this->get_plugin()->get_slug()}_get_upsells", [$this, 'get_upsells']);
    }
    /**
     * Query for upsells from the barn2 website and store them in a transient.
     *
     * @return void
     */
    public function get_upsells()
    {
        check_ajax_referer('barn2_setup_wizard_upsells_nonce', 'nonce');
        $plugins = [];
        $transient = get_transient("barn2_wizard_{$this->get_plugin()->get_slug()}_upsells");
        $license = $this->get_wizard()->get_licensing()->get_license_key();
        if ($transient) {
            $plugins = $transient;
        } else {
            $args = ['plugin' => $this->get_plugin()->get_slug()];
            if (!empty($license)) {
                $args['license'] = $license;
            }
            $request = wp_remote_get(add_query_arg($args, self::REST_URL));
            $response = wp_remote_retrieve_body($request);
            $response = \json_decode($response, \true);
            if (200 !== wp_remote_retrieve_response_code($request)) {
                if (isset($response['error_message'])) {
                    $this->send_error(sanitize_text_field($response['error_message']));
                } else {
                    $this->send_error(__('Something went wrong while retrieving the list of products. Please try again later.', 'edd-eu-vat'));
                }
            }
            if (isset($response['success']) && isset($response['upsells'])) {
                set_transient("barn2_wizard_{$this->get_plugin()->get_slug()}_upsells", Util::clean($response['upsells']), DAY_IN_SECONDS);
            }
            $plugins = $response['upsells'];
        }
        foreach ($plugins as $index => $plugin) {
            if ($plugin['slug'] === 'all-access') {
                continue;
            }
            if (is_plugin_active("{$plugin['slug']}/{$plugin['slug']}.php")) {
                unset($plugins[$index]);
            }
        }
        wp_send_json_success(['upsells' => $plugins, 'license' => $license]);
    }
    /**
     * {@inheritdoc}
     */
    public function submit()
    {
    }
}
