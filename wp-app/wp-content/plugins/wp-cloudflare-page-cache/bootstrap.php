<?php
/**
 * Plugin bootstrap file.
 */

if ( ! class_exists( 'SW_CLOUDFLARE_PAGECACHE' ) ) {
	define( 'SPC_PATH', defined( 'SPC_PRO_PATH' ) ? SPC_PRO_PATH : SPC_FREE_PATH );

	define( 'SWCFPC_PLUGIN_PATH', plugin_dir_path( SPC_PATH ) );
	define( 'SWCFPC_PLUGIN_URL', plugin_dir_url( SPC_PATH ) );
	define( 'SWCFPC_BASEFILE', SPC_PATH );
	define( 'SWCFPC_PLUGIN_REVIEWS_URL', 'https://wordpress.org/support/plugin/wp-cloudflare-page-cache/reviews/' );
	define( 'SWCFPC_PLUGIN_FORUM_URL', 'https://wordpress.org/support/plugin/wp-cloudflare-page-cache/' );
	define( 'SWCFPC_AUTH_MODE_API_KEY', 0 );
	define( 'SWCFPC_AUTH_MODE_API_TOKEN', 1 );
	define( 'SWCFPC_LOGS_STANDARD_VERBOSITY', 1 );
	define( 'SWCFPC_LOGS_HIGH_VERBOSITY', 2 );

	if ( ! defined( 'SWCFPC_PRELOADER_MAX_POST_NUMBER' ) ) {
		define( 'SWCFPC_PRELOADER_MAX_POST_NUMBER', 50 );
	}

	if ( ! defined( 'SWCFPC_CACHE_BUSTER' ) ) {
		define( 'SWCFPC_CACHE_BUSTER', 'swcfpc' );
	}

	if ( ! defined( 'SWCFPC_CURL_TIMEOUT' ) ) {
		define( 'SWCFPC_CURL_TIMEOUT', 10 );
	}

	if ( ! defined( 'SWCFPC_PURGE_CACHE_LOCK_SECONDS' ) ) {
		define( 'SWCFPC_PURGE_CACHE_LOCK_SECONDS', 10 );
	}

	if ( ! defined( 'SWCFPC_HOME_PAGE_SHOWS_POSTS' ) ) {
		define( 'SWCFPC_HOME_PAGE_SHOWS_POSTS', true );
	}

	class SW_CLOUDFLARE_PAGECACHE {

		private $config  = false;
		private $modules = [];
		private $version = '5.0.4';

		// Sorting Tool: https://onlinestringtools.com/sort-strings
		// Duplicate Finder: https://www.mynikko.com/tools/tool_duplicateremover.html
		// Special Thanks: https://github.com/mpchadwick/tracking-query-params-registry/blob/master/_data/params.csv
		private $ignored_query_params = [
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

		function __construct() {
			add_action( 'admin_init', [ $this, 'maybe_deactivate_free' ] );

			// add_action( 'plugins_loaded', array($this, 'update_plugin') );
			register_deactivation_hook( SPC_PATH, [ $this, 'deactivate_plugin' ] );

			if ( ! $this->init_config() ) {
				$this->config = $this->get_default_config();
				$this->update_config();
			}

			if ( ! file_exists( $this->get_plugin_wp_content_directory() ) ) {
				$this->create_plugin_wp_content_directory();
			}

			$this->update_plugin();
			$this->include_libs();
			$this->actions();

		}


		function load_textdomain() {

			load_plugin_textdomain( 'wp-cloudflare-page-cache', false, basename( dirname( SPC_PATH ) ) . '/languages/' );

		}


		function include_libs() { 
			if ( count( $this->modules ) > 0 ) {
				return;
			}

			$this->modules = [];

			include_once ABSPATH . 'wp-includes/pluggable.php';

			// Composer autoload.
			if ( file_exists( SWCFPC_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
				require SWCFPC_PLUGIN_PATH . 'vendor/autoload.php';
			}

			require_once SWCFPC_PLUGIN_PATH . 'libs/preloader.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/cloudflare.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/logs.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/cache_controller.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/backend.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/fallback_cache.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/varnish.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/html_cache.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/test_cache.class.php';

			$log_file_path = $this->get_plugin_wp_content_directory() . '/debug.log';
			$log_file_url  = $this->get_plugin_wp_content_directory_url() . '/debug.log';

			$this->modules = apply_filters( 'swcfpc_include_libs_early', $this->modules );

			if ( $this->get_single_config( 'log_enabled', 0 ) > 0 ) {
				$this->modules['logs'] = new SWCFPC_Logs( $log_file_path, $log_file_url, true, $this->get_single_config( 'log_max_file_size', 2 ), $this );
			} else {
				$this->modules['logs'] = new SWCFPC_Logs( $log_file_path, $log_file_url, false, $this->get_single_config( 'log_max_file_size', 2 ), $this );
			}

			$this->modules['logs']->set_verbosity( $this->get_single_config( 'log_verbosity', SWCFPC_LOGS_HIGH_VERBOSITY ) );

			$this->modules['cloudflare'] = new SWCFPC_Cloudflare( $this );

			$this->modules['fallback_cache']   = new SWCFPC_Fallback_Cache( $this );
			$this->modules['html_cache']       = new SWCFPC_Html_Cache( $this );
			$this->modules['cache_controller'] = new SWCFPC_Cache_Controller( SWCFPC_CACHE_BUSTER, $this );
			$this->modules['varnish']          = new SWCFPC_Varnish( $this );
			$this->modules['backend']          = new SWCFPC_Backend( $this );

			if ( ( ! defined( 'WP_CLI' ) || ( defined( 'WP_CLI' ) && WP_CLI === false ) ) && isset( $_SERVER['REQUEST_METHOD'] ) && strcasecmp( $_SERVER['REQUEST_METHOD'], 'GET' ) == 0 && ! is_admin() && ! $this->is_login_page() && $this->get_single_config( 'cf_fallback_cache', 0 ) > 0 && $this->modules['cache_controller']->is_cache_enabled() ) {
				$this->modules['fallback_cache']->fallback_cache_retrive_current_page();
			}

			$this->modules = apply_filters( 'swcfpc_include_libs_lately', $this->modules );

			// Inizializzo qui la classe del preloader in quanto questo metodo viene richiamato all'evento plugin_loaded. Dopodiche' posso stanziare l'oggetto anche in chiamate Ajax
			new SWCFPC_Preloader_Process( $this );

			$this->enable_wp_cli_support();

			$this->maybe_load_pro_modules();

			new SPC\Loader();
		}


		function actions() {
			add_filter( 'themeisle_sdk_products', [ $this, 'load_sdk' ] );
			add_filter( 'plugin_action_links_' . plugin_basename( SPC_PATH ), [ $this, 'add_plugin_action_links' ] );
			add_filter( 'plugin_row_meta', [ $this, 'add_plugin_meta_links' ], 10, 2 );

			// Multilanguage
			add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

			add_action( 'wp_ajax_swcfpc_test_page_cache', [ $this, 'ajax_test_page_cache' ] );

		}

		function load_sdk( $products ) {
			$products[] = SWCFPC_BASEFILE;
			return $products;
		}


		function get_default_config() {

			$config = [];

			// Cloudflare config
			$config['cf_zoneid']                          = '';
			$config['cf_zoneid_list']                     = [];
			$config['cf_email']                           = '';
			$config['cf_apitoken']                        = '';
			$config['cf_apikey']                          = '';
			$config['cf_token']                           = '';
			$config['cf_apitoken_domain']                 = $this->get_second_level_domain();
			$config['cf_old_bc_ttl']                      = '';
			$config['cf_page_rule_id']                    = '';
			$config['cf_bypass_backend_page_rule_id']     = '';
			$config['cf_bypass_backend_page_rule']        = 0;
			$config['cf_auto_purge']                      = 1;
			$config['cf_auto_purge_all']                  = 0;
			$config['cf_auto_purge_on_comments']          = 0;
			$config['cf_cache_enabled']                   = 0;
			$config['cf_maxage']                          = 31536000; // 1 year
			$config['cf_browser_maxage']                  = 60; // 1 minute
			$config['cf_post_per_page']                   = get_option( 'posts_per_page', 0 );
			$config['cf_purge_url_secret_key']            = $this->generate_password( 20, false, false );
			$config['cf_strip_cookies']                   = 0;
			$config['cf_fallback_cache']                  = 0;
			$config['cf_fallback_cache_ttl']              = 0;
			$config['cf_fallback_cache_auto_purge']       = 1;
			$config['cf_fallback_cache_curl']             = 0;
			$config['cf_fallback_cache_excluded_urls']    = [];
			$config['cf_fallback_cache_excluded_cookies'] = [ '^wordpress_logged_in_', '^wp-', '^comment_', '^woocommerce_', '^wordpressuser_', '^wordpresspass_', '^wordpress_sec_' ];
			$config['cf_fallback_cache_save_headers']     = 0;
			$config['cf_fallback_cache_prevent_cache_urls_without_trailing_slash'] = 1;
			$config['cf_preloader']                               = 1;
			$config['cf_preloader_start_on_purge']                = 1;
			$config['cf_preloader_nav_menus']                     = [];
			$config['cf_preload_last_urls']                       = 1;
			$config['cf_preload_excluded_post_types']             = [ 'attachment', 'jet-menu', 'elementor_library', 'jet-theme-core' ];
			$config['cf_preload_sitemap_urls']                    = [];
			$config['cf_woker_enabled']                           = 0;
			$config['cf_woker_id']                                = 'swcfpc_worker_' . time();
			$config['cf_woker_route_id']                          = '';
			$config['cf_worker_bypass_cookies']                   = [];
			$config['cf_purge_only_html']                         = 0;
			$config['cf_disable_cache_purging_queue']             = 0;
			$config['cf_auto_purge_on_upgrader_process_complete'] = 0;

			// Pages
			$config['cf_excluded_urls']            = [ '/*ao_noptirocket*', '/*jetpack=comms*', '/*kinsta-monitor*', '*ao_speedup_cachebuster*', '/*removed_item*', '/my-account*', '/wc-api/*', '/edd-api/*', '/wp-json*' ];
			$config['cf_bypass_front_page']        = 0;
			$config['cf_bypass_pages']             = 0;
			$config['cf_bypass_home']              = 0;
			$config['cf_bypass_archives']          = 0;
			$config['cf_bypass_tags']              = 0;
			$config['cf_bypass_category']          = 0;
			$config['cf_bypass_author_pages']      = 0;
			$config['cf_bypass_single_post']       = 0;
			$config['cf_bypass_feeds']             = 1;
			$config['cf_bypass_search_pages']      = 1;
			$config['cf_bypass_404']               = 1;
			$config['cf_bypass_logged_in']         = 1;
			$config['cf_bypass_amp']               = 0;
			$config['cf_bypass_file_robots']       = 1;
			$config['cf_bypass_sitemap']           = 1;
			$config['cf_bypass_ajax']              = 1;
			$config['cf_cache_control_htaccess']   = 0;
			$config['cf_browser_caching_htaccess'] = 0;
			$config['cf_auth_mode']                = SWCFPC_AUTH_MODE_API_KEY;
			// $config['cf_bypass_post']                   = 0;
			$config['cf_bypass_query_var']    = 0;
			$config['cf_bypass_wp_json_rest'] = 0;

			// Ruleset
			$config['cf_cache_settings_ruleset_id']      = '';
			$config['cf_cache_settings_ruleset_rule_id'] = '';

			// Varnish
			$config['cf_varnish_support']          = 0;
			$config['cf_varnish_auto_purge']       = 1;
			$config['cf_varnish_hostname']         = 'localhost';
			$config['cf_varnish_port']             = 6081;
			$config['cf_varnish_cw']               = 0;
			$config['cf_varnish_purge_method']     = 'PURGE';
			$config['cf_varnish_purge_all_method'] = 'PURGE';

			// WooCommerce
			$config['cf_bypass_woo_shop_page']           = 0;
			$config['cf_bypass_woo_pages']               = 0;
			$config['cf_bypass_woo_product_tax_page']    = 0;
			$config['cf_bypass_woo_product_tag_page']    = 0;
			$config['cf_bypass_woo_product_cat_page']    = 0;
			$config['cf_bypass_woo_product_page']        = 0;
			$config['cf_bypass_woo_cart_page']           = 1;
			$config['cf_bypass_woo_checkout_page']       = 1;
			$config['cf_bypass_woo_checkout_pay_page']   = 1;
			$config['cf_auto_purge_woo_product_page']    = 1;
			$config['cf_auto_purge_woo_scheduled_sales'] = 1;
			$config['cf_bypass_woo_account_page']        = 1;

			// Swift Performance (Lite/Pro)
			$config['cf_spl_purge_on_flush_all']         = 1;
			$config['cf_spl_purge_on_flush_single_post'] = 1;

			// W3TC
			$config['cf_w3tc_purge_on_flush_minfy']         = 0;
			$config['cf_w3tc_purge_on_flush_posts']         = 0;
			$config['cf_w3tc_purge_on_flush_objectcache']   = 0;
			$config['cf_w3tc_purge_on_flush_fragmentcache'] = 0;
			$config['cf_w3tc_purge_on_flush_dbcache']       = 0;
			$config['cf_w3tc_purge_on_flush_all']           = 1;

			// WP Rocket
			$config['cf_wp_rocket_purge_on_post_flush']               = 1;
			$config['cf_wp_rocket_purge_on_domain_flush']             = 1;
			$config['cf_wp_rocket_purge_on_cache_dir_flush']          = 1;
			$config['cf_wp_rocket_purge_on_clean_files']              = 1;
			$config['cf_wp_rocket_purge_on_clean_cache_busting']      = 1;
			$config['cf_wp_rocket_purge_on_clean_minify']             = 1;
			$config['cf_wp_rocket_purge_on_ccss_generation_complete'] = 1;
			$config['cf_wp_rocket_purge_on_rucss_job_complete']       = 1;

			// Litespeed Cache
			$config['cf_litespeed_purge_on_cache_flush']        = 1;
			$config['cf_litespeed_purge_on_ccss_flush']         = 1;
			$config['cf_litespeed_purge_on_cssjs_flush']        = 1;
			$config['cf_litespeed_purge_on_object_cache_flush'] = 1;
			$config['cf_litespeed_purge_on_single_post_flush']  = 1;

			// Flying Press
			$config['cf_flypress_purge_on_cache_flush'] = 1;

			// Hummingbird
			$config['cf_hummingbird_purge_on_cache_flush'] = 1;

			// WP-Optimize
			$config['cf_wp_optimize_purge_on_cache_flush'] = 1;

			// Yasr
			$config['cf_yasr_purge_on_rating'] = 0;

			// WP Asset Clean Up
			$config['cf_wpacu_purge_on_cache_flush'] = 1;

			// Autoptimize
			$config['cf_autoptimize_purge_on_cache_flush'] = 1;

			// WP Asset Clean Up
			$config['cf_nginx_helper_purge_on_cache_flush'] = 1;

			// WP Performance
			$config['cf_wp_performance_purge_on_cache_flush'] = 1;

			// EDD
			$config['cf_bypass_edd_checkout_page']         = 1;
			$config['cf_bypass_edd_success_page']          = 0;
			$config['cf_bypass_edd_failure_page']          = 0;
			$config['cf_bypass_edd_purchase_history_page'] = 1;
			$config['cf_bypass_edd_login_redirect_page']   = 1;
			$config['cf_auto_purge_edd_payment_add']       = 1;

			// WP Engine
			$config['cf_wpengine_purge_on_flush'] = 1;

			// SpinupWP
			$config['cf_spinupwp_purge_on_flush'] = 1;

			// Kinsta
			$config['cf_kinsta_purge_on_flush'] = 1;

			// Siteground
			$config['cf_siteground_purge_on_flush'] = 1;

			// Logs
			$config['log_enabled']       = 1;
			$config['log_max_file_size'] = 2; // Megabytes
			$config['log_verbosity']     = SWCFPC_LOGS_STANDARD_VERBOSITY;

			// Other
			$config['cf_remove_purge_option_toolbar']      = 0;
			$config['cf_disable_single_metabox']           = 1;
			$config['cf_seo_redirect']                     = 0;
			$config['cf_opcache_purge_on_flush']           = 0;
			$config['cf_object_cache_purge_on_flush']      = 0;
			$config['cf_purge_roles']                      = [];
			$config['cf_prefetch_urls_viewport']           = 0;
			$config['cf_prefetch_urls_viewport_timestamp'] = time();
			$config['cf_prefetch_urls_on_hover']           = 0;
			$config['cf_remove_cache_buster']              = 0;
			$config['keep_settings_on_deactivation']       = 1;

			return apply_filters( 'swcfpc_main_config_defaults', $config );
		}


		function get_single_config( $name, $default = false ) {

			if ( ! is_array( $this->config ) || ! isset( $this->config[ $name ] ) ) {
				return $default;
			}

			if ( is_array( $this->config[ $name ] ) ) {
				return $this->config[ $name ];
			}

			return trim( $this->config[ $name ] );

		}


		function set_single_config( $name, $value ) {

			if ( ! is_array( $this->config ) ) {
				$this->config = [];
			}

			if ( is_array( $value ) ) {
				$this->config[ trim( $name ) ] = $value;
			} else {
				$this->config[ trim( $name ) ] = trim( $value );
			}

		}


		function update_config() {

			update_option( 'swcfpc_config', $this->config );

		}


		function init_config() {

			$this->config = get_option( 'swcfpc_config', false );

			if ( ! $this->config ) {
				return false;
			}

			// If the option exists, return true
			return true;

		}


		function set_config( $config ) {
			$this->config = $config;
		}


		function get_config() {
			return $this->config;
		}


		function update_plugin() {

			$current_version = get_option( 'swcfpc_version', false );

			if ( $current_version === false || version_compare( $current_version, $this->version, '!=' ) ) {

				require_once SWCFPC_PLUGIN_PATH . 'libs/installer.class.php';

				if ( $current_version === false ) {
					$installer = new SWCFPC_Installer();
					$installer->start();
				} else {

					if ( version_compare( $current_version, '4.5', '<' ) ) {

						if ( count( $this->modules ) == 0 ) {
							$this->include_libs();
						}

						$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Updating to v4.5' );

						$this->set_single_config( 'cf_auto_purge_on_upgrader_process_complete', 0 );
						$this->set_single_config( 'cf_bypass_wp_json_rest', 0 );
						$this->set_single_config( 'cf_bypass_woo_account_page', 1 );
						$this->set_single_config( 'keep_settings_on_deactivation', 1 );

						$cf_excluded_urls = $this->get_single_config( 'cf_excluded_urls', [] );

						if ( is_array( $cf_excluded_urls ) ) {

							if ( ! in_array( '/my-account*', $cf_excluded_urls ) ) {
								$cf_excluded_urls[] = '/my-account*';
							}

							if ( ! in_array( '/wc-api/*', $cf_excluded_urls ) ) {
								$cf_excluded_urls[] = '/wc-api/*';
							}

							if ( ! in_array( '/edd-api/*', $cf_excluded_urls ) ) {
								$cf_excluded_urls[] = '/edd-api/*';
							}

							if ( ! in_array( '/wp-json*', $cf_excluded_urls ) ) {
								$cf_excluded_urls[] = '/wp-json*';
							}

							$this->set_single_config( 'cf_excluded_urls', $cf_excluded_urls );

						}

						$this->update_config();

						// Called to force the creation of nginx.conf inside the plugin's directory inside the wp-content one
						$this->create_plugin_wp_content_directory();

						add_action(
							'shutdown',
							function() {

								global $sw_cloudflare_pagecache;

								$objects = $sw_cloudflare_pagecache->get_modules();

								if ( $sw_cloudflare_pagecache->get_single_config( 'cf_woker_enabled', 0 ) > 0 ) {

									$error_msg_cf = '';

									$objects['cloudflare']->disable_page_cache( $error_msg_cf );
									$objects['cloudflare']->enable_page_cache( $error_msg_cf );

								}

								$objects['logs']->add_log( 'swcfpc::update_plugin', 'Update to v4.5 complete' );

							},
							PHP_INT_MAX
						);

					}

					if ( version_compare( $current_version, '4.5.6', '<' ) ) {

						if ( count( $this->modules ) == 0 ) {
							$this->include_libs();
						}

						$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Updating to v4.5.6' );

						$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Initiating the removal of double serialization for swcfpc_config' );

						// Get the serialized version of the swcfpc_config
						$serialized_swcfpc_config = get_option( 'swcfpc_config', false );

						if ( ! $serialized_swcfpc_config ) {

							$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Serialized swcfpc_config not present' );

						} else {

							// Unserialize the data to be further stored
							if ( is_string( $serialized_swcfpc_config ) ) {
								$unserialized_swcfpc_config = unserialize( $serialized_swcfpc_config );

								// Now store the same data again to swcfpc_config,
								// But this time we won't serialize the data, instead WP will automatically do it.
								update_option( 'swcfpc_config', $unserialized_swcfpc_config );
							} else {
								$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Unfortunately swcfpc_config did not returned a string. So, we can\'t unserialize it.' );
							}                       
						}


						$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Initiating the removal of double serialization for swcfpc_fc_ttl_registry' );

						// Get the serialized version of the swcfpc_fc_ttl_registry
						$serialized_swcfpc_fc_ttl_registry = get_option( 'swcfpc_fc_ttl_registry', false );

						if ( ! $serialized_swcfpc_fc_ttl_registry ) {

							$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Serialized swcfpc_fc_ttl_registry not present' );

						} else {

							if ( is_string( $serialized_swcfpc_fc_ttl_registry ) ) {
								// Unserialize the data to be further stored
								$unserialized_swcfpc_fc_ttl_registry = unserialize( $serialized_swcfpc_fc_ttl_registry );

								// Now store the same data again to swcfpc_fc_ttl_registry,
								// But this time we won't serialize the data, instead WP will automatically do it.
								update_option( 'swcfpc_fc_ttl_registry', serialize( $unserialized_swcfpc_fc_ttl_registry ) );
							} else {
								$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Unfortunately swcfpc_fc_ttl_registry did not returned a string. So, we can\'t unserialize it.' );
							}
						}

						add_action(
							'shutdown',
							function() {

								global $sw_cloudflare_pagecache;

								$objects = $sw_cloudflare_pagecache->get_modules();

								if ( $sw_cloudflare_pagecache->get_single_config( 'cf_woker_enabled', 0 ) > 0 ) {

									$error_msg_cf = '';

									$objects['cloudflare']->disable_page_cache( $error_msg_cf );
									$objects['cloudflare']->enable_page_cache( $error_msg_cf );

								}

								$objects['logs']->add_log( 'swcfpc::update_plugin', 'Update to v4.5.6 complete' );

							},
							PHP_INT_MAX
						);
					}

					if ( version_compare( $current_version, '4.6.1', '<' ) ) {
						if ( count( $this->modules ) == 0 ) {
							$this->include_libs();
						}

						$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Updating to v4.6.1' );

						add_action(
							'shutdown',
							function() {

								global $sw_cloudflare_pagecache;

								$objects = $sw_cloudflare_pagecache->get_modules();

								$error_msg_cf = '';

								// Enable Disable the Page Cache to take effect of the changes
								$objects['cloudflare']->disable_page_cache( $error_msg_cf );
								$objects['cloudflare']->enable_page_cache( $error_msg_cf );

								$objects['logs']->add_log( 'swcfpc::update_plugin', 'Update to v4.6.1 complete' );

							},
							PHP_INT_MAX
						);
					}

					if ( version_compare( $current_version, '4.7.3', '<' ) ) {
						if ( count( $this->modules ) == 0 ) {
							$this->include_libs();
						}

						$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Updating to v4.7.3' );

						add_action(
							'shutdown',
							function() {

								global $sw_cloudflare_pagecache;

								$objects = $sw_cloudflare_pagecache->get_modules();

								$error_msg_cf = '';

								// Enable Disable the Page Cache to take effect of the changes
								$objects['cloudflare']->disable_page_cache( $error_msg_cf );
								$objects['cloudflare']->enable_page_cache( $error_msg_cf );

								$objects['logs']->add_log( 'swcfpc::update_plugin', 'Update to v4.7.3 complete' );

							},
							PHP_INT_MAX
						);
					}               
				}           
			}

			update_option( 'swcfpc_version', $this->version );

		}


		function deactivate_plugin() {
			// Keep settings when upgrading.
			if ( defined( 'SPC_PRO_PATH' ) && defined( 'SPC_FREE_PATH' ) ) {
				return;
			}

			if ( $this->get_single_config( 'keep_settings_on_deactivation', 1 ) > 0 ) {
				$this->modules['cache_controller']->reset_all( true );
			} else {
				$this->modules['cache_controller']->reset_all();
			}

			$this->delete_plugin_wp_content_directory();
		}

		/**
		 * If both free & pro are active, we attempt to deactivate the free version.
		 *
		 * @return void
		 */
		function maybe_deactivate_free() {
			if ( defined( 'SPC_PRO_PATH' ) && defined( 'SPC_FREE_PATH' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';

				deactivate_plugins( SPC_FREE_PATH );

				add_action(
					'admin_notices',
					function () {
						printf(
							'<div class="notice notice-warning"><p><strong>%s</strong><br>%s</p></div>',
							sprintf(
							/* translators: %s: Name of deactivated plugin */
								__( '%s plugin deactivated.', 'wp-cloudflare-page-cache' ),
								'Super Page Cache for Cloudflare(Free)'
							),
							'Using the Premium version of Super Page Cache for Cloudflare is not requiring using the Free version.'
						);
					} 
				);
			}
		}

		/**
		 * Get the modules.
		 *
		 * @return array
		 */
		function get_modules() {
			return $this->modules;
		}

		/**
		 * Get the modules.
		 *
		 * Legacy function to preserve backward compatibility for old `advanced-cache.php` files.
		 *
		 * @return array
		 *
		 * @deprecated Use get_modules() instead.
		 */
		function get_objects() {
			return $this->get_modules();
		}

		function add_plugin_action_links( $links ) {

			$mylinks = [
				'<a href="' . admin_url( 'options-general.php?page=wp-cloudflare-super-page-cache-index' ) . '">' . __( 'Settings', 'wp-cloudflare-page-cache' ) . '</a>',
			];

			return array_merge( $links, $mylinks );

		}


		function add_plugin_meta_links( $meta_fields, $file ) {

			if ( plugin_basename( SPC_PATH ) == $file ) {

				$meta_fields[] = '<a href="' . esc_url( SWCFPC_PLUGIN_REVIEWS_URL . '?rate=5#new-post' ) . '" target="_blank" title="' . esc_html__( 'Rate', 'wp-cloudflare-page-cache' ) . '">
                <i class="ampforwp-rate-stars">'
								 . '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#ffb900" stroke="#ffb900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>'
								 . '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#ffb900" stroke="#ffb900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>'
								 . '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#ffb900" stroke="#ffb900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>'
								 . '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#ffb900" stroke="#ffb900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>'
								 . '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#ffb900" stroke="#ffb900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>'
								 . '</i></a>';

			}

			return $meta_fields;
		}

		/**
		 * Get the Zone ID.
		 *
		 * @return string
		 */
		function get_cloudflare_api_zone_id() {

			if ( defined( 'SWCFPC_CF_API_ZONE_ID' ) ) {
				return SWCFPC_CF_API_ZONE_ID;
			}

			return $this->get_single_config( 'cf_zoneid', '' );

		}

		/**
		 * Get the Zone Name.
		 *
		 * @param string $zone_id The Zone ID.
		 * @return string
		 */
		function get_cloudflare_api_zone_domain_name( $zone_id ) {

			if ( defined( 'SWCFPC_CF_API_ZONE_NAME' ) ) {
				return SWCFPC_CF_API_ZONE_NAME;
			}

			$zone_id_list = $this->get_single_config( 'cf_zoneid_list', [] );
			foreach ( $zone_id_list as $zone_name => $zone_id_item ) {
				if ( $zone_id === $zone_id_item ) {
					return $zone_name;
				}
			}

			return '';
		}

		/**
		 * Get the API Key.
		 *
		 * @return string
		 */
		function get_cloudflare_api_key() {

			if ( defined( 'SWCFPC_CF_API_KEY' ) ) {
				return SWCFPC_CF_API_KEY;
			}

			return $this->get_single_config( 'cf_apikey', '' );

		}

		/**
		 * Get the API Email.
		 *
		 * @return string
		 */
		function get_cloudflare_api_email() {

			if ( defined( 'SWCFPC_CF_API_EMAIL' ) ) {
				return SWCFPC_CF_API_EMAIL;
			}

			return $this->get_single_config( 'cf_email', '' );

		}

		/**
		 * Get the API Token.
		 *
		 * @return string
		 */
		function get_cloudflare_api_token() {

			if ( defined( 'SWCFPC_CF_API_TOKEN' ) ) {
				return SWCFPC_CF_API_TOKEN;
			}

			return $this->get_single_config( 'cf_apitoken', '' );

		}

		/**
		 * Get Worker status.
		 *
		 * @return string
		 */
		function get_cloudflare_worker_mode() {

			if ( defined( 'SWCFPC_CF_WOKER_ENABLED' ) ) {
				return SWCFPC_CF_WOKER_ENABLED;
			}

			return $this->get_single_config( 'cf_woker_enabled', 0 );

		}

		/**
		 * Get the Worker ID.
		 *
		 * @return string
		 */
		function get_cloudflare_worker_id() {

			if ( defined( 'SWCFPC_CF_WOKER_ID' ) ) {
				return SWCFPC_CF_WOKER_ID;
			}

			return $this->get_single_config( 'cf_woker_id', 'swcfpc_worker_' . time() );

		}

		/**
		 * Get the Worker Route ID.
		 *
		 * @return string
		 */
		function get_cloudflare_worker_route_id() {

			if ( defined( 'SWCFPC_CF_WOKER_ROUTE_ID' ) ) {
				return SWCFPC_CF_WOKER_ROUTE_ID;
			}

			return $this->get_single_config( 'cf_woker_route_id', '' );

		}

		/**
		 * Get the Worker Content.
		 *
		 * @return string
		 */
		function get_cloudflare_worker_content() {

			$worker_content = '';

			if ( defined( 'SWCFPC_CF_WOKER_FULL_PATH' ) && file_exists( SWCFPC_CF_WOKER_FULL_PATH ) ) {
				$worker_content = file_get_contents( SWCFPC_CF_WOKER_FULL_PATH );
			} elseif ( file_exists( SWCFPC_PLUGIN_PATH . 'assets/js/worker_template.js' ) ) {
				$worker_content = file_get_contents( SWCFPC_PLUGIN_PATH . 'assets/js/worker_template.js' );
			}

			return $worker_content;

		}


		function get_plugin_wp_content_directory() {

			$parts = parse_url( home_url() );

			return WP_CONTENT_DIR . "/wp-cloudflare-super-page-cache/{$parts['host']}";

		}


		function get_plugin_wp_content_directory_url() {

			$parts = parse_url( home_url() );

			return content_url( "wp-cloudflare-super-page-cache/{$parts['host']}" );

		}


		function get_plugin_wp_content_directory_uri() {

			$parts = parse_url( home_url() );

			return str_replace( [ "https://{$parts['host']}", "http://{$parts['host']}" ], '', content_url( "wp-cloudflare-super-page-cache/{$parts['host']}" ) );

		}


		function create_plugin_wp_content_directory() {

			$parts = parse_url( home_url() );
			$path  = WP_CONTENT_DIR . '/wp-cloudflare-super-page-cache/';

			if ( ! file_exists( $path ) && wp_mkdir_p( $path, 0755 ) ) {
				file_put_contents( "{$path}index.php", '<?php // Silence is golden' );
			}

			$path .= $parts['host'];

			if ( ! file_exists( $path ) && wp_mkdir_p( $path, 0755 ) ) {
				file_put_contents( "{$path}/index.php", '<?php // Silence is golden' );
			}

			$nginx_conf = "{$path}/nginx.conf";

			if ( ! file_exists( $nginx_conf ) ) {
				file_put_contents( $nginx_conf, '' );
			}

		}


		function delete_plugin_wp_content_directory() {

			$parts = parse_url( home_url() );
			$path  = WP_CONTENT_DIR . '/wp-cloudflare-super-page-cache/';
			$path .= $parts['host'];

			if ( file_exists( $path ) ) {
				$this->delete_directory_recursive( $path );
			}

		}


		function delete_directory_recursive( $dir ) {

			if ( ! class_exists( 'RecursiveDirectoryIterator' ) || ! class_exists( 'RecursiveIteratorIterator' ) ) {
				return false;
			}

			$it    = new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS );
			$files = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );

			foreach ( $files as $file ) {

				if ( $file->isDir() ) {
					rmdir( $file->getRealPath() );
				} else {
					unlink( $file->getRealPath() );
				}           
			}

			rmdir( $dir );

			return true;

		}


		function generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ) {

			$chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$password = '';

			if ( $special_chars ) {
				$chars .= '!@#$%^&*()';
			}
			if ( $extra_special_chars ) {
				$chars .= '-_ []{}<>~`+=,.;:/?|';
			}

			for ( $i = 0; $i < $length; $i++ ) {
				$password .= substr( $chars, rand( 0, strlen( $chars ) - 1 ), 1 );
			}

			return $password;

		}


		function is_login_page() {

			return in_array( $GLOBALS['pagenow'], [ 'wp-login.php', 'wp-register.php' ] );

		}


		function get_second_level_domain() {

			$site_hostname = parse_url( home_url(), PHP_URL_HOST );

			if ( is_null( $site_hostname ) ) {
				return '';
			}

			// get the domain name from the hostname
			$site_domain = preg_replace( '/^www\./', '', $site_hostname );

			return $site_domain;

		}


		function enable_wp_cli_support() {

			if ( defined( 'WP_CLI' ) && WP_CLI && ! class_exists( 'SWCFPC_WP_CLI' ) && class_exists( 'WP_CLI_Command' ) ) {

				require_once SWCFPC_PLUGIN_PATH . 'libs/wpcli.class.php';

				$wpcli = new SWCFPC_WP_CLI( $this );

				WP_CLI::add_command( 'cfcache', $wpcli );


			}

		}


		function can_current_user_purge_cache() {

			if ( ! is_user_logged_in() ) {
				return false;
			}

			if ( current_user_can( 'manage_options' ) ) {
				return true;
			}

			$allowed_roles = $this->get_single_config( 'cf_purge_roles', [] );

			if ( count( $allowed_roles ) > 0 ) {

				$user = wp_get_current_user();

				foreach ( $allowed_roles as $role_name ) {

					if ( in_array( $role_name, (array) $user->roles ) ) {
						return true;
					}               
				}           
			}

			return false;

		}


		function get_wordpress_roles() {

			global $wp_roles;
			$wordpress_roles = [];

			foreach ( $wp_roles->roles as $role => $role_data ) {
				$wordpress_roles[] = $role;
			}

			return $wordpress_roles;

		}


		function does_current_url_have_trailing_slash() {

			if ( ! preg_match( '/\/$/', $_SERVER['REQUEST_URI'] ) ) {
				return false;
			}

			return true;

		}


		function is_api_request() {

			// WordPress standard API
			if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || strcasecmp( substr( $_SERVER['REQUEST_URI'], 0, 8 ), '/wp-json' ) == 0 ) {
				return true;
			}

			// WooCommerce standard API
			if ( strcasecmp( substr( $_SERVER['REQUEST_URI'], 0, 8 ), '/wc-api/' ) == 0 ) {
				return true;
			}

			// WooCommerce standard API
			if ( strcasecmp( substr( $_SERVER['REQUEST_URI'], 0, 9 ), '/edd-api/' ) == 0 ) {
				return true;
			}

			return false;

		}


		function wildcard_match( $pattern, $subject ) {

			$pattern = '#^' . preg_quote( $pattern ) . '$#i'; // Case insensitive
			$pattern = str_replace( '\*', '.*', $pattern );
			// $pattern = str_replace('\.', '.', $pattern);

			if ( ! preg_match( $pattern, $subject, $regs ) ) {
				return false;
			}

			return true;

		}

		// Pass parse_url() array and get the URL back as string
		function get_unparsed_url( $parsed_url ) {
			// PHP_URL_SCHEME
			$scheme   = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '';
			$host     = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
			$port     = isset( $parsed_url['port'] ) ? ':' . $parsed_url['port'] : '';
			$user     = isset( $parsed_url['user'] ) ? $parsed_url['user'] : '';
			$pass     = isset( $parsed_url['pass'] ) ? ':' . $parsed_url['pass'] : '';
			$pass     = ( $user || $pass ) ? "$pass@" : '';
			$path     = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
			$query    = isset( $parsed_url['query'] ) ? '?' . $parsed_url['query'] : '';
			$fragment = isset( $parsed_url['fragment'] ) ? '#' . $parsed_url['fragment'] : '';
			return "{$scheme}{$user}{$pass}{$host}{$port}{$path}{$query}{$fragment}";
		}

		// Return the ignored query params array
		function get_ignored_query_params() {
			return $this->ignored_query_params;
		}

		function get_current_lang_code() {

			$current_language_code = false;

			if ( has_filter( 'wpml_current_language' ) ) {
				$current_language_code = apply_filters( 'wpml_current_language', null );
			}

			return $current_language_code;

		}


		function get_permalink( $post_id ) {

			$url = get_the_permalink( $post_id );

			if ( has_filter( 'wpml_permalink' ) ) {
				$url = apply_filters( 'wpml_permalink', $url, $this->get_current_lang_code() );
			}

			return $url;

		}


		function get_home_url( $blog_id = null, $path = '', $scheme = null ) {

			global $pagenow;

			if ( empty( $blog_id ) || ! is_multisite() ) {
				$url = get_option( 'home' );
			} else {
				switch_to_blog( $blog_id );
				$url = get_option( 'home' );
						restore_current_blog();
			}

			if ( ! in_array( $scheme, [ 'http', 'https', 'relative' ], true ) ) {

				if ( is_ssl() && ! is_admin() && 'wp-login.php' !== $pagenow ) {
					$scheme = 'https';
				} else {
					$scheme = parse_url( $url, PHP_URL_SCHEME );
				}           
			}

			$url = set_url_scheme( $url, $scheme );

			if ( $path && is_string( $path ) ) {
				$url .= '/' . ltrim( $path, '/' );
			}

			return $url;

		}


		function home_url( $path = '', $scheme = null ) {
			return $this->get_home_url( null, $path, $scheme );
		}

		function ajax_test_page_cache() {
			check_ajax_referer( 'ajax-nonce-string', 'security' );

			$return_array = [ 'status' => 'ok' ];

			$test_file_url = SWCFPC_PLUGIN_URL . 'assets/testcache.html';
			$tester        = new SWCFPC_Test_Cache( $test_file_url );

			$disk_cache_error = false;
			$cloudflare_error = false;
			$status_messages  = [];
			$cache_issues     = [];

			$is_disk_cache_enabled = $this->get_single_config( 'cf_fallback_cache' );
			$is_cloudflare_enabled = (
				! empty( $this->get_single_config( 'cf_page_rule_id' ) ) ||
				! empty( $this->get_single_config( 'cf_cache_settings_ruleset_rule_id' ) ) ||
				! empty( $this->get_single_config( 'cf_woker_route_id' ) )
			);

			if ( ! $is_cloudflare_enabled ) {
				$status_messages[] = [
					'status'  => 'warning',
					'message' => __( 'Cloudflare (Cache Rule or Worker) is not enabled!', 'wp-cloudflare-page-cache' ),
				];
			}

			// Check Cloudflare if it is possible.
			if ( $is_cloudflare_enabled ) {
				if ( ! $tester->check_cloudflare_cache() ) {
					$cloudflare_error  = true;
					$cache_issues      = $tester->get_errors();
					$status_messages[] = [
						'status'  => 'error',
						'message' => __( 'Cloudflare integration has an issue.', 'wp-cloudflare-page-cache' ),
					];
				} else {
					$status_messages[] = [
						'status'  => 'success',
						'message' => __( 'Cloudflare Page Caching is working properly.', 'wp-cloudflare-page-cache' ),
					];
				}
			}

			// Check Fallback cache.
			if ( ! $is_disk_cache_enabled ) {
				$status_messages[] = [
					'status'  => 'warning',
					'message' => __( 'Disk Page Cache is not enabled!', 'wp-cloudflare-page-cache' ),
				];
			}

			if ( $is_disk_cache_enabled ) {

				/**
				 * @var SWCFPC_Fallback_Cache $fallback_cache
				 */
				$fallback_cache = $this->modules['fallback_cache'];

				$fallback_cache->fallback_cache_add_current_url_to_cache( $test_file_url, true );
				$disk_cache_error = ! $fallback_cache->fallback_cache_check_cached_page( $test_file_url );

				if ( $disk_cache_error ) {
					$cache_issues[]    = __( 'Could not cache the page on the disk. [Page Disk Cache]', 'wp-cloudflare-page-cache' );
					$status_messages[] = [
						'status'  => 'error',
						'message' => __( 'Disk Page Caching has an issue.', 'wp-cloudflare-page-cache' ),
					];
				} else {
					$status_messages[] = [
						'status'  => 'success',
						'message' => __( 'Disk Page Caching is functional.', 'wp-cloudflare-page-cache' ),
					];
				}
			}

			$html_response = '<div class="swcfpc-test-response">';

			if ( ! empty( $status_messages ) ) {
				$html_response .= '<div class="test-container">';
				$html_response .= '<h3>' . __( 'Status', 'wp-cloudflare-page-cache' ) . '</h3>';
				$html_response .= '<ul>';

				foreach ( $status_messages as $status ) {
					$html_response .= '<li class="is-' . $status['status'] . '">' . $status['message'] . '</li>';
				}

				$html_response .= '</ul>';
				$html_response .= '</div>';
			}

			if ( ! empty( $cache_issues ) ) {
				$html_response .= '<div class="test-container">';
				$html_response .= '<h3>' . __( 'Issues', 'wp-cloudflare-page-cache' ) . '</h3>';
				$html_response .= '<ul>';
				foreach ( $cache_issues as $issue ) {
					$html_response .= '<li class="is-error">' . $issue . '</li>';
				}
				$html_response .= '</ul>';

				if ( $cloudflare_error ) {
					$html_response .= '<p>' . __( 'Please check if the page caching is working by yourself by surfing the website in incognito mode \'cause sometimes Cloudflare bypass the cache for cURL requests. Reload a page two or three times. If you see the response header <strong>cf-cache-status: HIT</strong>, the page caching is working well.', 'wp-cloudflare-page-cache' ) . '</p>';
				}

				if ( $is_cloudflare_enabled ) {
					$html_response .= '<p><a href="' . esc_url( $test_file_url ) . '" target="_blank">' . __( 'Cloudflare Test Page', 'wp-cloudflare-page-cache' ) . '</a></p>';
				}
				$html_response .= '</div>';
			}

			$html_response .= '</div>';

			$return_array['html'] = $html_response;

			if (
				! empty( $cache_issues ) ||
				( ! $is_cloudflare_enabled && ! $is_disk_cache_enabled )
			) {
				$return_array['status'] = 'error';
			}

			die( json_encode( $return_array ) );
		}

		function maybe_load_pro_modules() {
			if (
				! is_file( SWCFPC_PLUGIN_PATH . 'pro/Loader.php' ) ||
				! defined( 'SPC_PRO_PATH' ) ||
				! class_exists( 'SPC_Pro\\Loader' )
			) {
				return;
			}

			$loader = new SPC_Pro\Loader();
			$loader->init();
		}

		function get_plugin_version() {
			return $this->version;
		}
	}

	// Activate this plugin as last plugin
	add_action(
		'plugins_loaded',
		function () {

			if ( ! isset( $GLOBALS['sw_cloudflare_pagecache'] ) || empty( $GLOBALS['sw_cloudflare_pagecache'] ) ) {
				$GLOBALS['sw_cloudflare_pagecache'] = new SW_CLOUDFLARE_PAGECACHE();
			}

		},
		PHP_INT_MAX
	);

	add_action(
		'admin_init',
		function() {

			/**
			 * Redirect to the settings page after activation.
			 */
			if ( get_option( 'swcfpc_dashboard_redirect', false ) ) {
				delete_option( 'swcfpc_dashboard_redirect' );
				wp_safe_redirect( admin_url( 'options-general.php?page=wp-cloudflare-super-page-cache-index' ) );
				exit;
			}
		} 
	);

	register_activation_hook(
		SPC_PATH,
		function() {

			/**
			 * Activate redirection to the settings page.
			 */
			update_option( 'swcfpc_dashboard_redirect', true );
		} 
	);
}
