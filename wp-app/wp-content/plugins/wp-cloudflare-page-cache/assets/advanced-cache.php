<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
define( 'SWCFPC_ADVANCED_CACHE', true );

if ( ! swcfpc_is_this_page_cachable() ) {
	return;
}

if ( isset( $_SERVER['REQUEST_METHOD'] ) && strcasecmp( $_SERVER['REQUEST_METHOD'], 'GET' ) != 0 ) {
	return;
}

$swcfpc_fallback_cache_config_path = WP_CONTENT_DIR . "/wp-cloudflare-super-page-cache/{$_SERVER['HTTP_HOST']}/";
$swcfpc_fallback_cache_path        = WP_CONTENT_DIR . "/wp-cloudflare-super-page-cache/{$_SERVER['HTTP_HOST']}/fallback_cache/";
$swcfpc_fallback_cache_key         = swcfpc_fallback_cache_get_current_page_cache_key();

if ( ! file_exists( "{$swcfpc_fallback_cache_config_path}main_config.php" ) ) {
	return;
}

require "{$swcfpc_fallback_cache_config_path}main_config.php";

$swcfpc_config = json_decode( stripslashes( $swcfpc_config ), true );

if ( ! is_array( $swcfpc_config ) ) {
	return;
}

if ( swcfpc_fallback_cache_is_url_to_exclude() ) {
	return;
}

if ( swcfpc_fallback_cache_is_cookie_to_exclude() ) {
	return;
}

if ( swcfpc_fallback_cache_is_cookie_to_exclude_cf_worker() ) {
	return;
}

if ( file_exists( $swcfpc_fallback_cache_path . $swcfpc_fallback_cache_key ) && ! swcfpc_fallback_cache_is_expired_page( $swcfpc_fallback_cache_key ) ) {

	$cache_controller = "s-maxage={$swcfpc_config['cf_maxage']}, max-age={$swcfpc_config['cf_browser_maxage']}";
	$stored_headers   = swcfpc_fallback_cache_get_stored_headers( $swcfpc_fallback_cache_path, $swcfpc_fallback_cache_key );

	if ( (int) $swcfpc_config['cf_maxage'] > 0 ) {
		header_remove( 'Set-Cookie' );
	}

	header_remove( 'Pragma' );
	header_remove( 'Expires' );
	header_remove( 'Cache-Control' );
	header( "Cache-Control: {$cache_controller}" );
	header( 'X-WP-CF-Super-Cache: cache' );
	header( 'X-WP-CF-Super-Cache-Active: 1' );
	header( 'X-WP-CF-Fallback-Cache: 1' );
	header( "X-WP-CF-Super-Cache-Cache-Control: {$cache_controller}" );

	if ( isset( $swcfpc_config['cf_woker_enabled'] ) && $swcfpc_config['cf_woker_enabled'] > 0 && isset( $swcfpc_config['cf_worker_bypass_cookies'] ) && is_array( $swcfpc_config['cf_worker_bypass_cookies'] ) && count( $swcfpc_config['cf_worker_bypass_cookies'] ) > 0 ) {
		header( 'X-WP-CF-Super-Cache-Cookies-Bypass: ' . trim( implode( '|', $swcfpc_config['cf_worker_bypass_cookies'] ) ) );
	}

	if ( $stored_headers ) {

		foreach ( $stored_headers as $single_header ) {
			header( $single_header, false );
		}   
	}

	die( file_get_contents( $swcfpc_fallback_cache_path . $swcfpc_fallback_cache_key ) . '<!-- ADVANCED CACHE -->' );

}

ob_start( 'swcfpc_fallback_cache_end' );


function swcfpc_is_this_page_cachable() { 
	if (
		swcfpc_is_api_request() ||
		( isset( $GLOBALS['pagenow'] ) && in_array( $GLOBALS['pagenow'], [ 'wp-login.php', 'wp-register.php' ] ) ) ||
		( isset( $_SERVER['REQUEST_URI'] ) && substr( $_SERVER['REQUEST_URI'], 0, 16 ) == '/wp-register.php' ) ||
		( isset( $_SERVER['REQUEST_URI'] ) && substr( $_SERVER['REQUEST_URI'], 0, 13 ) == '/wp-login.php' ) ||
		( isset( $_SERVER['REQUEST_METHOD'] ) && strcasecmp( $_SERVER['REQUEST_METHOD'], 'GET' ) != 0 ) ||
		( ! defined( 'SWCFPC_CACHE_BUSTER' ) && isset( $_GET['swcfpc'] ) ) ||
		( defined( 'SWCFPC_CACHE_BUSTER' ) && isset( $_GET[ SWCFPC_CACHE_BUSTER ] ) ) ||
		is_admin() ||
		( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) ||
		( defined( 'DOING_AJAX' ) && DOING_AJAX ) ||
		( defined( 'WP_CLI' ) && WP_CLI ) ||
		( defined( 'DOING_CRON' ) && DOING_CRON )
	) {
		return false;
	}

	return true;

}


