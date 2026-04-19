<?php

namespace SPC;

class Constants {
	public const SETTING_NATIVE_LAZY_LOADING         = 'cf_native_lazy_loading';
	public const SETTING_LAZY_LOADING                = 'cf_lazy_loading';
	public const SETTING_LAZY_LOAD_VIDEO_IFRAME      = 'cf_lazy_load_video_iframe';
	public const SETTING_LAZY_LOAD_SKIP_IMAGES       = 'cf_lazy_load_skip_images';
	public const SETTING_LAZY_EXCLUDED               = 'cf_lazy_load_excluded';
	public const SETTING_LAZY_LOAD_BG                = 'cf_lazy_load_bg';
	public const SETTING_LAZY_LOAD_BG_SELECTORS      = 'cf_lazy_load_bg_selectors';
	public const SETTING_OPTIMIZE_GOOGLE_FONTS       = 'optimize_google_fonts';
	public const SETTING_LOCAL_GOOGLE_FONTS          = 'local_google_fonts';
	public const SETTING_LAZY_LOAD_BEHAVIOUR         = 'cf_lazy_load_behaviour';
	public const SETTING_EXCLUDED_COOKIES            = 'cf_fallback_cache_excluded_cookies';
	public const SETTING_EXCLUDED_URLS               = 'cf_fallback_cache_excluded_urls';
	public const SETTING_AUTO_PURGE                  = 'cf_auto_purge';
	public const SETTING_AUTO_PURGE_WHOLE            = 'cf_auto_purge_all';
	public const SETTING_PURGE_ON_COMMENT            = 'cf_auto_purge_on_comments';
	public const SETTING_PRELOAD_SITEMAPS_URLS       = 'cf_preload_sitemap_urls';
	public const SETTING_PREFETCH_ON_HOVER           = 'cf_prefetch_urls_on_hover';
	public const SETTING_REMOVE_CACHE_BUSTER         = 'cf_remove_cache_buster';
	public const SETTING_SHOW_ADVANCED               = 'show_advanced';
	public const SETTING_KEEP_ON_DEACTIVATION        = 'keep_settings_on_deactivation';
	public const SETTING_BROWSER_CACHE_STATIC_ASSETS = 'cf_browser_caching_htaccess';
	public const SETTING_ENABLE_FALLBACK_CACHE       = 'cf_fallback_cache';

	public const SETTING_VARNISH_SUPPORT          = 'cf_varnish_support';
	public const SETTING_VARNISH_AUTO_PURGE       = 'cf_varnish_auto_purge';
	public const SETTING_VARNISH_HOSTNAME         = 'cf_varnish_hostname';
	public const SETTING_VARNISH_PORT             = 'cf_varnish_port';
	public const SETTING_VARNISH_ON_CLOWDWAYS     = 'cf_varnish_cw';
	public const SETTING_VARNISH_PURGE_METHOD     = 'cf_varnish_purge_method';
	public const SETTING_VARNISH_PURGE_ALL_METHOD = 'cf_varnish_purge_all_method';

	public const SETTING_ENABLE_PRELOADER         = 'cf_preloader';
	public const SETTING_PRELOADER_START_ON_PURGE = 'cf_preloader_start_on_purge';
	public const SETTING_PRELOADER_NAV_MENUS      = 'cf_preloader_nav_menus';
	public const SETTING_PRELOAD_LAST_URLS        = 'cf_preload_last_urls';
	public const SETTING_PRELOAD_CRONJOB_SECRET   = 'cf_preloader_url_secret_key';

