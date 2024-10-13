<?php

/**
 * @link              https://jeanbaptisteaudras.com/portfolio/wordpress-reading-progressbar-indicator-plugin/
 * @since             1.0
 * @package           Reading Progress Bar
 *
 * @wordpress-plugin
 * Plugin Name:       Reading Progress Bar
 * Plugin URI:        https://jeanbaptisteaudras.com/portfolio/wordpress-reading-progressbar-indicator-plugin/
 * Description:       A reading position indicator that you can use where you want: top, bottom or custom position in differents templates or post types.
 * Version:           1.3
 * Author:            Jb Audras
 * Author URI:        http://jeanbaptisteaudras.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       reading-progress-bar
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * i18n
 */
load_plugin_textdomain( 'reading-progress-bar', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 

/**
 * Admin
 */
if (is_admin()) {
 require_once plugin_dir_path( __FILE__ ) . '/admin/rp-admin.php';
}
/**
 * Public
 */
require_once plugin_dir_path( __FILE__ ) . '/public/rp-public.php';