function swcfpc_is_api_request() { 
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


function swcfpc_fallback_cache_end( $html ) { 
	/**
	 * The main plugin class.
	 *
	 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
	 */
	global $sw_cloudflare_pagecache;

	if ( strlen( trim( $html ) ) == 0 ) {
		return $html;
	}

	if ( ! is_object( $sw_cloudflare_pagecache ) ) {
		return $html;
	}

	$swcfpc_objects = $sw_cloudflare_pagecache->get_modules();

	if ( $sw_cloudflare_pagecache->get_single_config( 'cf_fallback_cache', 0 ) == 0 ) {
		return $html;
	}

	if ( $swcfpc_objects['cache_controller']->is_cache_enabled() && ! $swcfpc_objects['cache_controller']->is_url_to_bypass() && ! $swcfpc_objects['cache_controller']->can_i_bypass_cache() && isset( $_SERVER['REQUEST_METHOD'] ) && strcasecmp( $_SERVER['REQUEST_METHOD'], 'GET' ) == 0 ) {

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && strcasecmp( $_SERVER['HTTP_USER_AGENT'], 'ua-swcfpc-fc' ) == 0 ) {
			return $html;
		}

		$cache_path = $swcfpc_objects['fallback_cache']->fallback_cache_init_directory();
		$cache_key  = swcfpc_fallback_cache_get_current_page_cache_key();

		if ( ! file_exists( $cache_path . $cache_key ) || $swcfpc_objects['fallback_cache']->fallback_cache_is_expired_page( $cache_key ) ) {

			if ( $sw_cloudflare_pagecache->get_single_config( 'cf_fallback_cache_ttl', 0 ) == 0 ) {
				$ttl = 0;
			} else {
				$ttl = time() + $sw_cloudflare_pagecache->get_single_config( 'cf_fallback_cache_ttl', 0 );
			}

			$html .= "\n<!-- Page retrieved from Super Page Cache fallback cache - page generated @ " . date( 'Y-m-d H:i:s' ) . ' - fallback cache expiration @ ' . ( $ttl > 0 ? date( 'Y-m-d H:i:s', $ttl ) : 'never expires' ) . " - cache key {$cache_key} -->";

			// Provide a filter to modify the HTML before it is cached
			$html = apply_filters( 'swcfpc_normal_fallback_cache_html', $html );

			file_put_contents( $cache_path . $cache_key, $html );

			// Update TTL
			$swcfpc_objects['fallback_cache']->fallback_cache_set_single_ttl( $cache_key, $ttl );
			$swcfpc_objects['fallback_cache']->fallback_cache_update_ttl_registry();

			// Store headers
			if ( $sw_cloudflare_pagecache->get_single_config( 'cf_fallback_cache_save_headers', 0 ) > 0 ) {
				swcfpc_fallback_cache_save_headers( $cache_path, $cache_key );
			}       
		}   
	}

	return $html;

}


