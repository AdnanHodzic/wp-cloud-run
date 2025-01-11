<?php

/**
 * WPBrigade Telemetry SDK
 *
 * @package wpbrigade_sdk
 * @since 3.1.0
 */
$this_sdk_version = '3.1.0';

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__FILE__) . '/require.php';

if (!defined('WP_WPBRIGADE_SDK_VERSION')) {
    define('WP_WPBRIGADE_SDK_VERSION', $this_sdk_version);
}


if (!function_exists('wpb_dynamic_init')) {
    function wpb_dynamic_init($module)
    {
        update_option('wpb_sdk_module_id', $module['id']);
        update_option('wpb_sdk_module_slug', $module['slug']);

        $wpb = WPBRIGADE_Logger::instance($module['id'], $module['slug'], true);
        $wpb->wpb_init($module);
        return [
            'logger' => $wpb,
            'slug' => $module['slug'],
            'id' => $module['id']
        ];
    }
}
