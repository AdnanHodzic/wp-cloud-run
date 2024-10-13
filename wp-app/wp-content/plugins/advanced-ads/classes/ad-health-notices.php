<?php // phpcs:ignoreFile
use AdvancedAds\Utilities\Conditional;

/**
 * Container class for Ad Health notice handling
 *
 * @package WordPress
 * @subpackage Advanced Ads Plugin
 * @since 1.12
 *
 * related scripts / functions
 *
 * advads_push_notice() function to push notifications using AJAX in admin/assets/js/admin-global.js
 * push_ad_health_notice() in Advanced_Ads_Ad_Ajax_Callbacks to push notifications sent via AJAX
 * Advanced_Ads_Checks – for the various checks
 * list of notification texts in admin/includes/ad-health-notices.php
 */
class Advanced_Ads_Ad_Health_Notices {

	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Options
	 *
	 * @var    array
	 */
	protected $options;

	/**
	 * All detected notices
	 *
	 * Structure is
	 *  [notice_key] => array(
	 *        'text'    - if not given, it uses the default text for output )
	 *        'orig_key'    - original notice key
	 *  )
	 *
	 * @var    array
	 */
	public $notices = [];

	/**
	 * All ignored notices
	 *
	 * @var    array
	 */
	public $ignore = [];

	/**
	 * All displayed notices ($notices minus $hidden)
	 *
	 * @var    array
	 */
	public $displayed_notices = [];

	/**
	 * Load default notices
	 *
	 * @var    array
	 */
	public $default_notices = [];

	/**
	 * The last notice key saved
	 *
	 * @var string
	 */
	public $last_saved_notice_key = false;

	/**
	 * Name of the transient saved for daily checks in the backend
	 *
	 * @const string
	 */
	const DAILY_CHECK_TRANSIENT_NAME = 'advanced-ads-daily-ad-health-check-ran';

	/**
	 * Plugin class
	 *
	 * @var string
	 */
	private $plugin;

	/**
	 * Advanced_Ads_Ad_Health_Notices constructor.
	 */
	public function __construct() {

		// failsafe for there were some reports of 502 errors.
		if ( 1 < did_action( 'plugins_loaded' ) ) {
			return;
		}

		// stop here if notices are disabled.
		if ( ! self::notices_enabled() ) {
			return;
		}

		// load default notices.
		if ( [] === $this->default_notices ) {
			include ADVADS_ABSPATH . '/admin/includes/ad-health-notices.php';
			$this->default_notices = $advanced_ads_ad_health_notices;
		}

		// fills the class arrays.
		$this->load_notices();

		/**
		 * Run checks
		 * needs to run after plugins_loaded with priority 10
		 * current_screen seems like the perfect hook
		 */
		add_action( 'current_screen', [ $this, 'run_checks' ], 20 );

		// add notification when an ad expires.
		add_action( 'advanced-ads-ad-expired', [ $this, 'ad_expired' ], 10, 2 );
	}

