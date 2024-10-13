<?php
// phpcs:ignoreFile

/**
 * Advanced Ads.
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright 2013-2018 Thomas Maier, Advanced Ads GmbH
 */

use AdvancedAds\Entities;
use AdvancedAds\Utilities\WordPress;

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * @package Advanced_Ads
 * @author  Thomas Maier <support@wpadvancedads.com>
 */
class Advanced_Ads {

	/**
	 * Post type slug
	 *
	 * @deprecated 1.47.0 Use \AdvancedAds\Entities::POST_TYPE_AD
	 *
	 * @var     string
	 */
	const POST_TYPE_SLUG = 'advanced_ads';

	/**
	 * Ad group slug
	 *
	 * @deprecated 1.47.0 Use \AdvancedAds\Entities::TAXONOMY_AD_GROUP
	 *
	 * @var     string
	 */
	const AD_GROUP_TAXONOMY = 'advanced_ads_groups';

	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	private static $instance = null;

	/**
	 * Array with ads currently delivered in the frontend
	 *
	 * @var array Ads already loaded in the frontend
	 */
	public $current_ads = [];

	/**
	 * Ad types
	 *
	 * @var array Ad types
	 */
	public $ad_types = [];

	/**
	 * Plugin options
	 *
	 * @var     array (if loaded)
	 */
	protected $options = false;

	/**
	 * Adsense options
	 *
	 * @var array (if loaded)
	 */
	protected $adsense_options = null;

	/**
	 * Interal plugin options – set by the plugin
	 *
	 * @var     array (if loaded)
	 */
	protected $internal_options = false;

	/**
	 * Whether the loop started in an inner `the_content`.
	 *
	 * @var bool
	 */
	protected $was_in_the_loop = false;

	/**
	 * Signifies Whether the loop has started and the caller is in the loop.
	 *
	 * We need it because Some the "Divi" theme calls the `loop_start/loop_end` hooks
	 * instead of calling `the_post()` to signify that the caller is in the loop.
	 *
	 * @var bool
	 */
	protected $in_the_loop = false;

	/**
	 * List of bots and crawlers to exclude from ad impressions
	 *
	 * @var array list of bots
	 */
	protected $bots = [ 'bot', 'spider', 'crawler', 'scraper', 'parser', '008', 'Accoona-AI-Agent', 'ADmantX', 'alexa', 'appie', 'Apple-PubSub', 'Arachmo', 'Ask Jeeves', 'avira\.com', 'B-l-i-t-z-B-O-T', 'boitho\.com-dc', 'BUbiNG', 'Cerberian Drtrs', 'Charlotte', 'cosmos', 'Covario IDS', 'curl', 'Datanyze', 'DataparkSearch', 'Dataprovider\.com', 'DDG-Android', 'Ecosia', 'expo9', 'facebookexternalhit', 'Feedfetcher-Google', 'FindLinks', 'Firefly', 'froogle', 'Genieo', 'heritrix', 'Holmes', 'htdig', 'https://developers\.google\.com', 'ia_archiver', 'ichiro', 'igdeSpyder', 'InfoSeek', 'inktomi', 'Kraken', 'L\.webis', 'Larbin', 'Linguee', 'LinkWalker', 'looksmart', 'lwp-trivial', 'mabontland', 'Mnogosearch', 'mogimogi', 'Morning Paper', 'MVAClient', 'NationalDirectory', 'NetResearchServer', 'NewsGator', 'NG-Search', 'Nusearch', 'NutchCVS', 'Nymesis', 'oegp', 'Orbiter', 'Peew', 'Pompos', 'PostPost', 'proximic', 'PycURL', 'Qseero', 'rabaz', 'Radian6', 'Reeder', 'savetheworldheritage', 'SBIder', 'Scooter', 'ScoutJet', 'Scrubby', 'SearchSight', 'semanticdiscovery', 'Sensis', 'ShopWiki', 'silk', 'Snappy', 'Spade', 'Sqworm', 'StackRambler', 'TechnoratiSnoop', 'TECNOSEEK', 'Teoma', 'Thumbnail\.CZ', 'TinEye', 'truwoGPS', 'updated', 'Vagabondo', 'voltron', 'Vortex', 'voyager', 'VYU2', 'WebBug', 'webcollage', 'WebIndex', 'Websquash\.com', 'WeSEE:Ads', 'wf84', 'Wget', 'WomlpeFactory', 'WordPress', 'yacy', 'Yahoo! Slurp', 'Yahoo! Slurp China', 'YahooSeeker', 'YahooSeeker-Testing', 'YandexBot', 'YandexMedia', 'YandexBlogs', 'YandexNews', 'YandexCalendar', 'YandexImages', 'Yeti', 'yoogliFetchAgent', 'Zao', 'ZyBorg', 'okhttp', 'ips-agent', 'ltx71', 'Optimizer', 'Daum', 'Qwantify' ];

