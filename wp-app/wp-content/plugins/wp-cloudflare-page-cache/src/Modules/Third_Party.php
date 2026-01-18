<?php

namespace SPC\Modules;


class Third_Party implements Module_Interface {

	public const SETTING_WOO_BYPASS_CART_PAGE           = 'cf_bypass_woo_cart_page';
	public const SETTING_WOO_BYPASS_CHECKOUT_PAGE       = 'cf_bypass_woo_checkout_page';
	public const SETTING_WOO_BYPASS_CHECKOUT_PAY_PAGE   = 'cf_bypass_woo_checkout_pay_page';
	public const SETTING_WOO_BYPASS_PRODUCT_PAGE        = 'cf_bypass_woo_product_page';
	public const SETTING_WOO_BYPASS_SHOP_PAGE           = 'cf_bypass_woo_shop_page';
	public const SETTING_WOO_BYPASS_PRODUCT_TAX_PAGE    = 'cf_bypass_woo_product_tax_page';
	public const SETTING_WOO_BYPASS_PRODUCT_TAG_PAGE    = 'cf_bypass_woo_product_tag_page';
	public const SETTING_WOO_BYPASS_PRODUCT_CAT_PAGE    = 'cf_bypass_woo_product_cat_page';
	public const SETTING_WOO_BYPASS_PAGES               = 'cf_bypass_woo_pages';
	public const SETTING_WOO_BYPASS_ACCOUNT_PAGE        = 'cf_bypass_woo_account_page';
	public const SETTING_WOO_AUTO_PURGE_PRODUCT_PAGE    = 'cf_auto_purge_woo_product_page';
	public const SETTING_WOO_AUTO_PURGE_SCHEDULED_SALES = 'cf_auto_purge_woo_scheduled_sales';

	public const SETTING_EDD_BYPASS_CHECKOUT_PAGE         = 'cf_bypass_edd_checkout_page';
	public const SETTING_EDD_BYPASS_SUCCESS_PAGE          = 'cf_bypass_edd_success_page';
	public const SETTING_EDD_BYPASS_FAILURE_PAGE          = 'cf_bypass_edd_failure_page';
	public const SETTING_EDD_BYPASS_PURCHASE_HISTORY_PAGE = 'cf_bypass_edd_purchase_history_page';
	public const SETTING_EDD_BYPASS_LOGIN_REDIRECT_PAGE   = 'cf_bypass_edd_login_redirect_page';
	public const SETTING_EDD_AUTO_PURGE_PAYMENT_ADD       = 'cf_auto_purge_edd_payment_add';

	public const SETTING_AUTOPTIMIZE_PURGE_ON_CACHE_FLUSH = 'cf_autoptimize_purge_on_cache_flush';

	public const SETTING_W3TC_PURGE_ON_FLUSH_MINIFY        = 'cf_w3tc_purge_on_flush_minfy';
	public const SETTING_W3TC_PURGE_ON_FLUSH_POSTS         = 'cf_w3tc_purge_on_flush_posts';
	public const SETTING_W3TC_PURGE_ON_FLUSH_OBJECTCACHE   = 'cf_w3tc_purge_on_flush_objectcache';
	public const SETTING_W3TC_PURGE_ON_FLUSH_FRAGMENTCACHE = 'cf_w3tc_purge_on_flush_fragmentcache';
	public const SETTING_W3TC_PURGE_ON_FLUSH_DBCACHE       = 'cf_w3tc_purge_on_flush_dbcache';
	public const SETTING_W3TC_PURGE_ON_FLUSH_ALL           = 'cf_w3tc_purge_on_flush_all';

	public const SETTING_LITESPEED_PURGE_ON_CACHE_FLUSH        = 'cf_litespeed_purge_on_cache_flush';
	public const SETTING_LITESPEED_PURGE_ON_CCSS_FLUSH         = 'cf_litespeed_purge_on_ccss_flush';
	public const SETTING_LITESPEED_PURGE_ON_CSSJS_FLUSH        = 'cf_litespeed_purge_on_cssjs_flush';
	public const SETTING_LITESPEED_PURGE_ON_OBJECT_CACHE_FLUSH = 'cf_litespeed_purge_on_object_cache_flush';
	public const SETTING_LITESPEED_PURGE_ON_SINGLE_POST_FLUSH  = 'cf_litespeed_purge_on_single_post_flush';