	/**
	 * Check if notices are enabled using "disable-notices" option in plugin settings
	 *
	 * @return bool
	 */
	public static function notices_enabled() {
		$options = Advanced_Ads::get_instance()->options();

		return empty( $options['disable-notices'] );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load notice arrays
	 */
	public function load_notices() {

		$options = $this->options();

		// load notices from "notices".
		$this->notices = isset( $options['notices'] ) ? $options['notices'] : [];

		/**
		 * Cleanup notices
		 */
		foreach ( $this->notices as $_key => $_notice ) {
			// without valid key caused by an issue prior to 1.13.3.
			if ( empty( $_key ) ) {
				unset( $this->notices[ $_key ] );
			};

			$time         = current_time( 'timestamp', 0 );
			$notice_array = $this->get_notice_array_for_key( $_key );

			// handle notices with a timeout.
			if ( isset( $_notice['closed'] ) ) {
				// remove notice when timeout expired – was closed longer ago than timeout set in the notice options.
				if ( empty( $notice_array['timeout'] )
					 || ( ( $time - $_notice['closed'] ) > $notice_array['timeout'] ) ) {
					$this->remove( $_key );
				} else {
					// just ignore notice if timeout is still valid.
					unset( $this->notices[ $_key ] );
				}
			}

			// check if notice still exists.
			if ( [] === $this->get_notice_array_for_key( $_key ) ) {
				unset( $this->notices[ $_key ] );
			}
		}

		// unignore notices if `show-hidden=true` is set in the URL.
		if ( isset( $_GET['advads_nonce'] ) && wp_verify_nonce( wp_unslash( $_GET['advads_nonce'] ), 'advanced-ads-show-hidden-notices' )
			&& isset( $_GET['advads-show-hidden-notices'] ) && 'true' === $_GET['advads-show-hidden-notices'] ) {
			$this->unignore();
			// remove the argument from the URL.
			add_filter( 'removable_query_args', [ $this, 'remove_query_vars_after_notice_update' ] );
		}

		// load hidden notices.
		$this->ignore = $this->get_valid_ignored();

		// get displayed notices
		// get keys of notices.
		$notice_keys             = array_keys( $this->notices );
		$this->displayed_notices = array_diff( $notice_keys, $this->ignore );
	}

	/**
	 * Remove query var from URL after notice was updated
	 *
	 * @param  array $removable_query_args array with removable query vars.
	 * @return array updated query vars.
	 */
	public function remove_query_vars_after_notice_update( $removable_query_args ) {
		$removable_query_args[] = 'advads-show-hidden-notices';
		$removable_query_args[] = 'advads_nonce';

		return $removable_query_args;
	}

	/**
	 * Manage when to run checks
	 * - only when users have ads
	 * - once per day on any backend page
	 * - on each Advanced Ads related page
	 */
	public function run_checks() {

		// run in WP Admin only and if there are any ads.
		if ( ! is_admin() || ! Advanced_Ads::get_number_of_ads() ) {
			return;
		}

		// don’t run on AJAX calls.
		if ( wp_doing_ajax() ) {
			return;
		}

		// run only daily unless we are on an Advanced Ads related page.
		if ( ! Conditional::is_screen_advanced_ads()
			 && get_transient( self::DAILY_CHECK_TRANSIENT_NAME ) ) {
			return;
		}

		$this->checks();
	}

	/**
	 * General checks done on each Advanced Ads-related page or once per day
	 */
	public function checks() {

		if ( ! Advanced_Ads_Checks::php_version_minimum() ) {
			$this->add( 'old_php' );
		} else {
			$this->remove( 'old_php' );
		}
		if ( count( Advanced_Ads_Checks::conflicting_plugins() ) ) {
			$this->add( 'conflicting_plugins' );
		} else {
			$this->remove( 'conflicting_plugins' );
		}
		if ( count( Advanced_Ads_Checks::php_extensions() ) ) {
			$this->add( 'php_extensions_missing' );
		} else {
			$this->remove( 'php_extensions_missing' );
		}
		if ( Advanced_Ads_Checks::ads_disabled() ) {
			$this->add( 'ads_disabled' );
		} else {
			$this->remove( 'ads_disabled' );
		}
		if ( Advanced_Ads_Checks::get_defined_constants() ) {
			$this->add( 'constants_enabled' );
		} else {
			$this->remove( 'constants_enabled' );
		}
		if ( Advanced_Ads_Checks::assets_expired() ) {
			$this->add( 'assets_expired' );
		} else {
			$this->remove( 'assets_expired' );
		}
		if ( Advanced_Ads_Checks::licenses_invalid() ) {
			$this->add( 'license_invalid' );
		} else {
			$this->remove( 'license_invalid' );
		}
		if ( class_exists( 'BuddyPress', false ) && ! defined( 'AAP_VERSION' ) ) {
			$this->add( 'buddypress_no_pro' );
		} else {
			$this->remove( 'buddypress_no_pro' );
		}
		if ( class_exists( 'bbPress', false ) && ! defined( 'AAP_VERSION' ) ) {
			$this->add( 'bbpress_no_pro' );
		} else {
			$this->remove( 'bbpress_no_pro' );
		}
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$this->add( 'WPML_active' );
		} else {
			$this->remove( 'WPML_active' );
		}
		if ( Advanced_Ads_Checks::active_amp_plugin() ) {
			$this->add( 'AMP_active' );
		} else {
			$this->remove( 'AMP_active' );
		}
		if ( Advanced_Ads_Checks::wp_engine_hosting() ) {
			$this->add( 'wpengine' );
		}
		if ( count( Advanced_Ads_Checks::ads_txt_plugins() ) ) {
			$this->add( 'ads_txt_plugins_enabled' );
		} else {
			$this->remove( 'ads_txt_plugins_enabled' );
		}
		if ( count( Advanced_Ads_Checks::header_footer_plugins() ) ) {
			$this->add( 'header_footer_plugins_enabled' );
		} else {
			$this->remove( 'header_footer_plugins_enabled' );
		}

		set_transient( self::DAILY_CHECK_TRANSIENT_NAME, true, DAY_IN_SECONDS );
	}