	public const SETTING_FALLBACK_CACHE_CURL                       = 'cf_fallback_cache_curl';
	public const SETTING_FALLBACK_CACHE_LIFESPAN                   = 'cf_fallback_cache_ttl';
	public const SETTING_FALLBACK_CACHE_SAVE_HEADERS               = 'cf_fallback_cache_save_headers';
	public const SETTING_FALLBACK_CACHE_PREVENT_TRAILING_SLASH     = 'cf_fallback_cache_prevent_cache_urls_without_trailing_slash';
	public const SETTING_FALLBACK_CACHE_PURGE_ON_UPGRADER_COMPLETE = 'cf_auto_purge_on_upgrader_process_complete';
	public const SETTING_STRIP_RESPONSE_COOKIES                    = 'cf_strip_cookies';
	public const SETTING_OVERWRITE_WITH_HTACCESS                   = 'cf_cache_control_htaccess';
	public const SETTING_PURGE_ONLY_HTML                           = 'cf_purge_only_html';
	public const SETTING_DISABLE_PURGING_QUEUE                     = 'cf_disable_cache_purging_queue';
	public const SETTING_FALLBACK_CACHE_HTTP_RESPONSE_CODE         = 'cf_fallback_cache_http_response_code';

	// Bypass Settings.
	public const SETTING_BYPASS_404          = 'cf_bypass_404';
	public const SETTING_BYPASS_SINGLE_POST  = 'cf_bypass_single_post';
	public const SETTING_BYPASS_PAGES        = 'cf_bypass_pages';
	public const SETTING_BYPASS_FRONT_PAGE   = 'cf_bypass_front_page';
	public const SETTING_BYPASS_HOME         = 'cf_bypass_home';
	public const SETTING_BYPASS_ARCHIVES     = 'cf_bypass_archives';
	public const SETTING_BYPASS_TAGS         = 'cf_bypass_tags';
	public const SETTING_BYPASS_CATEGORY     = 'cf_bypass_category';
	public const SETTING_BYPASS_FEEDS        = 'cf_bypass_feeds';
	public const SETTING_BYPASS_SEARCH_PAGES = 'cf_bypass_search_pages';
	public const SETTING_BYPASS_AUTHOR_PAGES = 'cf_bypass_author_pages';
	public const SETTING_BYPASS_AMP          = 'cf_bypass_amp';
	public const SETTING_BYPASS_AJAX         = 'cf_bypass_ajax';
	public const SETTING_BYPASS_QUERY_VAR    = 'cf_bypass_query_var';
	public const SETTING_BYPASS_WP_JSON_REST = 'cf_bypass_wp_json_rest';

	public const SETTING_BYPASS_SITEMAP    = 'cf_bypass_sitemap';
	public const SETTING_BYPASS_ROBOTS_TXT = 'cf_bypass_file_robots';

	public const SETTING_POSTS_PER_PAGE            = 'cf_post_per_page';
	public const SETTING_CACHE_MAX_AGE             = 'cf_maxage';
	public const SETTING_BROWSER_CACHE_MAX_AGE     = 'cf_browser_maxage';
	public const SETTING_FALLBACK_CACHE_AUTO_PURGE = 'cf_fallback_cache_auto_purge';

	public const SETTING_BYPASS_BACKEND_WITH_RULE = 'cf_bypass_backend_page_rule';

	public const SETTING_LOG_ENABLED      = 'log_enabled';
	public const SETTING_LOG_MAX_FILESIZE = 'log_max_file_size';
	public const SETTING_LOG_VERBOSITY    = 'log_verbosity';

	public const SETTING_UNUSED_CSS                  = 'unused_css';
	public const SETTING_UNUSED_CSS_EXCLUDED_PATHS   = 'unused_css_excluded_paths';
	public const SETTING_UNUSED_CSS_EXCLUDED_CSS     = 'unused_css_excluded_css';
	public const SETTING_OBJECT_CACHE_PURGE_ON_FLUSH = 'cf_object_cache_purge_on_flush';
	public const SETTING_OPCACHE_PURGE_ON_FLUSH      = 'cf_opcache_purge_on_flush';
	public const SETTING_PURGE_URL_SECRET_KEY        = 'cf_purge_url_secret_key';
	public const SETTING_REMOVE_PURGE_OPTION_TOOLBAR = 'cf_remove_purge_option_toolbar';
	public const SETTING_DISABLE_SINGLE_METABOX      = 'cf_disable_single_metabox';
	public const SETTING_SEO_REDIRECT                = 'cf_seo_redirect';
	public const SETTING_PURGE_ROLES                 = 'cf_purge_roles';
	public const SETTING_PREFETCH_URLS_VIEWPORT      = 'cf_prefetch_urls_viewport';
	public const SETTING_PREFETCH_URLS_TIMESTAMP     = 'cf_prefetch_urls_viewport_timestamp';

