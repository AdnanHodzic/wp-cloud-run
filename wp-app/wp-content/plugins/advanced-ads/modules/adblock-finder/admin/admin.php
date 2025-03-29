<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Logic to render options for ads, groups and placements
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

/**
 * Class Advanced_Ads_Adblock_Finder_Admin
 */
class Advanced_Ads_Adblock_Finder_Admin {
	/**
	 * Advanced_Ads_Adblock_Finder_Admin constructor.
	 */
	public function __construct() {
		add_filter( 'advanced-ads-setting-tabs', [ $this, 'add_tabs' ], 50 );
		add_action( 'advanced-ads-settings-init', [ $this, 'settings_init' ], 9 );
	}

	/**
	 * Add tabs to the settings page.
	 *
	 * @param array $tabs setting tabs.
	 *
	 * @return array
	 */
	public function add_tabs( array $tabs ): array {
		$tabs['adblocker'] = [
			'page'  => ADVADS_SETTINGS_ADBLOCKER,
			'group' => ADVADS_SETTINGS_ADBLOCKER,
			'tabid' => 'adblocker',
			'title' => __( 'Ad Blocker', 'advanced-ads' ),
		];

		return $tabs;
	}

	/**
	 * Add settings to settings page.
	 */
	public function settings_init() {
		register_setting( ADVADS_SETTINGS_ADBLOCKER, ADVADS_SETTINGS_ADBLOCKER );

		add_settings_section(
			'advanced_ads_adblocker_setting_section',
			__( 'Ad Blocker', 'advanced-ads' ),
			'__return_empty_string',
			ADVADS_SETTINGS_ADBLOCKER
		);

		add_settings_field(
			'GA-tracking-id',
			__( 'Ad blocker counter', 'advanced-ads' ),
			[ $this, 'render_settings_ga' ],
			ADVADS_SETTINGS_ADBLOCKER,
			'advanced_ads_adblocker_setting_section'
		);
	}

	/**
	 * Render input for the Google Analytics Tracking ID.
	 */
	public function render_settings_ga() {
		$options = Advanced_Ads::get_instance()->get_adblocker_options();
		$ga_uid  = isset( $options['ga-UID'] ) ? $options['ga-UID'] : '';

		include_once __DIR__ . '/views/setting-ga.php';
	}
}