	/**
	 * Add a notice to the queue
	 *
	 * @param string $notice_key notice key to be added to the notice array.
	 * @param array  $atts additional attributes.
	 *
	 *  attributes
	 *  - append_key        string attached to the key; enables to create multiple messages for one original key
	 *  - append_text    text added to the default message
	 *  - ad_id        ID of an ad, attaches the link to the ad edit page to the message
	 */
	public function add( $notice_key, $atts = [] ) {

		// stop here if notices are disabled.
		if ( empty( $notice_key ) || ! self::notices_enabled() ) {
			return;
		}

		// add string to key.
		if ( ! empty( $atts['append_key'] ) ) {
			$orig_notice_key = $notice_key;
			$notice_key     .= $atts['append_key'];
		}

		$notice_key     = esc_attr( $notice_key );
		$options_before = $options = $this->options();

		// load notices from "queue".
		$notices = isset( $options['notices'] ) ? $options['notices'] : [];

		// check if notice_key was already saved, this prevents the same notice from showing up in different forms.
		if ( isset( $notices[ $notice_key ] ) ) {
			return;
		}

		// save the new notice key.
		$notices[ $notice_key ] = [];

		// save text, if given.
		if ( ! empty( $atts['text'] ) ) {
			$notices[ $notice_key ]['text'] = $atts['text'];
		}

		// attach link to ad, if given.
		if ( ! empty( $atts['ad_id'] ) ) {
			$id = absint( $atts['ad_id'] );
			$ad = \Advanced_Ads\Ad_Repository::get( $id );
			if ( $id && isset( $ad->title ) && '' !== $ad->title ) {
				$edit_link                             = ' <a href="' . get_edit_post_link( $id ) . '">' . $ad->title . '</a>';
				$notices[ $notice_key ]['append_text'] = isset( $notices[ $notice_key ]['append_text'] ) ? $notices[ $notice_key ]['append_text'] . $edit_link : $edit_link;
			}
		}

		// save the original key, if we manipulated it.
		if ( ! empty( $atts['append_key'] ) ) {
			$notices[ $notice_key ]['orig_key'] = $orig_notice_key;
		}

		// add more text.
		if ( ! empty( $atts['append_text'] ) ) {
			$notices[ $notice_key ]['append_text'] = esc_attr( $atts['append_text'] );
		}

		// add current time – we store localized time including the offset set in WP.
		$notices[ $notice_key ]['time'] = current_time( 'timestamp', 0 );

		$this->last_saved_notice_key = $notice_key;

		$options['notices'] = $notices;
		// update db if changed.
		if ( $options_before !== $options ) {
			$this->update_options( $options );

			// update already registered notices.
			$this->load_notices();
		}
	}

