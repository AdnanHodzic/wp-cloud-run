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

	/**
	 * Ad creation cron job hook.
	 *
	 * @var string
	 */
	const CRON_API_ADS_CREATION = 'advanced-ads-pghb-auto-ad-creation';

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

	/* Entity: Group ------------------- */

	/**
	 * Default ad group weight
	 */
	const GROUP_AD_DEFAULT_WEIGHT = 10;
}