	public const SETTING_AUTH_MODE      = 'cf_auth_mode';
	public const SETTING_CF_EMAIL       = 'cf_email';
	public const SETTING_CF_API_KEY     = 'cf_apikey';
	public const SETTING_CF_API_TOKEN   = 'cf_apitoken';
	public const SETTING_CF_DOMAIN_NAME = 'cf_apitoken_domain';

	public const SETTING_CF_ZONE_ID = 'cf_zoneid';
	public const ZONE_ID_LIST       = 'cf_zoneid_list';

	public const RULE_ID_BYPASS_BACKEND = 'cf_bypass_backend_page_rule_id';
	public const RULE_ID_PAGE           = 'cf_page_rule_id';

	public const RULE_ID_CACHE    = 'cf_cache_settings_ruleset_rule_id';
	public const RULESET_ID_CACHE = 'cf_cache_settings_ruleset_id';


	public const SETTING_CF_CACHE_ENABLED = 'cf_cache_enabled';

	public const SETTING_OLD_BC_TTL = 'cf_old_bc_ttl';
	public const ENABLE_CACHE_RULE  = 'enable_cache_rule';

	public const SETTING_ENABLE_ASSETS_MANAGER = 'enable_assets_manager';

	// Defaults
	public const DEFAULT_PRELOADED_SITEMAPS_URLS = [
		'/wp-sitemap.xml',
	];
	public const DEFAULT_LAZY_LOAD_EXCLUSIONS    = [
		'skip-lazy',
	];
	public const DEFAULT_BG_LAZYLOAD_SELECTORS   = [
		'[style*="background-image:"]',
		'[class*="elementor"][data-settings*="background_background"]',
		'.elementor-section > .elementor-background-overlay',
		'[class*="wp-block-cover"][style*="background-image"]',
		'[class*="wp-block-group"][style*="background-image"]',
	];
	public const COMPAT_BG_LAZYLOAD_SELECTORS    = [
		'otter-blocks/otter-blocks.php'                 => [
			'.o-flip-front',
			'.o-flip-back',
			'.wp-block-themeisle-blocks-advanced-columns',
			'.wp-block-themeisle-blocks-advanced-columns-overlay',
			'.wp-block-themeisle-blocks-advanced-column',
			'.wp-block-themeisle-blocks-advanced-column-overlay',
		],
		'bb-plugin/fl-builder.php'                      => [
			'.fl-col-content',
			'.fl-row-bg-photo > .fl-row-content-wrap',
		],
		'beaver-builder-lite-version/fl-builder.php'    => [
			'.fl-col-content',
			'.fl-row-bg-photo > .fl-row-content-wrap',
		],
		'divi-builder/divi-builder.php'                 => [
			'.et_pb_slides > .et_pb_slide',
			'.et_parallax_bg',
			'.et_pb_video_overlay',
			'.et_pb_module:not([class*="et_pb_blog"])',
			'.et_pb_row',
			'.et_pb_section.et_pb_section_1',
			'.et_pb_with_background',
		],
		'elementor/elementor.php'                       => [
			'.elementor-widget-container',
			'.elementor-background-slideshow__slide__image',
		],
		'essential-grid/essential-grid.php'             => [
			'.esg-media-poster',
		],
		'master-slider/master-slider.php'               => [
			'.master-slider',
		],
		'ml-slider/ml-slider.php'                       => [
			'.coin-slider > .coin-slider > a',
			'.coin-slider > .coin-slider',
		],
		'ml-slider-pro/ml-slider-pro.php'               => [
			'.coin-slider > .coin-slider > a',
			'.coin-slider > .coin-slider',
		],
		'revslider/revslider.php'                       => [
			'.tp-bgimg',
		],
		'thrive-visual-editor/thrive-visual-editor.php' => [
			'.tve-content-box-background',
			'.tve-page-section-out',
			'.thrv_text_element',
		],
	];
	public const DEFAULT_EXCLUDED_COOKIES        = [
		'comment_',
		'woocommerce_',
		'wordpress',
		'xf_',
		'edd_',
		'jetpack',
		'yith_wcwl_session_',
		'yith_wrvp_',
		'wpsc_',
		'ecwid',
		'ec_',
		'bookly',
	];