	/**
	 * Updating an existing notice or add it, if it doesn’t exist, yet
	 *
	 * @param string $notice_key notice key to be added to the notice array.
	 * @param array  $atts additional attributes.
	 *
	 *  attributes:
	 *  - append_text – text added to the default message
	 */
	public function update( $notice_key, $atts = [] ) {

		// stop here if notices are disabled.
		if ( empty( $notice_key ) || ! self::notices_enabled() ) {
			return;
		}

		// check if the notice already exists.
		$notice_key     = esc_attr( $notice_key );
		$options        = $this->options();
		$options_before = $options;

		// load notices from "queue".
		$notices = isset( $options['notices'] ) ? $options['notices'] : [];

		// check if notice_key was already saved, this prevents the same notice from showing up in different forms.
		if ( ! isset( $notices[ $notice_key ] ) ) {
			$this->add( $notice_key, $atts );

			$notice_key = $this->last_saved_notice_key;

			// just in case, get notices again.
			$notices = $this->notices;
		} else {
			// add more text if this is an update.
			if ( ! empty( $atts['append_text'] ) ) {
				$notices[ $notice_key ]['append_text'] = isset( $notices[ $notice_key ]['append_text'] ) ? $notices[ $notice_key ]['append_text'] . $atts['append_text'] : $atts['append_text'];
			}
			// add `closed` marker, if given.
			if ( ! empty( $atts['closed'] ) ) {
				$notices[ $notice_key ]['closed'] = absint( $atts['closed'] );
			}
		}

		// update db.
		$options['notices'] = $notices;

		// update db if changed.
		if ( $options_before !== $options ) {
			$this->update_options( $options );

			// update already registered notices.
			$this->load_notices();
		}

	}

	/**
	 * Decide based on the notice, whether to remove or ignore it
	 *
	 * @param string $notice_key key of the notice.
	 */
	public function hide( $notice_key ) {
		if ( empty( $notice_key ) ) {
			return;
		}

		// get original notice array for the "hide" attribute.
		$notice_array = $this->get_notice_array_for_key( $notice_key );

		// handle notices with a timeout.
		// set `closed` timestamp if the notice definition has a timeout information.
		if ( isset( $notice_array['timeout'] ) ) {
			$this->update( $notice_key, [ 'closed' => current_time( 'timestamp', 0 ) ] );

			return;
		}

		if ( isset( $notice_array['hide'] ) && false === $notice_array['hide'] ) {
			// remove item.
			self::get_instance()->remove( $notice_key );
		} else {
			// hide item.
			self::get_instance()->ignore( $notice_key );
		}

	}

	/**
	 * Remove notice
	 * Would remove it from "notice" array. The notice can be added anytime again
	 * practically, this allows users to "skip" an notice if they are sure that it was only temporary
	 *
	 * @param string $notice_key notice key to be removed.
	 */
	public function remove( $notice_key ) {

		// stop here if notices are disabled.
		if ( empty( $notice_key ) || ! self::notices_enabled() ) {
			return;
		}

		// get notices from options.
		$options        = $this->options();
		$options_before = $options;
		if ( ! isset( $options['notices'] )
			|| ! is_array( $options['notices'] )
			|| ! isset( $options['notices'][ $notice_key ] ) ) {
			return;
		}

		unset( $options['notices'][ $notice_key ] );

		// only update if changed.
		if ( $options_before !== $options ) {
			$this->update_options( $options );

			// update already registered notices.
			$this->load_notices();
		}
	}

	/**
	 * Ignore any notice
	 * adds notice key into "ignore" array
	 * does not remove it from "notices" array
	 *
	 * @param string $notice_key key of the notice to be ignored.
	 */
	public function ignore( $notice_key ) {

		// stop here if notices are disabled.
		if ( empty( $notice_key ) || ! self::notices_enabled() ) {
			return;
		}

		// get options.
		$options        = $this->options();
		$options_before = $options;
		$ignored        = isset( $options['ignore'] ) && is_array( $options['ignore'] ) ? $options['ignore'] : [];

		// adds notice key to ignore array if it doesn’t exist already.
		if ( false === array_search( $notice_key, $ignored, true ) ) {
			$ignored[] = $notice_key;
		}

		// update db.
		$options['ignore'] = $ignored;

		// only update if changed.
		if ( $options_before !== $options ) {
			$this->update_options( $options );

			// update already registered notices.
			$this->load_notices();
		}
	}

	/**
	 * Clear all "ignore" messages
	 */
	public function unignore() {

		// get options.
		$options        = $this->options();
		$options_before = $options;

		// empty ignore value.
		$options['ignore'] = [];

		// only update if changed.
		if ( $options_before !== $options ) {
			$this->update_options( $options );

			// update already registered notices.
			$this->load_notices();
		}
	}

	/**
	 * Render notice widget on overview page
	 */
	public function render_widget() {

		$ignored_count = count( $this->ignore );
		$has_problems  = $this->has_notices_by_type( 'problem' );
		$has_notices   = $this->has_notices_by_type( 'notice' );

		// only render, if there are notices.
		if ( $this->has_notices() ) {
			include ADVADS_ABSPATH . 'admin/views/overview-notices.php';
		}
	}

