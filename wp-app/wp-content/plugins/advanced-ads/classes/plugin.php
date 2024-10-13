<?php
// phpcs:ignoreFile

use AdvancedAds\Entities;
use AdvancedAds\Installation\Capabilities;
use AdvancedAds\Utilities\WordPress;

/**
 * WordPress integration and definitions:
 *
 * - textdomain
 */
class Advanced_Ads_Plugin {
	/**
	 * Instance of Advanced_Ads_Plugin
	 *
	 * @var object Advanced_Ads_Plugin
	 */
	protected static $instance;

	/**
	 * Instance of Advanced_Ads_Model
	 *
	 * @var object Advanced_Ads_Model
	 */
	protected $model;

	/**
	 * Plugin options
	 *
	 * @var array $options
	 */
	protected $options;

	/**
	 * Interal plugin options – set by the plugin
	 *
	 * @var     array $internal_options
	 */
	protected $internal_options;

	/**
	 * Default prefix of selectors (id, class) in the frontend
	 * can be changed by options
	 *
	 * @var Advanced_Ads_Plugin
	 */
	const DEFAULT_FRONTEND_PREFIX = 'advads-';

	/**
	 * Frontend prefix for classes and IDs
	 *
	 * @var string $frontend_prefix
	 */
	private $frontend_prefix;

	/**
	 * Advanced_Ads_Plugin constructor.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'wp_plugins_loaded' ], 20 );
		add_action( 'init', [ $this, 'run_upgrades' ], 9 );
	}

	/**
	 * Get instance of Advanced_Ads_Plugin
	 *
	 * @return Advanced_Ads_Plugin
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get instance of Advanced_Ads_Model
	 *
	 * @param Advanced_Ads_Model $model model to access data.
	 */
	public function set_model( Advanced_Ads_Model $model ) {
		$this->model = $model;
	}

	/**
	 * Execute various hooks after WordPress and all plugins are available
	 */
	public function wp_plugins_loaded() {
		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_head', [ $this, 'print_head_scripts' ], 7 );
		// higher priority to make sure other scripts are printed before.
		add_action( 'wp_footer', [ $this, 'print_footer_scripts' ], 100 );

		// add short codes.
		add_shortcode( 'the_ad', [ $this, 'shortcode_display_ad' ] );
		add_shortcode( 'the_ad_group', [ $this, 'shortcode_display_ad_group' ] );
		add_shortcode( 'the_ad_placement', [ $this, 'shortcode_display_ad_placement' ] );

		// load widgets.
		add_action( 'widgets_init', [ $this, 'widget_init' ] );

		// Call action hooks for ad status changes.
		add_action( 'transition_post_status', [ $this, 'transition_ad_status' ], 10, 3 );

		// register expired post status.
		Advanced_Ads_Ad_Expiration::register_post_status();

		// if expired ad gets untrashed, revert it to expired status (instead of draft).
		add_filter( 'wp_untrash_post_status', [ Advanced_Ads_Ad_Expiration::class, 'wp_untrash_post_status' ], 10, 3 );

		// load display conditions.
		Advanced_Ads_Display_Conditions::get_instance();
		new Advanced_Ads_Frontend_Checks();
		new Advanced_Ads_Compatibility();
		Advanced_Ads_Ad_Health_Notices::get_instance(); // load to fetch notices.
	}

	/**
	 * Run upgrades.
	 *
	 * Compatibility with the Piklist plugin that has a function hooked to `posts_where` that access $GLOBALS['wp_query'].
	 * Since `Advanced_Ads_Upgrades` applies `posts_where`: (`Advanced_Ads_Admin_Notices::get_instance()` >
	 * `Advanced_Ads::get_number_of_ads()` > new WP_Query > ... 'posts_where') this function is hooked to `init` so that `$GLOBALS['wp_query']` is instantiated.
	 */
	public function run_upgrades() {
		/**
		 * Run upgrades, if this is a new version or version does not exist.
		 */
		$internal_options = $this->internal_options();

		if ( ! defined( 'DOING_AJAX' ) && ( ! isset( $internal_options['version'] ) || version_compare( $internal_options['version'], ADVADS_VERSION, '<' ) ) ) {
			new Advanced_Ads_Upgrades();
		}
	}