	public const DEFAULT_EXCLUDED_URLS = [
		'/*ao_noptirocket*',
		'/*jetpack=comms*',
		'/*kinsta-monitor*',
		'*ao_speedup_cachebuster*',
		'/*removed_item*',
		'/my-account*',
		'/wc-api/*',
		'/edd-api/*',
		'/wp-json*',
	];

	/**
	 * Sorting Tool: https://onlinestringtools.com/sort-strings
	 * Duplicate Finder: https://www.mynikko.com/tools/tool_duplicateremover.html
	 * Special Thanks: https://github.com/mpchadwick/tracking-query-params-registry/blob/master/_data/params.csv
	 */
	public const IGNORED_QUERY_PARAMS = [
		'Browser',
		'C',
		'GCCON',
		'MCMP',
		'MarketPlace',
		'PD',
		'Refresh',
		'Sens',
		'ServiceVersion',
		'Source',
		'Topic',
		'__WB_REVISION__',
		'__cf_chl_jschl_tk__',
		'__d',
		'__hsfp',
		'__hssc',
		'__hstc',
		'__s',
		'_branch_match_id',
		'_bta_c',
		'_bta_tid',
		'_com',
		'_escaped_fragment_',
		'_ga',
		'_ga-ft',
		'_gl',
		'_hsmi',
		'_ke',
		'_kx',
		'_paged',
		'_sm_byp',
		'_sp',
		'_szp',
		'_thumbnail_id',
		'3x',
		'a',
		'a_k',
		'ac',
		'acpage',
		'action-box',
		'action_object_map',
		'action_ref_map',
		'action_type_map',
		'activecampaign_id',
		'ad',
		'ad_frame_full',
		'ad_frame_root',
		'ad_name',
		'adclida',
		'adid',
		'adlt',
		'adsafe_ip',
		'adset_name',
		'advid',
		'aff_sub2',
		'afftrack',
		'afterload',
		'ak_action',
		'alt_id',
		'am',
		'amazingmurphybeds',
		'amp;',
		'amp;amp',
		'amp;amp;amp',
		'amp;amp;amp;amp',
		'amp;utm_campaign',
		'amp;utm_medium',
		'amp;utm_source',
		'amp%3Butm_content',
		'ampStoryAutoAnalyticsLinker',
		'ampstoryautoanalyticslinke',
		'an',
		'ap',
		'ap_id',
		'apif',
		'apipage',
		'as_occt',
		'as_q',
		'as_qdr',
		'askid',
		'atFileReset',
		'atfilereset',
		'aucid',
		'auct',
		'audience',
		'author',
		'awt_a',
		'awt_l',
		'awt_m',
		'b2w',
		'back',
		'bannerID',
		'blackhole',
		'blockedAdTracking',
		'blog-reader-used',
		'blogger',
		'body',
		'br',
		'bsft_aaid',
		'bsft_clkid',
		'bsft_eid',
		'bsft_ek',
		'bsft_lx',
		'bsft_mid',
		'bsft_mime_type',
		'bsft_tv',
		'bsft_uid',
		'bvMethod',
		'bvTime',
		'bvVersion',
		'bvb64',
		'bvb64resp',
		'bvplugname',
		'bvprms',
		'bvprmsmac',
		'bvreqmerge',
		'cacheburst',
		'campaign',
		'campaign_id',
		'campaign_name',
		'campid',
		'catablog-gallery',
		'channel',
		'checksum',
		'ck_subscriber_id',
		'cmplz_region_redirect',
		'cmpnid',
		'cn-reloaded',
		'code',
		'comment',
		'content_ad_widget',
		'cost',
		'cr',
		'crl8_id',
		'crlt.pid',
		'crlt_pid',
		'crrelr',
		'crtvid',
		'ct',
		'cuid',
		'daksldlkdsadas',
		'dcc',
		'dfp',
		'dm_i',
		'domain',
		'dosubmit',
		'dsp_caid',
		'dsp_crid',
		'dsp_insertion_order_id',
		'dsp_pub_id',
		'dsp_tracker_token',
		'dt',
		'dur',
		'durs',
		'e',
		'ee',
		'ef_id',
		'el',
		'emailID',
		'env',
		'epik',
		'erprint',
		'et_blog',
		'exch',
		'externalid',
		'fb_action_ids',
		'fb_action_types',
		'fb_ad',
		'fb_source',
		'fbclid',
		'fbzunique',
		'fg-aqp',
		'fireglass_rsn',
		'firstName',
		'fo',
		'fp_sid',
		'fpa',
		'fref',
		'fs',
		'furl',
		'fwp_lunch_restrictions',
		'ga_action',
		'gclid',
		'gclsrc',
		'gdffi',
		'gdfms',
		'gdftrk',
		'gf_page',
		'gidzl',
		'goal',
		'gooal',
		'gpu',
		'gtVersion',
		'haibwc',
		'hash',
		'hc_location',
		'hemail',
		'hid',
		'highlight',
		'hl',
		'home',
		'hsa_acc',
		'hsa_ad',
		'hsa_cam',
		'hsa_grp',
		'hsa_kw',
		'hsa_mt',
		'hsa_net',
		'hsa_src',
		'hsa_tgt',
		'hsa_ver',
		'ias_campId',
		'ias_chanId',
		'ias_dealId',
		'ias_dspId',
		'ias_impId',
		'ias_placementId',
		'ias_pubId',
		'ical',
		'ict',
		'ie',
		'igshid',
		'im',
		'ipl',
		'jw_start',
		'jwsource',
		'k',
		'key1',
		'key2',
		'klaviyo',
		'ksconf',
		'ksref',
		'l',
		'label',
		'lang',
		'ldtag_cl',
		'level1',
		'level2',
		'level3',
		'level4',
		'limit',
		'lng',
		'load_all_comments',
		'lt',
		'ltclid',
		'ltd',
		'lucky',
		'm',
		'm?sales_kw',
		'matomo_campaign',
		'matomo_cid',
		'matomo_content',
		'matomo_group',
		'matomo_keyword',
		'matomo_medium',
		'matomo_placement',
		'matomo_source',
		'max-results',
		'mc_cid',
		'mc_eid',
		'mdrv',
		'mediaserver',
		'memset',
		'mibextid',
		'mkcid',
		'mkevt',
		'mkrid',
		'mkwid',
		'mkt_tok',
		'ml_subscriber',
		'ml_subscriber_hash',
		'mobileOn',
		'mode',
		'moderation-hash',
		'modernpatio',
		'month',
		'msID',
		'msclkid',
		'msg',
		'mtm_campaign',
		'mtm_cid',
		'mtm_content',
		'mtm_group',
		'mtm_keyword',
		'mtm_medium',
		'mtm_placement',
		'mtm_source',
		'murphybedstoday',
		'mwprid',
		'n',
		'name',
		'native_client',
		'navua',
		'nb',
		'nb_klid',
		'nowprocketcache',
		'o',
		'okijoouuqnqq',
		'org',
		'pa_service_worker',
		'partnumber',
		'pcmtid',
		'pcode',
		'pcrid',
		'pfstyle',
		'phrase',
		'pid',
		'piwik_campaign',
		'piwik_keyword',
		'piwik_kwd',
		'pk_campaign',
		'pk_keyword',
		'pk_kwd',
		'placement',
		'plat',
		'platform',
		'playsinline',
		'position',
		'pp',
		'pr',
		'preview',
		'preview_id',
		'preview_nonce',
		'prid',
		'print',
		'q',
		'q1',
		'qsrc',
		'r',
		'rd',
		'rdt_cid',
		'redig',
		'redir',
		'ref',
		'reftok',
		'relatedposts_hit',
		'relatedposts_origin',
		'relatedposts_position',
		'remodel',
		'replytocom',
		'rest_route',
		'reverse-paginate',
		'rid',
		'rnd',
		'rndnum',
		'robots_txt',
		'rq',
		'rsd',
		's_kwcid',
		'sa',
		'safe',
		'said',
		'sales_cat',
		'sales_kw',
		'sb_referer_host',
		'scrape',
		'script',
		'scrlybrkr',
		'search',
		'sellid',
		'sersafe',
		'sfn_data',
		'sfn_trk',
		'sfns',
		'sfw',
		'sha1',
		'share',
		'shared',
		'showcomment',
		'showComment',
		'si',
		'sid',
		'sid1',
		'sid2',
		'sidewalkShow',
		'sig',
		'site',
		'site_id',
		'siteid',
		'slicer1',
		'slicer2',
		'source',
		'spref',
		'spvb',
		'sra',
		'src',
		'srk',
		'srp',
		'ssp_iabi',
		'ssts',
		'stylishmurphybeds',
		'subId1 ',
		'subId2 ',
		'subId3',
		'subid',
		'swcfpc',
		'tail',
		'teaser',
		'test',
		'timezone',
		'toWww',
		'triplesource',
		'trk_contact',
		'trk_module',
		'trk_msg',
		'trk_sid',
		'tsig',
		'turl',
		'u',
		'unapproved',
		'up_auto_log',
		'upage',
		'updated-max',
		'uptime',
		'us_privacy',
		'usegapi',
		'userConsent',
		'usqp',
		'utm',
		'utm_campa',
		'utm_campaign',
		'utm_content',
		'utm_expid',
		'utm_id',
		'utm_medium',
		'utm_reader',
		'utm_referrer',
		'utm_source',
		'utm_sq',
		'utm_ter',
		'utm_term',
		'v',
		'vc',
		'vf',
		'vgo_ee',
		'vp',
		'vrw',
		'vz',
		'wbraid',
		'webdriver',
		'wing',
		'wpdParentID',
		'wpmp_switcher',
		'wref',
		'wswy',
		'wtime',
		'x',
		'zMoatImpID',
		'zarsrc',
		'zeffdn',
	];