	/**
	 * Display notices in a list
	 *
	 * @param string $type which type of notice to show; default: 'problem'.
	 */
	public function display( $type = 'problem' ) {

		// iterate through notices.
		?>
		<ul class="advads-ad-health-notices advads-ad-health-notices-<?php echo esc_attr( $type ); ?>">
																				<?php

																				// failsafe in case this is not an array.
																				if ( ! is_array( $this->notices ) ) {
																					return;
																				}

																				foreach ( $this->notices as $_notice_key => $_notice ) {

																					$notice_array = $this->get_notice_array_for_key( $_notice_key );

																					// remove the notice if key doesn’t exist anymore.
																					if ( [] === $notice_array ) {
																						$this->remove( $_notice_key );
																					}

																					$notice_type = isset( $notice_array['type'] ) ? $notice_array['type'] : 'problem';

																					// skip if type is not correct.
																					if ( $notice_type !== $type ) {
																						continue;
																					}

																					if ( ! empty( $_notice['text'] ) ) {
																						$text = $_notice['text'];
																					} elseif ( isset( $notice_array['text'] ) ) {
																						$text = $notice_array['text'];
																					} else {
																						continue;
																					}

																					// attach "append_text".
																					if ( ! empty( $_notice['append_text'] ) ) {
																						$text .= $_notice['append_text'];
																					}

																					// attach "get help" link.
																					if ( ! empty( $_notice['get_help_link'] ) ) {
																						$text .= $this->get_help_link( $_notice['get_help_link'] );
																					} elseif ( isset( $notice_array['get_help_link'] ) ) {
																						$text .= $this->get_help_link( $notice_array['get_help_link'] );
																					}

																					$can_hide  = ( ! isset( $notice_array['can_hide'] ) || true === $notice_array['can_hide'] ) ? true : false;
																					$hide      = ( ! isset( $notice_array['hide'] ) || true === $notice_array['hide'] ) ? true : false;
																					$is_hidden = in_array( $_notice_key, $this->ignore, true ) ? true : false;
																					$date      = isset( $_notice['time'] ) ? date_i18n( get_option( 'date_format' ), $_notice['time'] ) : false;

																					include ADVADS_ABSPATH . '/admin/views/overview-notice-row.php';
																				}

																				?>
		</ul>
		<?php
	}

	/**
	 * Display problems.
	 */
	public function display_problems() {
		$this->display( 'problem' );
	}

	/**
	 * Display notices.
	 */
	public function display_notices() {
		$this->display( 'notice' );
	}

	/**
	 * Return notices option from DB
	 *
	 * @return array $options
	 */
	public function options() {
		if ( ! isset( $this->options ) ) {
			$this->options = get_option( ADVADS_SLUG . '-ad-health-notices', [] );
		}
		if ( ! is_array( $this->options ) ) {
			$this->options = [];
		}

		return $this->options;
	}

	/**
	 * Update notice options
	 *
	 * @param array $options new options.
	 */
	public function update_options( array $options ) {
		// do not allow to clear options.
		if ( [] === $options ) {
			return;
		}

		$this->options = $options;
		update_option( ADVADS_SLUG . '-ad-health-notices', $options );
	}

	/**
	 * Get the number of overall visible notices
	 */
	public static function get_number_of_notices() {
		$displayed_notices = self::get_instance()->displayed_notices;
		if ( ! is_array( $displayed_notices ) ) {
			return 0;
		}

		return count( $displayed_notices );
	}

	/**
	 * Get ignored messages that are also in the notices
	 * also updates ignored array, if needed
	 */
	public function get_valid_ignored() {

		$options        = $this->options();
		$options_before = $options;

		$ignore_before = isset( $options['ignore'] ) ? $options['ignore'] : [];

		// get keys from notices.
		$notice_keys = array_keys( $this->notices );

		// get the errors that are in ignore AND notices and reset the keys.
		$ignore            = array_values( array_intersect( $ignore_before, $notice_keys ) );
		$options['ignore'] = $ignore;

		// only update if changed.
		if ( $options_before !== $options ) {
			$this->update_options( $options );
		}

		return $ignore;
	}