	/**
	 * Loaded instance of Advanced_Ads_Model
	 *
	 * @var Advanced_Ads_Model
	 */
	protected $model;

	/**
	 * Loaded instance of Advanced_Ads_Plugin
	 *
	 * @var Advanced_Ads_Plugin
	 */
	protected $plugin;

	/**
	 * Loaded instance of Advanced_Ads_Select
	 *
	 * @var Advanced_Ads_Select
	 */
	protected $ad_selector;

	/**
	 * Is the query the main query?, when WP_Query is used
	 *
	 * @var bool
	 */
	private $is_main_query;

	/**
	 * Save number of ads
	 *
	 * @var array
	 */
	private $number_of_ads = [];

	/**
	 * Reason why are disabled.
	 *
	 * @var string
	 */
	public $disabled_reason = null;

	/**
	 * Identifier that supplement the disabled reason.
	 *
	 * @var int|string
	 */
	public $disabled_id = null;

	/**
	 * Initialize frontend features
	 */
	private function __construct() {
		$this->plugin = Advanced_Ads_Plugin::get_instance();
		$this->plugin->set_model( $this->get_model() );
		$this->ad_selector = Advanced_Ads_Select::get_instance();

		// initialize plugin specific functions.
		add_action( 'init', [ $this, 'wp_init' ] );

		// only when not doing ajax.
		if ( wp_doing_ajax() ) {
			Advanced_Ads_Ajax::get_instance();
		}
		add_action( 'plugins_loaded', [ $this, 'wp_plugins_loaded' ] );

		// allow add-ons to interact.
		add_action( 'init', [ $this, 'advanced_ads_loaded' ], 9 );

		add_filter( 'the_content', [ $this, 'set_was_in_the_loop' ], ~PHP_INT_MAX );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    Advanced_Ads    A single instance of this class.
	 */
	public static function get_instance() {

		// if the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Return the Advanced_Ads_Model responsible for loading ads, groups and placements into the frontend
	 *
	 * @return Advanced_Ads_Model
	 */
	public function get_model() {

		global $wpdb;

		if ( ! isset( $this->model ) ) {
			$this->model = new Advanced_Ads_Model( $wpdb );
		}

		return $this->model;
	}

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	public function wp_plugins_loaded() {
		// register hook for global constants.
		// Wait until BuddyPress assigns `WP_Query->is_404` back to `false`.
		add_action( 'template_redirect', [ $this, 'set_disabled_constant' ], 11 );
		add_action( 'rest_api_init', [ $this, 'set_disabled_constant' ] );

		// setup default ad types.
		add_filter( 'advanced-ads-ad-types', [ $this, 'setup_default_ad_types' ], 5 );

		// register hooks and filters for auto ad injection.
		$this->init_injection();

		// add meta robots noindex, nofollow to images, which are part of 'Image ad' ad type.
		add_action( 'wp_head', [ $this, 'noindex_attachment_images' ] );

		// use custom CSS or other custom header code.
		add_action( 'wp_head', [ $this, 'custom_header_code' ] );

		// check if ads are disabled in secondary queries.
		add_action( 'the_post', [ $this, 'set_query_type' ], 10, 2 );
		add_action( 'loop_start', [ $this, 'set_loop_start' ], 10, 0 );
		add_action( 'loop_end', [ $this, 'set_loop_end' ], 10, 0 );

		// register debug parameter
		$this->debug_parameter();
	}

	/**
	 *  Allow add-ons to hook
	 */
	public function advanced_ads_loaded() {
		do_action( 'advanced-ads-plugin-loaded' );
	}

	/**
	 * Init / load plugin specific functions and settings
	 */
	public function wp_init() {
		$this->set_ad_types();
	}

	/**
	 * Define ad types with their options
	 * name => publically readable name
	 * description => publically readable description
	 * editor => kind of editor: text (normal text field), content (WP content field), none (no content field)
	 *  will display text field, if left empty
	 */
	public function set_ad_types() {

		/**
		 * Load default ad type files
		 * custom ad types can also be loaded in your own plugin or functions.php
		 */
		$types = [];

		/**
		 * Developers can add new ad types using this filter
		 * see classes/ad-type-content.php for an example for an ad type and usage of this filter
		*/
		$this->ad_types = apply_filters( 'advanced-ads-ad-types', $types );
	}

	/**
	 * Load filters to inject ads into various sections of our site
	 */
	public function init_injection() {
		// -TODO abstract
		add_action( 'wp_head', [ $this, 'inject_header' ], 20 );
		add_action( 'wp_footer', [ $this, 'inject_footer' ], 20 );
		add_filter( 'the_content', [ $this, 'inject_content' ], $this->plugin->get_content_injection_priority() );
	}

	/**
	 * Set global constant that prevents ads from being displayed on the current page view
	 */
	public function set_disabled_constant() {

		global $post, $wp_the_query;

		// don't set the constant if already defined.
		if ( defined( 'ADVADS_ADS_DISABLED' ) ) {
			return; }

		$options = $this->plugin->options();

		// check if ads are disabled completely.
		if ( ! empty( $options['disabled-ads']['all'] ) ) {
			$this->disable_ads( 'all' );
			return;
		}

		// Check if ads are disabled in REST API.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			if ( ! empty( $options['disabled-ads']['rest-api'] ) ) {
				$this->disable_ads();
			}
			return;
		}

		// check if ads are disabled from 404 pages.
		if ( $wp_the_query->is_404() && ! empty( $options['disabled-ads']['404'] ) ) {
			$this->disable_ads( '404' );
			return;
		}

		// check if ads are disabled from non singular pages (often = archives).
		if ( ! $wp_the_query->is_singular() && ! empty( $options['disabled-ads']['archives'] ) ) {
			$this->disable_ads( 'archive' );
			return;
		}

		// check if ads are disabled in Feed.
		if ( $wp_the_query->is_feed() && ( ! isset( $options['disabled-ads']['feed'] ) || $options['disabled-ads']['feed'] ) ) {
			$this->disable_ads();
			return;
		}

		// check if ads are disabled on the current page.
		if ( $wp_the_query->is_singular() && isset( $post->ID ) ) {
			$post_ad_options = get_post_meta( $post->ID, '_advads_ad_settings', true );

			if ( ! empty( $post_ad_options['disable_ads'] ) ) {
				$this->disable_ads( 'page', $post->ID );
				return;
			}
		}

		// "Posts page" set on the WordPress Reading settings page.
		if ( $wp_the_query->is_posts_page ) {
			$post_ad_options = get_post_meta( $wp_the_query->queried_object_id, '_advads_ad_settings', true );

			if ( ! empty( $post_ad_options['disable_ads'] ) ) {
				$this->disable_ads( 'page', $wp_the_query->queried_object_id );
				return;
			}
		}

		/**
		 * Check if ads are disabled on WooCommerce shop page (and currently on shop page).
		 * since WooCommerce changes the post ID of the static page selected to be the product overview page, we need to get the original page id from the WC options.
		 */
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			$shop_id         = get_option( 'woocommerce_shop_page_id' );
			$shop_ad_options = get_post_meta( absint( $shop_id ), '_advads_ad_settings', true );
			if ( ! empty( $shop_ad_options['disable_ads'] ) ) {
				$this->disable_ads( 'page', $shop_id );
				return;
			}
		}