	public const KEY_RULE_UPDATE_FAILED = 'cf_rule_update_failed';

	public const PRELOAD_EXCLUDED_POST_TYPES = [
		'attachment',
		'jet-menu',
		'elementor_library',
		'jet-theme-core',
	];

	public const SETTING_ENABLE_DATABASE_OPTIMIZATION = 'database_optimization';

	public const SETTING_POST_REVISION_INTERVAL = 'post_revision_interval';

	public const SETTING_AUTO_DRAFT_POST_INTERVAL = 'auto_draft_post_interval';

	public const SETTING_TRASHED_POST_INTERVAL = 'trashed_post_interval';

	public const SETTING_SPAM_COMMENT_INTERVAL = 'spam_comment_interval';

	public const SETTING_TRASHED_COMMENT_INTERVAL = 'trashed_comment_interval';

	public const SETTING_ALL_TRANSIENT_INTERVAL = 'all_transients_interval';

	public const SETTING_OPTIMIZE_TABLE_INTERVAL = 'optimize_tables_interval';

	public const ACTION_SCHEDULER_GROUP = 'spc';

	/**
	 * @deprecated 5.1.0 - Worker mode is not supported anymore after
	 *
	 * @var string
	 */
	public const WORKER_ID = 'cf_woker_id';
	/**
	 * @deprecated 5.1.0 - Worker mode is not supported anymore after
	 *
	 * @var string
	 */
	public const SETTING_WORKER_ENABLED = 'cf_woker_enabled';
	/**
	 * @deprecated 5.1.0 - Worker mode is not supported anymore after
	 *
	 * @var string
	 */
	public const SETTING_WORKER_EXCLUDED_COOKIES = 'cf_worker_bypass_cookies';
}