function swcfpc_fallback_cache_get_current_page_cache_key( $url = null ) { 
	$replacements = [ '://', '/', '?', '#', '&', '.', ',', '@', '-', '\'', '"', '%', ' ', '\\', '=' ];

	if ( ! is_null( $url ) ) {

		$parts = parse_url( strtolower( $url ) );

		if ( ! $parts ) {
			return false;
		}

		$current_uri = isset( $parts['path'] ) ? $parts['path'] : '/';

		if ( isset( $parts['query'] ) ) {
			$current_uri .= "?{$parts['query']}";
		}

		if ( $current_uri == '/' ) {
			$current_uri = $parts['host'];
		}   
	} else {

		$current_uri = $_SERVER['REQUEST_URI'];

		if ( $current_uri == '/' ) {
			$current_uri = $_SERVER['HTTP_HOST'];
		}
	}

	if ( substr( $current_uri, 0, 1 ) == '/' ) {
		$current_uri = substr( $current_uri, 1 );
	}

	if ( substr( $current_uri, -1, 1 ) == '/' ) {
		$current_uri = substr( $current_uri, 0, -1 );
	}

	if ( has_filter( 'swcfpc_fc_modify_current_url' ) ) {

		// Modify the current URL by yourself to remove the query string or any other unwanted characters
		$current_uri = apply_filters( 'swcfpc_fc_modify_current_url', $current_uri );

		$cache_key = str_replace( $replacements, '_', $current_uri );

	} else {

		$cache_key = str_replace( $replacements, '_', swcfpc_fallback_cache_remove_url_parameters( $current_uri ) );
	}


	// Fix error fnmatch(): Filename exceeds the maximum allowed length
	$cache_key = sha1( $cache_key );

	/*
	if( strlen($cache_key) > 250 ) {
		$cache_key = substr($cache_key, 0, -32);
		$cache_key .= md5( $current_uri );
	}
	*/

	$cache_key .= '.html';

	return $cache_key;

}