	public const SETTING_HUMMINGBIRD_PURGE_ON_CACHE_FLUSH = 'cf_hummingbird_purge_on_cache_flush';

	public const SETTING_WP_OPTIMIZE_PURGE_ON_CACHE_FLUSH = 'cf_wp_optimize_purge_on_cache_flush';

	public const SETTING_FLYPRESS_PURGE_ON_CACHE_FLUSH = 'cf_flypress_purge_on_cache_flush';

	public const SETTING_WP_ROCKET_PURGE_ON_POST_FLUSH               = 'cf_wp_rocket_purge_on_post_flush';
	public const SETTING_WP_ROCKET_PURGE_ON_DOMAIN_FLUSH             = 'cf_wp_rocket_purge_on_domain_flush';
	public const SETTING_WP_ROCKET_PURGE_ON_CACHE_DIR_FLUSH          = 'cf_wp_rocket_purge_on_cache_dir_flush';
	public const SETTING_WP_ROCKET_PURGE_ON_CLEAN_FILES              = 'cf_wp_rocket_purge_on_clean_files';
	public const SETTING_WP_ROCKET_PURGE_ON_CLEAN_CACHE_BUSTING      = 'cf_wp_rocket_purge_on_clean_cache_busting';
	public const SETTING_WP_ROCKET_PURGE_ON_CLEAN_MINIFY             = 'cf_wp_rocket_purge_on_clean_minify';
	public const SETTING_WP_ROCKET_PURGE_ON_CCSS_GENERATION_COMPLETE = 'cf_wp_rocket_purge_on_ccss_generation_complete';
	public const SETTING_WP_ROCKET_PURGE_ON_RUCSS_JOB_COMPLETE       = 'cf_wp_rocket_purge_on_rucss_job_complete';
	public const SETTING_WP_ROCKET_DISABLE_CACHE                     = 'cf_wp_rocket_disable_cache';

	public const SETTING_WP_ACU_PURGE_ON_CACHE_FLUSH = 'cf_wpacu_purge_on_cache_flush';

	public const SETTING_NGINX_HELPER_PURGE_ON_CACHE_FLUSH = 'cf_nginx_helper_purge_on_cache_flush';

	public const SETTING_WP_PERFORMANCE_PURGE_ON_CACHE_FLUSH = 'cf_wp_performance_purge_on_cache_flush';

	public const SETTING_YASR_PURGE_ON_RATING = 'cf_yasr_purge_on_rating';

	public const SETTING_SPL_PURGE_ON_FLUSH_ALL         = 'cf_spl_purge_on_flush_all';
	public const SETTING_SPL_PURGE_ON_FLUSH_SINGLE_POST = 'cf_spl_purge_on_flush_single_post';

	public const SETTING_WPENGINE_PURGE_ON_FLUSH = 'cf_wpengine_purge_on_flush';

	public const SETTING_SPINUPWP_PURGE_ON_FLUSH = 'cf_spinupwp_purge_on_flush';

	public const SETTING_KINSTA_PURGE_ON_FLUSH = 'cf_kinsta_purge_on_flush';

	public const SETTING_SITEGROUND_PURGE_ON_FLUSH = 'cf_siteground_purge_on_flush';