		if ( isset( $options['hide-for-user-role'] ) ) {
			$hide_for_roles = Advanced_Ads_Utils::maybe_translate_cap_to_role( $options['hide-for-user-role'] );
		} else {
			$hide_for_roles = [];
		}
		$user = wp_get_current_user();

		if ( $hide_for_roles && is_user_logged_in() && is_array( $user->roles ) && array_intersect( $hide_for_roles, $user->roles ) ) {
			$this->disable_ads();
			return;
		}

		// check bots if option is enabled.
		if ( isset( $options['block-bots'] ) && $options['block-bots']
			&& ! $this->is_cache_bot() && $this->is_bot() ) {
			$this->disable_ads();
			return;
		}
	}

	/**
	 * Disable ads.
	 *
	 * @param string     $reason Reason why are disabled.
	 * @param int|string $id Identifier that supplement the disabled reason.
	 */
	private function disable_ads( $reason = null, $id = null ) {
		define( 'ADVADS_ADS_DISABLED', true );
		$this->disabled_reason = $reason;
		$this->disabled_id = $id;
	}

	/**
	 * Return the plugin slug.
	 *
	 * @return string Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin->get_plugin_slug();
	}

	/**
	 * Add plain and content ad types to the default ads of the plugin using a filter
	 *
	 * @param array $types array with ad types.
	 * @return array
	 */
	public function setup_default_ad_types( $types ) {
		$types['plain']   = new Advanced_Ads_Ad_Type_Plain(); /* plain text and php code */
		$types['dummy']   = new Advanced_Ads_Ad_Type_Dummy(); /* dummy ad */
		$types['content'] = new Advanced_Ads_Ad_Type_Content(); /* rich content editor */
		$types['image']   = new Advanced_Ads_Ad_Type_Image(); /* image ads */
		$types['group']   = new Advanced_Ads_Ad_Type_Group(); /* group ad */
		return $types;
	}

	/**
	 * Log error messages when debug is enabled
	 *
	 * @param string $message error message.
	 * @link http://www.smashingmagazine.com/2011/03/08/ten-things-every-wordpress-plugin-developer-should-know/
	 */
	public static function log( $message ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( __( 'Advanced Ads Error following:', 'advanced-ads' ) );
				error_log( print_r( $message, true ) );
			} else {
				// translators: %s is an error message generated by the plugin.
				$message = sprintf( __( 'Advanced Ads Error: %s', 'advanced-ads' ), $message );
				error_log( $message );
			}
		}
	}

	/**
	 * Compat method
	 *
	 * @return array with plugin options
	 */
	public function options() {
		return $this->plugin->options();
	}

	/**
	 * Compat method
	 *
	 * @return array with adsense options
	 */
	public function get_adsense_options() {
		// we can’t store options if WPML String Translations is enabled, or it would not translate the "Ad Label" option.
		if ( ! isset( $this->adsense_options ) || class_exists( 'WPML_ST_String' ) ) {
			$this->adsense_options = get_option( 'advanced-ads-adsense', [] );
		}
		return $this->adsense_options;
	}

	/**
	 * Compat method
	 *
	 * @return array with internal plugin options
	 */
	public function internal_options() {
		return $this->plugin->internal_options();
	}

	/**
	 * Injected ad into header
	 */
	public function inject_header() {
		$placements = get_option( 'advads-ads-placements', [] );
		if ( is_array( $placements ) ) {
			foreach ( $placements as $_placement_id => $_placement ) {
				if ( isset( $_placement['type'] ) && 'header' === $_placement['type'] ) {
					$_options = isset( $_placement['options'] ) ? $_placement['options'] : [];
					// injecting ad code so we don’t run escaping here.
					// phpcs:ignore
					echo Advanced_Ads_Select::get_instance()->get_ad_by_method( $_placement_id, Advanced_Ads_Select::PLACEMENT, $_options );
				}
			}
		}
	}

	/**
	 * Injected ads into footer
	 *
	 * @since 1.1.0
	 */
	public function inject_footer() {
		$placements = get_option( 'advads-ads-placements', [] );
		if ( is_array( $placements ) ) {
			foreach ( $placements as $_placement_id => $_placement ) {
				if ( isset( $_placement['type'] ) && 'footer' === $_placement['type'] ) {
					$_options = isset( $_placement['options'] ) ? $_placement['options'] : [];
					// injecting ad code so we don’t run escaping here.
					// phpcs:ignore
					echo Advanced_Ads_Select::get_instance()->get_ad_by_method( $_placement_id, Advanced_Ads_Select::PLACEMENT, $_options );
				}
			}
		}
	}

	/**
	 * Injected ad into content (before and after)
	 * Displays ALL ads
	 *
	 * @param string $content post content.
	 *
	 * @return string
	 */
	public function inject_content( $content = '' ) {
		$options = $this->plugin->options();

		// do not inject in content when on a BuddyPress profile upload page (avatar & cover image).
		if ( ( function_exists( 'bp_is_user_change_avatar' ) && bp_is_user_change_avatar() ) || ( function_exists( 'bp_is_user_change_cover_image' ) && bp_is_user_change_cover_image() ) ) {
			return $content;
		}

		// do not inject ads multiple times, e.g., when the_content is applied multiple times.
		if ( $this->has_many_the_content() ) {
			return $content;
		}

		// Check if ads are disabled in secondary queries.
		if ( ! empty( $options['disabled-ads']['secondary'] ) ) {
			// this function was called by ajax (in secondary query).
			if ( wp_doing_ajax() ) {
				return $content;
			}
			// get out of wp_router_page post type if ads are disabled in secondary queries.
            if ( 'wp_router_page' === get_post_type() ) {
                return $content;
            }
		}

		// No need to inject ads because all tags are stripped from excepts.
		if ( doing_filter( 'get_the_excerpt' ) ) {
			return $content;
		}

		// run only within the loop on single pages of public post types.
		$public_post_types = get_post_types(
			[
				'public'             => true,
				'publicly_queryable' => true,
			],
			'names',
			'or'
		);

		// make sure that no ad is injected into another ad.
		if ( get_post_type() === Entities::POST_TYPE_AD ) {
			return $content;
		}

		// Do not inject on admin pages.
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $content;
		}

		// Do not inject in writing REST requests.
		if (  WordPress::is_gutenberg_writing_request() && WordPress::is_rest_request() ) {
			return $content;
		}

		// check if admin allows injection in all places.
		$enabled = $options['content-injection-enabled'] ?? 'off';
		if ( $enabled === 'off' || ! isset( $options['content-injection-everywhere'] ) || 0 === $options['content-injection-everywhere'] ) {
					// check if this is a singular page within the loop or an AMP page.
					$is_amp = advads_is_amp();
			if ( ( ! is_singular( $public_post_types ) && ! is_feed() ) || ( ! $is_amp && ! $this->in_the_loop() && ! $this->was_in_the_loop ) ) {
				return $content; }
		} else {
					global $wp_query;
			if ( is_main_query() && 'true' !== $options['content-injection-everywhere'] && isset( $wp_query->current_post ) && $wp_query->current_post >= ( $options['content-injection-everywhere'] ) ) {
				return $content;
			}
		}

		$placements = get_option( 'advads-ads-placements', [] );

		if ( ! apply_filters( 'advanced-ads-can-inject-into-content', true, $content, $placements ) ) {
			return $content;
		}

		if ( is_array( $placements ) ) {
			foreach ( $placements as $_placement_id => $_placement ) {
				if ( empty( $_placement['item'] ) || ! isset( $_placement['type'] ) ) {
					continue; }
				$_options = isset( $_placement['options'] ) ? $_placement['options'] : [];

				// check if injection is ok for a specific placement ID.
				if ( ! apply_filters( 'advanced-ads-can-inject-into-content-' . $_placement_id, true, $content, $_placement_id ) ) {
					continue;
				}

				switch ( $_placement['type'] ) {
					case 'post_top':
						$content = Advanced_Ads_Select::get_instance()->get_ad_by_method( $_placement_id, Advanced_Ads_Select::PLACEMENT, $_options ) . $content;
						break;
					case 'post_bottom':
						$content .= Advanced_Ads_Select::get_instance()->get_ad_by_method( $_placement_id, Advanced_Ads_Select::PLACEMENT, $_options );
						break;
					case 'post_content':
						$content = Advanced_Ads_Placements::inject_in_content( $_placement_id, $_options, $content );
						break;
				}
			}
		}

		if ( ! empty( $_COOKIE['advads_frontend_picker'] ) ) {
			// Make possible to know where the content starts and ends.
			$content = '<ins style="display: none;" class="advads-frontend-picker-boundary-helper"></ins>
			' . $content;
		}

		return $content;
	}

	/**
	 * Load all ads based on WP_Query conditions
	 *
	 * @deprecated 1.4.8 use model class
	 * @param array $args WP_Query arguments that are more specific that default.
	 * @return array $ads array with post objects
	 */
	public static function get_ads( $args = [] ) {
		return self::get_instance()->get_model()->get_ads( $args );
	}

	/**
	 * Load all ad groups
	 *
	 * @deprecated 1.4.8 use model class
	 * @param array $args array with options.
	 * @return array $groups array with ad groups
	 * @link http://codex.wordpress.org/Function_Reference/get_terms
	 */
	public static function get_ad_groups( $args = [] ) {
		return self::get_instance()->get_model()->get_ad_groups( $args );
	}

	/**
	 * Get the array with ad placements
	 *
	 * @deprecated 1.4.8 use model
	 * @return array $ad_placements
	 */
	public static function get_ad_placements_array() {
		return self::get_instance()->get_model()->get_ad_placements_array();
	}

	/**
	 * Get ad conditions.
	 *
	 * @deprecated 1.4.8 use model
	 * @return array
	 */
	public static function get_ad_conditions() {
		return self::get_instance()->get_model()->get_ad_conditions();
	}

	/**
	 * General check if ads can be displayed for the whole page impression
	 *
	 * @return bool true, if ads can be displayed.
	 * @todo move this to set_disabled_constant().
	 */
	public function can_display_ads() {

		// check global constant if ads are enabled or disabled.
		if ( defined( 'ADVADS_ADS_DISABLED' ) ) {
			return false;
		}

		$options = $this->options();

		// check if ads are disabled in secondary queries.
		// and this is not main query and this is not ajax (because main query does not exist in ajax but ad needs to be shown).
		if ( ! empty( $options['disabled-ads']['secondary'] ) && ! $this->is_main_query() && ! wp_doing_ajax() ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the current user agent is given or a bot
	 *
	 * @return bool true if the current user agent is empty or a bot.
	 * @since 1.4.9
	 */
	public function is_bot() {
		// show ads on AMP version also for bots in order to allow Google (and maybe others) to cache the page.
		if ( advads_is_amp() ) {
			return false;
		}

		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return true;
		}

		$bots = apply_filters( 'advanced-ads-bots', $this->bots );
		$bots = implode( '|', $bots );
		// Make sure delimiters in regex are escaped.
		$bots = preg_replace( '/(.*?)(?<!\\\)' . preg_quote( '/', '/' ) . '(.*?)/', '$1\\/$2', $bots );

		return preg_match( sprintf( '/%s/i', $bots ), wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
	}

	/**
	 * Get the array of known bots.
	 *
	 * @param bool $filter Whether to apply filters.
	 *
	 * @return array
	 */
	public function get_bots( $filter = true ) {
		return (array) ( $filter ? apply_filters( 'advanced-ads-bots', $this->bots ) : $this->bots );
	}

	/**
	 * Check if the current user is a bot prepopulating the cache
	 * Ads should be loaded for the bot, because they should show up on the cached site
	 *
	 * @return bool
	 */
	public function is_cache_bot() {
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && '' !== $_SERVER['HTTP_USER_AGENT'] ) {
			$current = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
			// WP Rocket.
			if ( false !== strpos( $current, 'wprocketbot' ) ) {
				return true;
			}

			// WP Super Cache.
			$wp_useragent = apply_filters( 'http_headers_useragent', 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) );
			if ( $current === $wp_useragent ) {
				return true;
			}

			// LiteSpeed Cache: `lscache_runner` and `lscache_walker` user agents.
			if ( false !== strpos( $current, 'lscache_' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add meta robots noindex, nofollow to images, which are part of 'Image ad' ad type
	 */
	public function noindex_attachment_images() {
		global $post;

		if ( is_attachment() && is_object( $post ) && isset( $post->post_parent ) ) {
			$post_parent  = get_post( $post->post_parent );
			$parent_is_ad = $post_parent && Entities::POST_TYPE_AD === $post_parent->post_type;
			// if the image was not attached to any post and if at least one image ad contains the image. Needed for backward compatibility.
			$parent_is_image_ad = ( empty( $post->post_parent ) && 0 < get_post_meta( get_the_ID(), '_advanced-ads_parent_id', true ) );

			if ( $parent_is_ad || $parent_is_image_ad ) {
				echo '<meta name="robots" content="noindex,nofollow" />';
			}
		}
	}

	/**
	 * Show custom CSS in the header
	 */
	public function custom_header_code(){
		if ( ! defined( 'ADVANCED_ADS_DISABLE_EDIT_BAR' ) && WordPress::user_can( 'advanced_ads_edit_ads' ) ) {
			?><style>
				div.advads-edit-bar{position:absolute;height:0;display:none;z-index:10000;animation:advads-edit-appear 2s linear 1;}
				@keyframes advads-edit-appear {
					0% {opacity: 0.0;pointer-events: none;}
					66% {opacity: 0.0;}
					100% {opacity: 1.0;}
				}
				a.advads-edit-button{position:absolute;top:0;left:0;text-decoration:none !important;box-shadow:none;border-bottom:none;color:#0074a2;margin-top:-5px;}
				a.advads-edit-button span{top:10px;line-height:25px;margin-left:-5px;width:26px;height:26px;border-radius:13px;border:solid 1px #0074a2;background:#fff}
				<?php
				printf(
					'div[class^="%s"]:hover > div.advads-edit-bar {display: inline-block; vertical-align: top;}',
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					Advanced_Ads_Plugin::get_instance()->get_frontend_prefix()
				);
				?>
				</style>
				<?php
		}
	}

	/**
	 * Supports the "$this->is_main_query=true" while main query is being executed
	 *
	 * @param WP_Post  $post The Post object (passed by reference).
	 * @param WP_Query $query The current Query object (passed by reference).
	 */
	public function set_query_type( $post, $query = null ) {
		if ( $query instanceof WP_Query ) {
			$this->is_main_query = $query->is_main_query();
		}
	}

	/**
	 * Check if main query is being executed
	 *
	 * @return bool true while main query is being executed or not in the loop, false otherwise
	 */
	public function is_main_query() {
		if ( ! $this->in_the_loop() ) {
			// the secondary query check only designed for within post content.
			return true;
		}

		return true === $this->is_main_query;
	}

	/**
	 * Sets whether the loop has started.
	 */
	public function set_loop_start() {
		$this->in_the_loop = true;
	}

	/**
	 * Sets whether the loop has ended.
	 */
	public function set_loop_end() {
		$this->in_the_loop = false;
	}


	/**
	 * Whether the loop has started and the caller is in the loop.
	 *
	 * @return bool
	 */
	public function in_the_loop() {
		if ( in_the_loop() ) {
			return true;
		}

		if ( $this->in_the_loop ) {
			return true;
		}
	}

	/**
	 * Find the calls to `the_content` inside functions hooked to `the_content`.
	 *
	 * @return bool
	 */
	public function has_many_the_content() {
		global $wp_current_filter;
		if ( count( array_keys( $wp_current_filter, 'the_content', true ) ) > 1 ) {
			// More then one `the_content` in the stack.
			return true;
		}
		return false;
	}

	/**
	 * Get an "Advertisement" label to use before single ad or before first ad in a group
	 *
	 * @param string $placement_state default/enabled/disabled.
	 * @return string label, empty string if label should not be displayed.
	 */
	public function get_label( $placement_state = 'default' ) {
		if ( 'disabled' === $placement_state ) {
			return '';
		}

		$advads_options = self::get_instance()->options();

		if ( 'enabled' !== $placement_state && empty( $advads_options['custom-label']['enabled'] ) ) {
			return '';
		}

		$label = ! empty( $advads_options['custom-label']['text'] ) ? esc_html( $advads_options['custom-label']['text'] ) : _x( 'Advertisements', 'label above ads', 'advanced-ads' );

		$template = sprintf( '<div class="%s">%s</div>', Advanced_Ads_Plugin::get_instance()->get_frontend_prefix() . 'adlabel', $label );
		return apply_filters( 'advanced-ads-custom-label', $template, $label );
	}

	/**
	 * Retrieve the number of ads in any status
	 * excludes trash status by default
	 *
	 * @param string|array $post_status default post status.
	 *
	 * @return int number of ads.
	 */
	public static function get_number_of_ads( $post_status = 'any' ) {
		$key = md5( serialize( $post_status ) );
		// query number of ads only, if not retrieved, yet.
		if ( ! isset( self::get_instance()->number_of_ads[ $key ] ) ) {
			$args                               = [ 'post_status' => $post_status ];
			$recent_ads                         = self::get_instance()->get_model()->get_ads( $args );
			self::get_instance()->number_of_ads[ $key ] = count( $recent_ads );
		}

		return self::get_instance()->number_of_ads[ $key ];
	}

	/**
	 * Switch the current blog.
	 *
	 * @param int $blog_id ID of the blog in the WP network.
	 */
	public function switch_to_blog( $blog_id ) {
		if ( is_multisite() ) {
			switch_to_blog( $blog_id );
			self::get_instance()->get_model()->reset_placement_array();
		}
	}

	/**
	 * Restore the current blog.
	 */
	public function restore_current_blog() {
		if ( is_multisite() ) {
			restore_current_blog();
			self::get_instance()->get_model()->reset_placement_array();
		}
	}

	/**
	 * Store whether the loop started in an inner `the_content`.
	 *
	 * If so, let us assume that we are in the loop when we are in the outermost `the_content`.
	 * Makes sense only when a hooked to `the_content` function that produces an inner `the_content` has
	 * lesser priority then `$this->plugin->get_content_injection_priority()`.
	 *
	 * @param string $content Post content (unchanged).
	 *
	 * @return string
	 */
	public function set_was_in_the_loop( $content ) {
		if ( self::get_instance()->has_many_the_content() ) {
			$this->was_in_the_loop = $this->was_in_the_loop || $this->in_the_loop();
		} else {
			// Next top level `the_content`, forget that the loop started.
			$this->was_in_the_loop = false;
		}

		return $content;
	}

	/**
	 * Listen to URL parameters for debugging
	 */
	private function debug_parameter() {
		if ( wp_doing_ajax() && isset( $_SERVER['HTTP_REFERER'] ) ) {
			$query_string = wp_parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_QUERY );
			if ( $query_string ) {
				parse_str( $query_string, $query );
			}

			if ( empty( $query['aa-debug'] ) ) {
				return;
			}

			$debug_query = $query['aa-debug'];
		} else {
			if ( empty( $_GET['aa-debug'] ) ) {
				return;
			}
			$debug_query = $_GET['aa-debug'];
		}

		$parameters = explode( ',', sanitize_text_field( $debug_query ) );
		foreach ( $parameters as $parameter ) {
			switch ( trim( $parameter ) ) {
				case 'dummy':
					// switch all ads to "dummy"
					add_filter( 'advanced-ads-ad-option-type', function() {
						return 'dummy';
					} );
					break;
				case 'vcoff':
					// disable ad visitor conditions
					add_filter( 'advanced-ads-ad-option-visitors', '__return_empty_array' );
					break;
				case 'cboff':
					// disable cache-busting for all ads
					add_filter( 'advanced-ads-ad-select-args', function( $args ) {
						$args['cache-busting'] = 'ignore';
						return $args;
					} );
					break;
			}
		}
	}
}
