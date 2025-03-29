<?php
/**
 * Constants.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds;

defined( 'ABSPATH' ) || exit;

/**
 * Constants.
 */
class Constants {
	/**
	 * Rest API base.
	 *
	 * @var string
	 */
	const REST_BASE = 'advanced-ads/v1';

	/**
	 * Prefix of selectors (id, class) in the frontend
	 * can be changed by options
	 *
	 * @var string
	 */
	const DEFAULT_FRONTEND_PREFIX = 'advads-';

	/**
	 * Constant representing the slug for the hidden page in the Advanced Ads plugin.
	 *
	 * @var string HIDDEN_PAGE_SLUG The slug for the hidden page.
	 */
	const HIDDEN_PAGE_SLUG = 'advanced_ads_hidden_page_slug';

	/* Entity Types ------------------- */

	/**
	 * The ad entity type.
	 *
	 * @var string
	 */
	const ENTITY_AD = 'ad';

	/**
	 * The group entity type.
	 *
	 * @var string
	 */
	const ENTITY_GROUP = 'group';

	/**
	 * The placement entity type.
	 *
	 * @var string
	 */
	const ENTITY_PLACEMENT = 'placement';

	/* Post Types and Taxonomies Slugs ------------------- */

	/**
	 * The ad post type slug.
	 *
	 * @var string
	 */
	const POST_TYPE_AD = 'advanced_ads';

	/**
	 * The placement post type slug.
	 *
	 * @var string
	 */
	const POST_TYPE_PLACEMENT = 'advanced_ads_plcmnt';

	/**
	 * The group taxonomy slug.
	 *
	 * @var string
	 */
	const TAXONOMY_GROUP = 'advanced_ads_groups';

	/* Post Types Status ------------------- */

	/**
	 * Ad post expired status
	 *
	 * @var string
	 */
	const AD_STATUS_EXPIRED = 'advanced_ads_expired';

	/**
	 * Ad post expiring status
	 *
	 * @var string
	 */
	const AD_STATUS_EXPIRING = 'advanced_ads_expiring';

	/* Cron Jobs Hooks ------------------- */

	/**
	 * Ad expiration cron job hook.
	 *
	 * @var string
	 */
	const CRON_JOB_AD_EXPIRATION = 'advanced-ads-ad-expiration';

	/* Meta keys ------------------- */

	/**
	 * Ad metakey for expiry time.
	 *
	 * @var string
	 */
	const AD_META_EXPIRATION_TIME = 'advanced_ads_expiration_time';

	/* User Meta Keys ------------------- */

	/**
	 * Wizard notice dismiss.
	 *
	 * @var string
	 */
	const USER_WIZARD_DISMISS = 'advanced-ads-notice-wizard-dismiss';

	/* Option keys ------------------- */

	/**
	 * Option key for the completion status of the wizard.
	 *
	 * @var string
	 */
	const OPTION_WIZARD_COMPLETED = '_advanced_ads_wizard_completed';

	/**
	 * Option key for adblocker settings.
	 *
	 * @var string
	 */
	const OPTION_ADBLOCKER_SETTINGS = 'advanced-ads-adblocker';

	/* Entity: Group ------------------- */

	/**
	 * Default ad group weight
	 */
	const GROUP_AD_DEFAULT_WEIGHT = 10;

	/* Misc ---------------------------- */

	const ADDONS_NON_COMPATIBLE_VERSIONS = [
		'1.1.3'  => 'advanced-ads-adsense-in-feed',    // Advanced Ads – Google AdSense In-feed Placement.
		'2.5.0'  => 'advanced-ads-gam',                // GAM.
		'1.0.8'  => 'advanced-ads-genesis',            // Genesis.
		'1.3.5'  => 'advanced-ads-geo',                // Geo.
		'1.7.9'  => 'advanced-ads-layer',              // Layer Ads.
		'0.1.3'  => 'advanced-ads-page-peel',          // Page Peel.
		'2.28.0' => 'advanced-ads-pro',                // Pro.
		'1.12.3' => 'advanced-ads-responsive',         // AMP former Responsive Ads.
		'1.4.5'  => 'advanced-ads-selling',            // Selling.
		'1.4.10' => 'slider-ads',                      // Slider.
		'1.8.6'  => 'advanced-ads-sticky',             // Sticky Ads.
		'2.8.1'  => 'advanced-ads-tracking',           // Tracking.
		'1.0.7'  => 'ads-for-visual-composer',         // Visual Composer.
		'1.1.0'  => 'advanced-ads-browser-language',   // Browser Language.
	];
}
