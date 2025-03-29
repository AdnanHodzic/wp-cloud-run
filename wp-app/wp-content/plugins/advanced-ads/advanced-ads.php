<?php
/**
 * Advanced Ads.
 *
 * @package   Advanced_Ads
 * @author    Advanced Ads <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Advanced Ads GmbH
 *
 * @wordpress-plugin
 * Plugin Name:       Advanced Ads
 * Version:           2.0.5
 * Description:       Manage and optimize your ads in WordPress
 * Plugin URI:        https://wpadvancedads.com
 * Author:            Advanced Ads
 * Author URI:        https://wpadvancedads.com
 * Text Domain:       advanced-ads
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @requires
 * Requires at least: 5.7
 * Requires PHP:      7.4
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
define( 'ADVADS_VERSION', '2.0.5' );

// Load the autoloader.
require_once __DIR__ . '/includes/class-autoloader.php';
\AdvancedAds\Autoloader::get()->initialize();

/**
 * Compatibility check for addons for 2.0.0 release.
 */
if ( version_compare( ADVADS_VERSION, '2.0.0', '>=' ) ) {
	( new \AdvancedAds\Installation\Compatibility() )->hooks();
}

if ( ! function_exists( 'wp_advads' ) ) {
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
}