function swcfpc_get_unparsed_url( $parsed_url ) {
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

function swcfpc_fallback_cache_remove_url_parameters( $url ) { 
	$url_parsed       = parse_url( $url );
	$url_query_params = [];

	if ( array_key_exists( 'query', $url_parsed ) ) {

		if ( $url_parsed['query'] === '' ) {

			// this means the URL ends with just ? i.e. /example-page/? - so just remove the last character ? from the URL
			$url = substr( trim( $url ), 0, -1 );

		} else {

			// Set the ignored query param array
			$ignored_query_params = [
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

			// First parse the query params to an array to manage it better
			parse_str( $url_parsed['query'], $url_query_params );

			// Loop though $ignored_query_params
			foreach ( $ignored_query_params as $ignored_query_param ) {

				// Check if that query param is present in $url_query_params
				if ( array_key_exists( $ignored_query_param, $url_query_params ) ) {

					// The ignored query param is present in the $url_query_params. So, unset it from there
					unset( $url_query_params[ $ignored_query_param ] );
				}
			}

			// Now lets check if we have any query params left in $url_query_params
			if ( count( $url_query_params ) > 0 ) {

				$new_url_query_params = http_build_query( $url_query_params );
				$url_parsed['query']  = $new_url_query_params;

			} else {
				// Remove the query section from parsed URL
				unset( $url_parsed['query'] );
			}

			// Get the new current URL without the marketing query params
			$url = swcfpc_get_unparsed_url( $url_parsed );
		}
	}

	return $url;

}


function swcfpc_fallback_cache_is_expired_page( $cache_key ) { 
	$config_path = WP_CONTENT_DIR . "/wp-cloudflare-super-page-cache/{$_SERVER['HTTP_HOST']}/";

	if ( ! file_exists( "{$config_path}ttl_registry.json" ) ) {
		return false;
	}

	$swcfpc_ttl_registry = json_decode( file_get_contents( "{$config_path}ttl_registry.json" ), true );
	$current_ttl         = 0;

	if ( ! is_array( $swcfpc_ttl_registry ) || ! isset( $swcfpc_ttl_registry[ $cache_key ] ) ) {
		$current_ttl = 0;
	} elseif ( is_array( $swcfpc_ttl_registry[ $cache_key ] ) ) {
		$current_ttl = $swcfpc_ttl_registry[ $cache_key ];
	} else {
		$current_ttl = (int) $swcfpc_ttl_registry[ $cache_key ];
	}

	if ( $current_ttl > 0 && time() > $current_ttl ) {
		return true;
	}

	return false;


}


function swcfpc_fallback_cache_is_cookie_to_exclude() { 
	global $swcfpc_config;

	if ( count( $_COOKIE ) == 0 ) {
		return false;
	}

	if ( is_array( $swcfpc_config ) && ! isset( $swcfpc_config['cf_fallback_cache_excluded_cookies'] ) ) {
		return false;
	}

	$excluded_cookies = $swcfpc_config['cf_fallback_cache_excluded_cookies'];

	if ( count( $excluded_cookies ) == 0 ) {
		return false;
	}

	$cookies = array_keys( $_COOKIE );

	foreach ( $excluded_cookies as $single_cookie ) {

		if ( count( preg_grep( "#{$single_cookie}#", $cookies ) ) > 0 ) {
			return true;
		}   
	}

	return false;

}


function swcfpc_fallback_cache_is_cookie_to_exclude_cf_worker() { 
	global $swcfpc_config;

	if ( count( $_COOKIE ) == 0 ) {
		return false;
	}

	if ( ! is_array( $swcfpc_config ) ) {
		return false;
	}

	if ( ! isset( $swcfpc_config['cf_worker_bypass_cookies'] ) || ! isset( $swcfpc_config['cf_woker_enabled'] ) ) {
		return false;
	}

	if ( (int) $swcfpc_config['cf_woker_enabled'] == 0 ) {
		return false;
	}

	$excluded_cookies = $swcfpc_config['cf_worker_bypass_cookies'];

	if ( count( $excluded_cookies ) == 0 ) {
		return false;
	}

	$cookies = array_keys( $_COOKIE );

	foreach ( $excluded_cookies as $single_cookie ) {

		if ( count( preg_grep( "#{$single_cookie}#", $cookies ) ) > 0 ) {
			return true;
		}   
	}

	return false;

}


function swcfpc_fallback_cache_is_url_to_exclude( $url = false ) { 
	global $swcfpc_config;

	if ( is_array( $swcfpc_config ) && isset( $swcfpc_config['cf_fallback_cache_prevent_cache_urls_without_trailing_slash'] ) && $swcfpc_config['cf_fallback_cache_prevent_cache_urls_without_trailing_slash'] > 0 && ! preg_match( '/\/$/', $_SERVER['REQUEST_URI'] ) ) {
		return true;
	}

	if ( is_array( $swcfpc_config ) && ! isset( $swcfpc_config['cf_fallback_cache_excluded_urls'] ) ) {
		return false;
	}

	$excluded_urls = $swcfpc_config['cf_fallback_cache_excluded_urls'];

	if ( is_array( $excluded_urls ) && count( $excluded_urls ) > 0 ) {

		if ( $url === false ) {

			$current_url = $_SERVER['REQUEST_URI'];

			if ( isset( $_SERVER['QUERY_STRING'] ) && strlen( $_SERVER['QUERY_STRING'] ) > 0 ) {
				$current_url .= "?{$_SERVER['QUERY_STRING']}";
			}       
		} else {
			$current_url = $url;
		}

		foreach ( $excluded_urls as $url_to_exclude ) {

			if ( swcfpc_wildcard_match( $url_to_exclude, $current_url ) ) {
				return true;
			}

			/*
			if( fnmatch($url_to_exclude, $current_url, FNM_CASEFOLD) ) {
				return true;
			}
			*/

		}   
	}

	return false;

}


function swcfpc_fallback_cache_save_headers( $fallback_cache_path, $cache_key ) { 
	$headers_file = "{$fallback_cache_path}{$cache_key}.headers.json";

	$headers_list  = headers_list();
	$headers_count = count( $headers_list );

	for ( $i = 0; $i < $headers_count; ++$i ) {

		list($header_name, $header_value) = explode( ':', $headers_list[ $i ] );

		if (
			strcasecmp( $header_name, 'cache-control' ) == 0 ||
			strcasecmp( $header_name, 'set-cookie' ) == 0 ||
			strcasecmp( substr( $header_name, 0, 19 ), 'X-WP-CF-Super-Cache' ) == 0
		) {
			unset( $headers_list[ $i ] );
			continue;
		}   
	}

	if ( count( $headers_list ) == 0 ) {

		if ( file_exists( $headers_file ) ) {
			@unlink( $headers_file );
		}

		return false;

	}

	file_put_contents( $headers_file, json_encode( $headers_list ) );

	return true;

}


function swcfpc_fallback_cache_get_stored_headers( $fallback_cache_path, $cache_key ) { 
	$headers_file = "{$fallback_cache_path}{$cache_key}.headers.json";

	if ( file_exists( $headers_file ) ) {

		$swcfpc_headers = json_decode( file_get_contents( $headers_file ), true );

		if ( is_array( $swcfpc_headers ) && count( $swcfpc_headers ) > 0 ) {
			return $swcfpc_headers;
		}   
	}

	return false;

}


function swcfpc_wildcard_match( $pattern, $subject ) {
	// Case insensitive
	$pattern = '#^' . preg_quote( $pattern ) . '$#i';
	$pattern = str_replace( '\*', '.*', $pattern );
	// $pattern = str_replace('\.', '.', $pattern);

	if ( ! preg_match( $pattern, $subject, $regs ) ) {
		return false;
	}

	return true;

}
