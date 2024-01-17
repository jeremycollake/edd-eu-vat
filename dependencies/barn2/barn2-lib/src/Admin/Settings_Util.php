<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Lib\Admin;

use Barn2\Plugin\EDD_VAT\Dependencies\Lib\Plugin\Plugin;
/**
 * Utility functions for the Plugin settings.
 *
 * @package   Barn2\barn2-lib
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 * @version   1.1
 */
class Settings_Util
{
    /**
     * Convert a checkbox bool value to 'yes' or 'no' (used by WooCommerce).
     *
     * @param bool $bool A bool value.
     * @return string 'yes' if true, 'no' otherwise.
     */
    public static function bool_to_checkbox_setting($bool)
    {
        return $bool ? 'yes' : 'no';
    }
    /**
     * Convert a checkbox 'yes'/'no' value to a bool.
     *
     * @param string $value 'yes' or 'no'
     * @return bool true if 'yes', false otherwise.
     */
    public static function checkbox_setting_to_bool($value)
    {
        return \in_array($value, ['yes', \true], \true);
    }
    public static function get_checkbox_option($option, $default = \false)
    {
        return self::checkbox_setting_to_bool(\get_option($option, $default));
    }
    public static function get_custom_attributes($field)
    {
        $custom_attributes = [];
        if (!empty($field['custom_attributes']) && \is_array($field['custom_attributes'])) {
            foreach ($field['custom_attributes'] as $attribute => $attribute_value) {
                $custom_attributes[] = \esc_attr($attribute) . '="' . \esc_attr($attribute_value) . '"';
            }
        }
        return \implode(' ', $custom_attributes);
    }
    /**
     * Return the help links for the plugin settings page - Documentation, Support, etc.
     *
     * @param Plugin $plugin The plugin object
     * @return string The formatted help links
     */
    public static function get_help_links(Plugin $plugin)
    {
        /**
         * Filters the list of help links used at the top of the plugin settings page.
         *
         * @param array $links
         *   The array of help links. Each link is an array containing the following keys:
         *   - url: The link URL
         *   - label: The link label
         *   - class (optional): The link class.
         *   - target (optional): If the link should open in a new tab, set this to '_blank'
         * @since 1.1
         */
        $links = \apply_filters('barn2_plugin_settings_help_links', ['doc' => ['url' => $plugin->get_documentation_url(), 'label' => __('Documentation', 'edd-eu-vat'), 'target' => '_blank'], 'support' => ['url' => $plugin->get_support_url(), 'label' => __('Support', 'edd-eu-vat'), 'target' => '_blank']], $plugin);
        $links = \apply_filters_deprecated('barn2_plugins_title_links', [$links, $plugin], '1.1', 'barn2_plugin_settings_help_links');
        return \implode(' | ', \array_map(function ($link) {
            $class = !empty($link['class']) ? \sprintf(' class="%s"', \esc_attr($link['class'])) : '';
            $target = !empty($link['target']) ? \sprintf(' target="%s"', \esc_attr($link['target'])) : '';
            return \sprintf('<a href="%s"%s%s>%s</a>', \esc_url($link['url']), $class, $target, \esc_html($link['label']));
        }, $links));
    }
    /**
     * Return the description for the main title of a settings tab/section including the links below the description.
     *
     * @param Plugin $plugin
     * @param string $description The text of the description
     *
     * @return string
     * @depecated Replaced by get_plugin_links
     */
    public static function get_title_description($plugin, $description)
    {
        \_deprecated_function(__METHOD__, '1.1', 'get_plugin_links');
        return \sprintf('<p>%s</p><p>%s</p>', self::get_help_links($plugin), \esc_html($description));
    }
    /**
     * Check whether the current page, tab and section match the ones the plugin uses
     *
     * @param string $page    The slug of the page for the plugin settings.
     * @param string $tab     The slug of the tab for the plugin settings. Default to an empty string.
     * @param string $section The slug of the section for the plugin settings. Default to an empty string.
     *
     * @return boolean
     */
    public static function is_current_settings_page($page, $tab = '', $section = '')
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $current_page = empty($_GET['page']) ? 'general' : \sanitize_title(\wp_unslash($_GET['page']));
        $current_tab = empty($_GET['tab']) ? '' : \sanitize_title(\wp_unslash($_GET['tab']));
        $current_section = empty($_REQUEST['section']) ? '' : \sanitize_title(\wp_unslash($_REQUEST['section']));
        if (!$section) {
            $section = $current_section;
        }
        if (!$tab) {
            $tab = $tab;
        }
        return $page === $current_page && $tab === $current_tab && $section === $current_section;
    }
    /**
     * A shorthand of is_current_settings_page for WooCommerce plugins
     *
     * @param string $section The slug of the section for the plugin settings.
     * @param string $tab     The slug of the tab for the plugin settings. Default to 'products'.
     *
     * @return boolean
     */
    public static function is_current_wc_settings_page($section, $tab = 'products')
    {
        return self::is_current_settings_page('wc-settings', $tab, $section);
    }
}