	/**
	 * Return the plugin slug.
	 *
	 * @return   string plugin slug variable.
	 */
	public function get_plugin_slug() {
		return ADVADS_SLUG;
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 */
	public function enqueue_scripts() {
		if ( advads_is_amp() ) {
			return;
		}

		wp_register_script(
			$this->get_plugin_slug() . '-advanced-js',
			sprintf( '%spublic/assets/js/advanced%s.js', ADVADS_BASE_URL, defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ),
			[ 'jquery' ],
			ADVADS_VERSION,
			false
		);

		$privacy                    = Advanced_Ads_Privacy::get_instance();
		$privacy_options            = $privacy->options();
		$privacy_options['enabled'] = ! empty( $privacy_options['enabled'] );
		$privacy_options['state']   = $privacy->get_state();

		wp_localize_script(
			$this->get_plugin_slug() . '-advanced-js',
			'advads_options',
			[
				'blog_id' => get_current_blog_id(),
				'privacy' => $privacy_options,
			]
		);

		$activated_js = apply_filters( 'advanced-ads-activate-advanced-js', isset( $this->options()['advanced-js'] ) );

		if ( $activated_js || ! empty( $_COOKIE['advads_frontend_picker'] ) ) {
			wp_enqueue_script( $this->get_plugin_slug() . '-advanced-js' );
		}

		wp_register_script(
			$this->get_plugin_slug() . '-frontend-picker',
			sprintf( '%spublic/assets/js/frontend-picker%s.js', ADVADS_BASE_URL, defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ),
			[ 'jquery', $this->get_plugin_slug() . '-advanced-js' ],
			ADVADS_VERSION,
			false
		);

		if ( ! empty( $_COOKIE['advads_frontend_picker'] ) ) {
			wp_enqueue_script( $this->get_plugin_slug() . '-frontend-picker' );
		}
	}

	/**
	 * Print public-facing JavaScript in the HTML head.
	 */
	public function print_head_scripts() {
		$short_url   = self::get_short_url();
		$attribution = '<!-- ' . $short_url . ' is managing ads with Advanced Ads%1$s%2$s -->';
		$version     = self::is_new_user( 1585224000 ) ? ' ' . ADVADS_VERSION : '';
		$plugin_url  = self::get_group_by_url( $short_url, 'a' ) ? ' – https://wpadvancedads.com/' : '';
		// escaping would break HTML comment tags so we disable checks here.
		// phpcs:ignore
		echo apply_filters( 'advanced-ads-attribution', sprintf( $attribution, $version, $plugin_url ) );

		if ( advads_is_amp() ) {
			return;
		}

		$frontend_prefix = $this->get_frontend_prefix();

		ob_start();
		?>
		<script id="<?php echo esc_attr( $frontend_prefix ); ?>ready">
			<?php
			readfile( sprintf(
				'%spublic/assets/js/ready%s.js',
				ADVADS_ABSPATH,
				defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min'
			) );
			?>
		</script>
		<?php

		/**
		 * Print inline script in the page header form add-ons.
		 *
		 * @param string $frontend_prefix the prefix used for Advanced Ads related HTML ID-s and classes.
		 */
		do_action( 'advanced_ads_inline_header_scripts', $frontend_prefix );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaping would break the HTML
		echo Advanced_Ads_Utils::get_inline_asset( ob_get_clean() );
	}

	/**
	 * Print inline scripts in wp_footer.
	 */
	public function print_footer_scripts() {
		if ( advads_is_amp() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaping would break the HTML
		echo Advanced_Ads_Utils::get_inline_asset(
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- we're getting the contents of a local file
			sprintf( '<script>%s</script>', file_get_contents( sprintf(
				'%spublic/assets/js/ready-queue%s.js',
				ADVADS_ABSPATH,
				defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min'
			) ) )
		);
	}

	/**
	 * Register the Advanced Ads widget
	 */
	public function widget_init() {
		register_widget( 'Advanced_Ads_Widget' );
	}

	/**
	 * Shortcode to include ad in frontend
	 *
	 * @param array $atts shortcode attributes.
	 *
	 * @return string ad content.
	 */
	public function shortcode_display_ad( $atts ) {
		$atts = is_array( $atts ) ? $atts : [];
		$id   = isset( $atts['id'] ) ? (int) $atts['id'] : 0;
		// check if there is an inline attribute with or without value.
		if ( isset( $atts['inline'] ) || in_array( 'inline', $atts, true ) ) {
			$atts['inline_wrapper_element'] = true;
		}
		$atts = $this->prepare_shortcode_atts( $atts );

		// use the public available function here.
		return get_ad( $id, $atts );
	}

	/**
	 * Shortcode to include ad from an ad group in frontend
	 *
	 * @param array $atts shortcode attributes.
	 *
	 * @return string ad group content.
	 */
	public function shortcode_display_ad_group( $atts ) {
		$atts = is_array( $atts ) ? $atts : [];
		$id   = isset( $atts['id'] ) ? (int) $atts['id'] : 0;
		$atts = $this->prepare_shortcode_atts( $atts );

		// use the public available function here.
		return get_ad_group( $id, $atts );
	}

	/**
	 * Shortcode to display content of an ad placement in frontend
	 *
	 * @param array $atts shortcode attributes.
	 *
	 * @return string ad placement content.
	 */
	public function shortcode_display_ad_placement( $atts ) {
		$atts = is_array( $atts ) ? $atts : [];
		$id   = isset( $atts['id'] ) ? (string) $atts['id'] : '';
		$atts = $this->prepare_shortcode_atts( $atts );

		// use the public available function here.
		return get_ad_placement( $id, $atts );
	}

	/**
	 * Prepare shortcode attributes.
	 *
	 * @param array $atts array with strings.
	 *
	 * @return array
	 */
	private function prepare_shortcode_atts( $atts ) {
		$result = [];

		/**
		 * Prepare attributes by converting strings to multi-dimensional array
		 * Example: [ 'output__margin__top' => 1 ]  =>  ['output']['margin']['top'] = 1
		 */
		if ( ! defined( 'ADVANCED_ADS_DISABLE_CHANGE' ) || ! ADVANCED_ADS_DISABLE_CHANGE ) {
			foreach ( $atts as $attr => $data ) {
				$levels = explode( '__', $attr );
				$last   = array_pop( $levels );

				$cur_lvl = &$result;

				foreach ( $levels as $lvl ) {
					if ( ! isset( $cur_lvl[ $lvl ] ) ) {
						$cur_lvl[ $lvl ] = [];
					}

					$cur_lvl = &$cur_lvl[ $lvl ];
				}

				$cur_lvl[ $last ] = $data;
			}

			$result = array_diff_key(
				$result,
				[
					'id'      => false,
					'blog_id' => false,
					'ad_args' => false,
				]
			);
		}

		// Ad type: 'content' and a shortcode inside.
		if ( isset( $atts['ad_args'] ) ) {
			$result = array_merge( $result, json_decode( urldecode( $atts['ad_args'] ), true ) );

		}

		return $result;
	}

	/**
	 * Return plugin options
	 * these are the options updated by the user
	 *
	 * @return array $options
	 */
	public function options() {
		// we can’t store options if WPML String Translations is enabled, or it would not translate the "Ad Label" option.
		if ( ! isset( $this->options ) || class_exists( 'WPML_ST_String' ) ) {
			$this->options = get_option( ADVADS_SLUG, [] );
		}

		// allow to change options dynamically
		$this->options = apply_filters( 'advanced-ads-options', $this->options );

		return $this->options;
	}

	/**
	 * Update plugin options (not for settings page, but if automatic options are needed)
	 *
	 * @param array $options new options.
	 */
	public function update_options( array $options ) {
		// do not allow to clear options.
		if ( [] === $options ) {
			return;
		}

		$this->options = $options;
		update_option( ADVADS_SLUG, $options );
	}

	/**
	 * Return internal plugin options
	 * these are options set by the plugin
	 *
	 * @return array $options
	 */
	public function internal_options() {
		if ( ! isset( $this->internal_options ) ) {
			$defaults               = [
				'version'   => ADVADS_VERSION,
				'installed' => time(), // when was this installed.
			];
			$this->internal_options = get_option( ADVADS_SLUG . '-internal', [] );

			// save defaults.
			if ( [] === $this->internal_options ) {
				$this->internal_options = $defaults;
				$this->update_internal_options( $this->internal_options );

				Capabilities::get_instance()->create_capabilities();
			}

			// for versions installed prior to 1.5.3 set installed date for now.
			if ( ! isset( $this->internal_options['installed'] ) ) {
				$this->internal_options['installed'] = time();
				$this->update_internal_options( $this->internal_options );
			}
		}

		return $this->internal_options;
	}

	/**
	 * Update internal plugin options
	 *
	 * @param array $options new internal options.
	 */
	public function update_internal_options( array $options ) {
		// do not allow to clear options.
		if ( [] === $options ) {
			return;
		}

		$this->internal_options = $options;
		update_option( ADVADS_SLUG . '-internal', $options );
	}

	/**
	 * Get prefix used for frontend elements
	 *
	 * @return string
	 */
	public function get_frontend_prefix() {
		if ( isset( $this->frontend_prefix ) ) {
			return $this->frontend_prefix;
		}

		$options = $this->options();

		if ( ! isset( $options['front-prefix'] ) ) {
			if ( isset( $options['id-prefix'] ) ) {
				// deprecated: keeps widgets working that previously received an id based on the front-prefix.
				$frontend_prefix = $options['id-prefix'];
			} else {
				$frontend_prefix = preg_match( '/[A-Za-z][A-Za-z0-9_]{4}/', parse_url( get_home_url(), PHP_URL_HOST ), $result )
					? $result[0] . '-'
					: self::DEFAULT_FRONTEND_PREFIX;
			}
		} else {
			$frontend_prefix = $options['front-prefix'];
		}
		/**
		 * Applying the filter here makes sure that it is the same frontend prefix for all
		 * calls on this page impression
		 *
		 * @param string $frontend_prefix
		 */
		$this->frontend_prefix = (string) apply_filters( 'advanced-ads-frontend-prefix', $frontend_prefix );
		$this->frontend_prefix = $this->sanitize_frontend_prefix( $frontend_prefix );

		return $this->frontend_prefix;
	}

	/**
	 * Sanitize the frontend prefix to result in valid HTML classes.
	 * See https://www.w3.org/TR/selectors-3/#grammar for valid tokens.
	 *
	 * @param string $prefix The HTML class to sanitize.
	 * @param string $fallback The fallback if the class is invalid.
	 *
	 * @return string
	 */
	public function sanitize_frontend_prefix( $prefix, $fallback = '' ) {
		$prefix   = sanitize_html_class( $prefix );
		$nonascii = '[^\0-\177]';
		$unicode  = '\\[0-9a-f]{1,6}(\r\n|[ \n\r\t\f])?';
		$escape   = sprintf( '%s|\\[^\n\r\f0-9a-f]', $unicode );
		$nmstart  = sprintf( '[_a-z]|%s|%s', $nonascii, $escape );
		$nmchar   = sprintf( '[_a-z0-9-]|%s|%s', $nonascii, $escape );

		if ( ! preg_match( sprintf( '/-?(?:%s)(?:%s)*/i', $nmstart, $nmchar ), $prefix, $matches ) ) {
			return $fallback;
		}

		return $matches[0];
	}

	/**
	 * Get priority used for injection inside content
	 */
	public function get_content_injection_priority() {
		$options = $this->options();

		return isset( $options['content-injection-priority'] ) ? (int) $options['content-injection-priority'] : 100;
	}

	/**
	 * Returns the capability needed to perform an action
	 *
	 * @deprecated 1.47.0
	 *
	 * @param string $capability a capability to check, can be internal to Advanced Ads.
	 *
	 * @return string $capability a valid WordPress capability.
	 */
	public static function user_cap( $capability = 'manage_options' ) {
		_deprecated_function( __METHOD__, '1.47.0', '\AdvancedAds\Utilities\WordPress::user_cap()' );
		return WordPress::user_cap( $capability );
	}

	/**
	 * Create roles and capabilities
	 *
	 * @deprecated 1.47.0
	 */
	public function create_capabilities() {
		_deprecated_function( __METHOD__, '1.47.0', 'AdvancedAds\Installation\Capabilities::get_instance()->create_capabilities();' );

		Capabilities::get_instance()->create_capabilities();
	}

	/**
	 * Remove roles and capabilities
	 *
	 * @deprecated 1.47.0
	 */
	public function remove_capabilities() {
		_deprecated_function( __METHOD__, '1.47.0', 'AdvancedAds\Installation\Capabilities::remove_capabilities()' );

		( new Capabilities() )->remove_capabilities();
	}

	/**
	 * Check if any add-on is activated
	 *
	 * @return bool true if there is any add-on activated
	 */
	public static function any_activated_add_on() {
		return ( defined( 'AAP_VERSION' )    // Advanced Ads Pro.
				 || defined( 'AAGAM_VERSION' )    // Google Ad Manager.
				 || defined( 'AASA_VERSION' )    // Selling Ads.
				 || defined( 'AAT_VERSION' )        // Tracking.
				 || defined( 'AASADS_VERSION' )  // Sticky Ads.
				 || defined( 'AAR_VERSION' )        // Responsive Ads.
				 || defined( 'AAPLDS_VERSION' )  // PopUp and Layer Ads.
		);
	}

	/**
	 * Get the correct support URL: wp.org for free users and website for those with any add-on installed
	 *
	 * @param string $utm add UTM parameter to the link leading to https://wpadvancedads.com, if given.
	 *
	 * @return string URL.
	 */
	public static function support_url( $utm = '' ) {

		$utm = empty( $utm ) ? '?utm_source=advanced-ads&utm_medium=link&utm_campaign=support' : $utm;
		if ( self::any_activated_add_on() ) {
			$url = 'https://wpadvancedads.com/support/' . $utm . '-with-addons';
		} else {
			$url = 'https://wpadvancedads.com/support/' . $utm . '-free-user';
		}

		return $url;
	}

	/**
	 * Create a random group
	 *
	 * @param string $url optional parameter.
	 * @param string $ex group.
	 *
	 * @return bool
	 */
	public static function get_group_by_url( $url = '', $ex = 'a' ) {

		$url = self::get_short_url( $url );

		$code = (int)substr( md5( $url ), - 1 );

		switch ( $ex ) {
			case 'b':
				return ( $code & 2 ) >> 1; // returns 1 or 0.
			case 'c':
				return ( $code & 4 ) >> 2; // returns 1 or 0.
			case 'd':
				return ( $code & 8 ) >> 3; // returns 1 or 0.
			default:
				return $code & 1; // returns 1 or 0.
		}
	}

	/**
	 * Check if user started after a given date
	 *
	 * @param integer $timestamp time stamp.
	 *
	 * @return bool true if user is added after timestamp.
	 */
	public static function is_new_user( $timestamp = 0 ) {

		// allow admins to see version for new users in any case.
		if ( WordPress::user_can( 'advanced_ads_manage_options' ) && isset( $_REQUEST['advads-ignore-timestamp'] ) ) {
			return true;
		}

		$timestamp = absint( $timestamp );

		$options   = self::get_instance()->internal_options();
		$installed = isset( $options['installed'] ) ? $options['installed'] : 0;

		return ( $installed >= $timestamp );
	}

	/**
	 * Show stuff to new users only.
	 *
	 * @param integer $timestamp time after which to show whatever.
	 * @param string  $group optional group.
	 *
	 * @return bool true if user enabled after given timestamp.
	 */
	public static function show_to_new_users( $timestamp, $group = 'a' ) {

		return ( self::get_group_by_url( null, $group ) && self::is_new_user( $timestamp ) );
	}

	/**
	 * Get short version of home_url()
	 * remove protocol and www
	 * remove slash
	 *
	 * @param string $url URL to be shortened.
	 *
	 * @return string
	 */
	public static function get_short_url( $url = '' ) {

		$url = empty( $url ) ? home_url() : $url;

		// strip protocols.
		if ( preg_match( '/^(\w[\w\d]*:\/\/)?(www\.)?(.*)$/', trim( $url ), $matches ) ) {
			$url = $matches[3];
		}

		// strip slashes.
		$url = trim( $url, '/' );

		return $url;
	}

	/**
	 * Fires when a post is transitioned from one status to another.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public function transition_ad_status( $new_status, $old_status, $post ) {
		if ( ! isset( $post->post_type ) || Entities::POST_TYPE_AD !== $post->post_type || ! isset( $post->ID ) ) {
			return;
		}

		$ad = \Advanced_Ads\Ad_Repository::get( $post->ID );

		if ( $old_status !== $new_status ) {
			/**
			 * Fires when an ad has transitioned from one status to another.
			 *
			 * @param Advanced_Ads_Ad $ad Ad object.
			 */
			do_action( "advanced-ads-ad-status-{$old_status}-to-{$new_status}", $ad );
		}

		if ( 'publish' === $new_status && 'publish' !== $old_status ) {
			/**
			 * Fires when an ad has transitioned from any other status to `publish`.
			 *
			 * @param Advanced_Ads_Ad $ad Ad object.
			 */
			do_action( 'advanced-ads-ad-status-published', $ad );
		}

		if ( 'publish' === $old_status && 'publish' !== $new_status ) {
			/**
			 * Fires when an ad has transitioned from `publish` to any other status.
			 *
			 * @param Advanced_Ads_Ad $ad Ad object.
			 */
			do_action( 'advanced-ads-ad-status-unpublished', $ad );
		}

		if ( $old_status === 'publish' && $new_status === Advanced_Ads_Ad_Expiration::POST_STATUS ) {
			/**
			 * Fires when an ad is expired.
			 *
			 * @param int             $id
			 * @param Advanced_Ads_Ad $ad
			 */
			do_action( 'advanced-ads-ad-expired', $ad->id, $ad );
		}
	}

}
