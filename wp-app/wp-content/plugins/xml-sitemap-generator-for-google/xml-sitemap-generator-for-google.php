<?php
/**
 * Plugin Name: Google XML Sitemaps Generator
 * Plugin URI: https://wordpress.org/plugins/xml-sitemap-generator-for-google/
 * Description: Greatly improves SEO by generating XML Sitemaps, HTML Sitemaps and Google News, helping search engines like Google, Bing, Yahoo and others index your site more effectively.
 * Author: WPGrim
 * Author URI: https://wpgrim.com/
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: xml-sitemap-generator-for-google
 * Version: 2.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GRIM_SG_VERSION', '2.0.5' );
define( 'GRIM_SG_FILE', __FILE__ );
define( 'GRIM_SG_PATH', dirname( GRIM_SG_FILE ) );
define( 'GRIM_SG_INCLUDES', GRIM_SG_PATH . '/includes/' );
define( 'GRIM_SG_URL', plugin_dir_url( GRIM_SG_FILE ) );
define( 'GRIM_SG_BASENAME', plugin_basename( GRIM_SG_FILE ) );

require_once GRIM_SG_INCLUDES . 'autoload.php';

register_activation_hook( GRIM_SG_FILE, 'sgg_activation' );
register_deactivation_hook( GRIM_SG_FILE, 'sgg_deactivation' );
