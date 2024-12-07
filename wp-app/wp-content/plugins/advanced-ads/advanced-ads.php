<?php
/**
 * Advanced Ads.
 *
 * @package   Advanced_Ads
 * @author    Advanced Ads <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, Advanced Ads GmbH
 *
 * @wordpress-plugin
 * Plugin Name:       Advanced Ads
 * Plugin URI:        https://wpadvancedads.com
 * Description:       Manage and optimize your ads in WordPress
 * Version:           1.56.1
 * Author:            Advanced Ads
 * Author URI:        https://wpadvancedads.com
 * Text Domain:       advanced-ads
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 */

// Early bail!!
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( defined( 'ADVADS_FILE' ) ) {
	return;
}

define( 'ADVADS_FILE', __FILE__ );
define( 'ADVADS_VERSION', '1.56.1' );

// Load the autoloader.
require_once __DIR__ . '/includes/class-autoloader.php';
\AdvancedAds\Autoloader::get()->initialize();

/**
 * Returns the main instance of Advanced Ads.
 *
 * @since 1.46.0
 * @return \AdvancedAds\Plugin
 */
function wp_advads() {
	return \AdvancedAds\Plugin::get();
}

// Start it.
wp_advads();
