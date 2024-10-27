<?php
/**
 * Plugin Name: LightPress Lightbox
 * Plugin URI: http://wordpress.org/extend/plugins/wp-jquery-lightbox/
 * Description: Simple and lightweight lightbox for galleries and images. Formerly WP Jquery Lightbox.
 * Version: 2.3.3
 * Text Domain: wp-jquery-lightbox
 * Author: LightPress
 * Author URI: https://lightpress.io
 * License: GPLv2 or later
 *
 * @package LightPress Lighbox
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define constants.
 */
define( 'LIGHTPRESS_VERSION', '2.3.3' );
define( 'LIGHTPRESS_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'LIGHTPRESS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); // Includes ending slash.
define( 'LIGHTPRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // Includes ending slash.

/**
 * Instantiate plugin.
 *
 * NOTE: PLUGIN DEPENDENCY
 * We load this on plugins_loaded with priority 10.
 * LightPress Pro depends on this plugin, and loads
 * on plugins_loaded with priority 20.
 */
if ( defined( 'JQLB_LEGACY' ) && true === JQLB_LEGACY ) {
	require_once LIGHTPRESS_PLUGIN_DIR . 'lightboxes/wp-jquery-lightbox/wp-jquery-lightbox-legacy.php';
} else {
	require_once LIGHTPRESS_PLUGIN_DIR . 'class-lightpress.php';
	add_action( 'plugins_loaded', 'LightPress::get_instance', 10 );
}