	private const THIRD_PARTY_FIELDS = [
		'woocommerce'       => [
			self::SETTING_WOO_BYPASS_CART_PAGE           => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WOO_BYPASS_CHECKOUT_PAGE       => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WOO_BYPASS_CHECKOUT_PAY_PAGE   => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WOO_BYPASS_PRODUCT_PAGE        => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_WOO_BYPASS_SHOP_PAGE           => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_WOO_BYPASS_PRODUCT_TAX_PAGE    => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_WOO_BYPASS_PRODUCT_TAG_PAGE    => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_WOO_BYPASS_PRODUCT_CAT_PAGE    => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_WOO_BYPASS_PAGES               => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_WOO_BYPASS_ACCOUNT_PAGE        => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WOO_AUTO_PURGE_PRODUCT_PAGE    => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WOO_AUTO_PURGE_SCHEDULED_SALES => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'edd'               => [
			self::SETTING_EDD_BYPASS_CHECKOUT_PAGE         => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_EDD_BYPASS_SUCCESS_PAGE          => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_EDD_BYPASS_FAILURE_PAGE          => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_EDD_BYPASS_PURCHASE_HISTORY_PAGE => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_EDD_BYPASS_LOGIN_REDIRECT_PAGE   => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_EDD_AUTO_PURGE_PAYMENT_ADD       => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'autoptimize'       => [
			self::SETTING_AUTOPTIMIZE_PURGE_ON_CACHE_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'w3tc'              => [
			self::SETTING_W3TC_PURGE_ON_FLUSH_MINIFY      => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_W3TC_PURGE_ON_FLUSH_POSTS       => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_W3TC_PURGE_ON_FLUSH_OBJECTCACHE => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_W3TC_PURGE_ON_FLUSH_FRAGMENTCACHE => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_W3TC_PURGE_ON_FLUSH_DBCACHE     => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
			self::SETTING_W3TC_PURGE_ON_FLUSH_ALL         => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'litespeed_cache'   => [
			self::SETTING_LITESPEED_PURGE_ON_CACHE_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_LITESPEED_PURGE_ON_CCSS_FLUSH  => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_LITESPEED_PURGE_ON_CSSJS_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_LITESPEED_PURGE_ON_OBJECT_CACHE_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_LITESPEED_PURGE_ON_SINGLE_POST_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'hummingbird'       => [
			self::SETTING_HUMMINGBIRD_PURGE_ON_CACHE_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'wp_optimize'       => [
			self::SETTING_WP_OPTIMIZE_PURGE_ON_CACHE_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'flying_press'      => [
			self::SETTING_FLYPRESS_PURGE_ON_CACHE_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'wp_rocket'         => [
			self::SETTING_WP_ROCKET_PURGE_ON_POST_FLUSH   => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WP_ROCKET_PURGE_ON_DOMAIN_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WP_ROCKET_PURGE_ON_CACHE_DIR_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WP_ROCKET_PURGE_ON_CLEAN_FILES  => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WP_ROCKET_PURGE_ON_CLEAN_CACHE_BUSTING => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WP_ROCKET_PURGE_ON_CLEAN_MINIFY => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WP_ROCKET_PURGE_ON_CCSS_GENERATION_COMPLETE => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WP_ROCKET_PURGE_ON_RUCSS_JOB_COMPLETE => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_WP_ROCKET_DISABLE_CACHE         => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => false,
				'default'    => 0,
			],
		],
		'wp_asset_cleanup'  => [
			self::SETTING_WP_ACU_PURGE_ON_CACHE_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'nginx_helper'      => [
			self::SETTING_NGINX_HELPER_PURGE_ON_CACHE_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'wp_performance'    => [
			self::SETTING_WP_PERFORMANCE_PURGE_ON_CACHE_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'yasr'              => [
			self::SETTING_YASR_PURGE_ON_RATING => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 0,
			],
		],
		'swift_performance' => [
			self::SETTING_SPL_PURGE_ON_FLUSH_ALL         => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
			self::SETTING_SPL_PURGE_ON_FLUSH_SINGLE_POST => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'wp_engine'         => [
			self::SETTING_WPENGINE_PURGE_ON_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'spinup_wp'         => [
			self::SETTING_SPINUPWP_PURGE_ON_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'kinsta'            => [
			self::SETTING_KINSTA_PURGE_ON_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
		'siteground'        => [
			self::SETTING_SITEGROUND_PURGE_ON_FLUSH => [
				'type'       => Settings_Manager::SETTING_TYPE_BOOLEAN,
				'bust_cache' => true,
				'default'    => 1,
			],
		],
	];

	public function init() {
		add_filter( 'spc_additional_settings_fields', [ $this, 'attach_fields' ] );
	}

	/**
	 * Attach third-party fields to the settings handler.
	 *
	 * @param array $fields Fields to add.
	 *
	 * @return array
	 */
	public function attach_fields( array $fields ): array {
		return array_merge( $fields, ...array_values( self::THIRD_PARTY_FIELDS ) );
	}
}