	/**
	 * Check if there are visible problems (notices of type "problem")
	 *
	 * @return bool true if there are visible notices (notices that are not hidden)
	 */
	public static function has_visible_problems() {
		$displayed_notices = self::get_instance()->displayed_notices;
		if ( ! is_array( $displayed_notices ) ) {
			return false;
		}

		return 0 < count( $displayed_notices );
	}

	/**
	 * Get visible notices by type – hidden and displayed
	 *
	 * @param string $type type of the notice.
	 *
	 * @return  array
	 */
	public function get_visible_notices_by_type( $type = 'problem' ) {

		// get all notices with a given type.
		$notices_by_type = [];

		foreach ( $this->notices as $_key => $_notice ) {
			$notice_array = $this->get_notice_array_for_key( $_key );

			if ( isset( $notice_array['type'] ) && $type === $notice_array['type']
				 && ( ! isset( $this->ignore ) || false === array_search( $_key, $this->ignore, true ) ) ) {
				$notices_by_type[ $_key ] = $_notice;
			}
		}

		return $notices_by_type;
	}

	/**
	 * Check if there are notices
	 *
	 * @return  bool    true if there are notices, false if not
	 */
	public function has_notices() {

		// get all notices.
		return isset( $this->notices ) && is_array( $this->notices ) && count( $this->notices );

	}

	/**
	 * Check if there are visible notices for a given type
	 *
	 * @param string $type type of the notice.
	 *
	 * @return  integer
	 */
	public function has_notices_by_type( $type = 'problem' ) {

		// get all notices with a given type.
		$notices = $this->get_visible_notices_by_type( $type );

		if ( ! is_array( $notices ) ) {
			return 0;
		}

		return count( $notices );
	}

	/**
	 * Get the notice array for a notice key
	 * useful, if a notice key was manipulated
	 *
	 * @param string $notice_key key of the notice.
	 *
	 * @return  array    type
	 */
	public function get_notice_array_for_key( $notice_key ) {

		// check if there is an original key.
		$orig_key = isset( $this->notices[ $notice_key ]['orig_key'] ) ? $this->notices[ $notice_key ]['orig_key'] : $notice_key;

		return isset( $this->default_notices[ $orig_key ] ) ? $this->default_notices[ $orig_key ] : [];
	}

	/**
	 * Add notification when an ad expires based on the expiry date
	 *
	 * @param integer $ad_id ID of the ad.
	 * @param object  $ad ad object.
	 */
	public function ad_expired( $ad_id, $ad ) {
		$id = ! empty( $ad_id ) ? absint( $ad_id ) : 0;
		$this->update(
			'ad_expired',
			[
				'append_key' => $id,
				'ad_id'      => $id,
			]
		);
	}

	/**
	 * Get AdSense error link
	 * this is a copy of Advanced_Ads_AdSense_MAPI::get_adsense_error_link() which might not be available all the time
	 *
	 * @param string $code error code.
	 *
	 * @return string link
	 */
	public static function get_adsense_error_link( $code ) {
		if ( ! empty( $code ) ) {
			$code = '-' . $code;
		}

		if ( class_exists( 'Advanced_Ads_AdSense_MAPI', false ) ) {
			return Advanced_Ads_AdSense_MAPI::get_adsense_error_link( 'disapprovedAccount' );
		}

		// is a copy of Advanced_Ads_AdSense_MAPI::get_adsense_error_link().
		return sprintf(
		// translators: %1$s is an anchor (link) opening tag, %2$s is the closing tag.
			esc_attr__( 'Learn more about AdSense account issues %1$shere%2$s.', 'advanced-ads' ),
			'<a href="https://wpadvancedads.com/adsense-errors/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adsense-error' . $code . '" target="_blank">',
			'</a>'
		);
	}

	/**
	 * Return a "Get Help" link
	 *
	 * @param string $link target URL.
	 *
	 * @return  string  HTML of the target link
	 */
	public function get_help_link( $link ) {

		$link = esc_url( $link );

		if ( ! $link ) {
			return '';
		}

		return '&nbsp;<a href="' . $link . '" target="_blank">' . __( 'Get help', 'advanced.ads' ) . '</a>';
	}


}
