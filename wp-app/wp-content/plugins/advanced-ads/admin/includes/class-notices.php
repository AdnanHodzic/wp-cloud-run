<?php
// phpcs:ignoreFile
use AdvancedAds\Utilities\Conditional;

/**
 * Container class for admin notices
 *
 * @package WordPress
 * @subpackage Advanced Ads Plugin
 */
class Advanced_Ads_Admin_Notices {

	/**
	 * Maximum number of notices to show at once
	 */
	const MAX_NOTICES = 2;

	/**
	 * Instance of this class
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
	 * Notices to be displayed
	 *
	 * @var    array
	 */
	public $notices = [];

	/**
	 * Plugin class
	 *
	 * @var Advanced_Ads_Plugin
	 */
	private $plugin;

	/**
	 * Advanced_Ads_Admin_Notices constructor to load notices
	 */
	public function __construct() {
		$this->plugin = Advanced_Ads_Plugin::get_instance();
		// load notices.
		$this->load_notices();

		add_action( 'advanced-ads-ad-params-before', [ $this, 'adsense_tutorial' ], 10, 2 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// if the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load admin notices
	 */
	public function load_notices() {

		$options        = $this->options();
		$plugin_options = $this->plugin->options();

		// load notices from queue.
		$this->notices  = isset( $options['queue'] ) ? $options['queue'] : [];
		$notices_before = $this->notices;

		// check license notices.
		$this->register_license_notices();

		// don’t check non-critical notices if they are disabled.
		if ( ! isset( $plugin_options['disable-notices'] ) ) {
			// check other notices.
			$this->check_notices();
		}

		// register notices in db so they get displayed until closed for good.
		if ( $this->notices !== $notices_before ) {
			$this->add_to_queue( $this->notices );
		}
	}

	/**
	 * Update version number to latest one
	 */
	public function update_version_number() {

		$internal_options = $this->plugin->internal_options();
		$new_options      = $internal_options; // in case we udpate options here.

		$new_options['version'] = ADVADS_VERSION;

		// update version numbers.
		if ( $internal_options !== $new_options ) {
			$this->plugin->update_internal_options( $new_options );
		}
	}

	/**
	 * Check various notices conditions
	 */
	public function check_notices() {
		$internal_options = $this->plugin->internal_options();
		$now              = time();
		$activation       = ( isset( $internal_options['installed'] ) ) ? $internal_options['installed'] : $now; // activation time.

		$options = $this->options();
		$closed  = isset( $options['closed'] ) ? $options['closed'] : [];
		$queue   = isset( $options['queue'] ) ? $options['queue'] : [];
		$paused  = isset( $options['paused'] ) ? $options['paused'] : [];

		// offer free add-ons if not yet subscribed.
		if ( $this->user_can_subscribe() && ! in_array( 'nl_free_addons', $queue, true ) && ! isset( $closed['nl_free_addons'] ) ) {
			// get number of ads.
			if ( Advanced_Ads::get_number_of_ads() ) {
				$this->notices[] = 'nl_free_addons';
			}
		}
		$number_of_ads = 0;
		// needed error handling due to a weird bug in the piklist plugin.
		try {
			$number_of_ads = Advanced_Ads::get_number_of_ads();
		} catch ( Exception $e ) {
			// no need to catch anything since we just use TRY/CATCH to prevent an issue caused by another plugin.
		}

		// register intro message.
		if ( ! $number_of_ads
			&& [] === $options && ! in_array( 'nl_intro', $queue, true ) && ! isset( $closed['nl_intro'] ) ) {
			$this->notices[] = 'nl_intro';
		} elseif ( $number_of_ads ) {
			$key = array_search( 'nl_intro', $this->notices, true );
			if ( false !== $key ) {
				unset( $this->notices[ $key ] );
			}
		}

		// ask for a review after 2 days and when 3 ads were created and when not paused.
		if ( ! in_array( 'review', $queue, true )
			 && ! isset( $closed['review'] )
			 && ( ! isset( $paused['review'] ) || $paused['review'] <= time() )
			 && 172800 < ( time() - $activation )
			 && 3 <= $number_of_ads
		) {
			$this->notices[] = 'review';
		} elseif ( in_array( 'review', $queue, true ) && 3 > $number_of_ads ) {
			$review_key = array_search( 'review', $this->notices, true );
			if ( false !== $review_key ) {
				unset( $this->notices[ $review_key ] );
			}
		}
	}

	/**
	 * Register license key notices
	 */
	public function register_license_notices() {

		if ( ! Conditional::is_screen_advanced_ads() ) {
			return;
		}

		$options = $this->options();
		$queue   = isset( $options['queue'] ) ? $options['queue'] : [];
		// check license keys.

		if ( Advanced_Ads_Checks::licenses_invalid() ) {
			if ( ! in_array( 'license_invalid', $queue, true ) ) {
				$this->notices[] = 'license_invalid';
			}
		} else {
			$this->remove_from_queue( 'license_invalid' );
		}
	}

	/**
	 * Add update notices to the queue of all notices that still needs to be closed
	 *
	 * @param mixed $notices one or more notices to be added to the queue.
	 *
	 * @since 1.5.3
	 */
	public function add_to_queue( $notices = 0 ) {
		if ( ! $notices ) {
			return;
		}

		// get queue from options.
		$options = $this->options();
		$queue   = isset( $options['queue'] ) ? $options['queue'] : [];

		if ( is_array( $notices ) ) {
			$queue = array_merge( $queue, $notices );
		} else {
			$queue[] = $notices;
		}

		// remove possible duplicated.
		$queue = array_unique( $queue );

		// update db.
		$options['queue'] = $queue;
		$this->update_options( $options );
	}

	/**
	 * Remove update notice from queue
	 *  move notice into "closed"
	 *
	 * @param string $notice notice to be removed from the queue.
	 *
	 * @since 1.5.3
	 */
	public function remove_from_queue( $notice ) {
		if ( ! isset( $notice ) ) {
			return;
		}

		// get queue from options.
		$options        = $this->options();
		$options_before = $options;
		if ( ! isset( $options['queue'] ) ) {
			return;
		}
		$queue  = (array) $options['queue'];
		$closed = isset( $options['closed'] ) ? $options['closed'] : [];
		$paused = isset( $options['paused'] ) ? $options['paused'] : [];

		$key = array_search( $notice, $queue, true );
		if ( false !== $key ) {
			unset( $queue[ $key ] );
			// close message with timestamp.
		}
		// don’t close again twice.
		if ( ! isset( $closed[ $notice ] ) ) {
			$closed[ $notice ] = time();
		}
		// remove from pause.
		if ( isset( $paused[ $notice ] ) ) {
			unset( $paused[ $notice ] );
		}

		// update db.
		$options['queue']  = $queue;
		$options['closed'] = $closed;
		$options['paused'] = $paused;

		// only update if changed.
		if ( $options_before !== $options ) {
			$this->update_options( $options );
			// update already registered notices.
			$this->load_notices();
		}
	}

	/**
	 *  Hide any notice for a given time
	 *  move notice into "paused" with notice as key and timestamp as value
	 *
	 * @param string $notice notice to be paused.
	 */
	public function hide_notice( $notice ) {
		if ( ! isset( $notice ) ) {
			return;
		}

		// get queue from options.
		$options        = $this->options();
		$options_before = $options;
		if ( ! isset( $options['queue'] ) ) {
			return;
		}
		$queue  = (array) $options['queue'];
		$paused = isset( $options['paused'] ) ? $options['paused'] : [];

		$key = array_search( $notice, $queue, true );
		if ( false !== $key ) {
			unset( $queue[ $key ] );
		}
		// close message with timestamp in 7 days
		// don’t close again twice.
		if ( ! isset( $paused[ $notice ] ) ) {
			$paused[ $notice ] = time() + WEEK_IN_SECONDS;
		}

		// update db.
		$options['queue']  = $queue;
		$options['paused'] = $paused;

		// only update if changed.
		if ( $options_before !== $options ) {
			$this->update_options( $options );
			// update already registered notices.
			$this->load_notices();
		}
	}

	/**
	 * Display notices
	 */
	public function display_notices() {

		if ( defined( 'DOING_AJAX' ) ) {
			return;
		}

		// register Black Friday 2023 deals.
		if ( time() > 1732669200 &&
			time() <= 1733266800 && Conditional::is_screen_advanced_ads() ) {
			$options = $this->options();
			$closed  = isset( $options['closed'] ) ? $options['closed'] : [];

			if ( ! isset( $closed['bfcm23'] ) ) {
				$this->notices[] = 'bfcm23';
			}
		}

		// 2024 AA 10 year anniversary
		if ( time() > 1719464400 &&
			time() <= 1720047600 && Conditional::is_screen_advanced_ads() ) {
			$options = $this->options();
			$closed  = isset( $options['closed'] ) ? $options['closed'] : [];

			if ( ! isset( $closed['promo-10ya'] ) ) {
				$this->notices[] = 'promo-10ya';
			}
		}

		if ( [] === $this->notices ) {
			return;
		}

		// hide the welcome panel on the ad edit page
		$screen = get_current_screen();
		if ( isset( $screen->id ) && $screen->id === 'advanced_ads' ) {
			$intro_key = array_search( 'nl_intro', $this->notices, true );
			if ( $intro_key !== false ) {
				unset( $this->notices[ $intro_key ] );
			}
		}

		// load notices.
		include ADVADS_ABSPATH . '/admin/includes/notices.php';

		// iterate through notices.
		$count = 0;
		foreach ( $this->notices as $_notice ) {

			if ( isset( $advanced_ads_admin_notices[ $_notice ] ) ) {
				$notice = $advanced_ads_admin_notices[ $_notice ];
				$text   = $advanced_ads_admin_notices[ $_notice ]['text'];
				$type   = isset( $advanced_ads_admin_notices[ $_notice ]['type'] ) ? $advanced_ads_admin_notices[ $_notice ]['type'] : '';
			} else {
				continue;
			}

			// don’t display non-global notices on other than plugin related pages.
			if ( ( ! isset( $advanced_ads_admin_notices[ $_notice ]['global'] ) || ! $advanced_ads_admin_notices[ $_notice ]['global'] )
				 && ! Conditional::is_screen_advanced_ads() ) {
				continue;
			}

			// don't display license nag if ADVANCED_ADS_SUPPRESS_PLUGIN_ERROR_NOTICES is defined.
			if ( defined( 'ADVANCED_ADS_SUPPRESS_PLUGIN_ERROR_NOTICES' ) && 'plugin_error' === $advanced_ads_admin_notices[ $_notice ]['type'] ) {
				continue;
			}

			switch ( $type ) {
				case 'info':
					include ADVADS_ABSPATH . '/admin/views/notices/info.php';
					break;
				case 'subscribe':
					include ADVADS_ABSPATH . '/admin/views/notices/subscribe.php';
					break;
				case 'plugin_error':
					include ADVADS_ABSPATH . '/admin/views/notices/plugin_error.php';
					break;
				case 'promo':
					include ADVADS_ABSPATH . '/admin/views/notices/promo.php';
					break;
				default:
					include ADVADS_ABSPATH . '/admin/views/notices/error.php';
			}

			if ( self::MAX_NOTICES === ++ $count ) {
				break;
			}
		}
	}

	/**
	 * Return notices options
	 *
	 * @return array $options
	 */
	public function options() {
		if ( ! isset( $this->options ) ) {
			$this->options = get_option( ADVADS_SLUG . '-notices', [] );
		}

		return $this->options;
	}

	/**
	 * Update notices options
	 *
	 * @param array $options new options.
	 */
	public function update_options( array $options ) {
		// do not allow to clear options.
		if ( [] === $options ) {
			return;
		}

		$this->options = $options;
		update_option( ADVADS_SLUG . '-notices', $options );
	}

	/**
	 * Subscribe to newsletter and autoresponder
	 *
	 * @param string $notice slug of the subscription notice to send the correct reply.
	 *
	 * @return string
	 */
	public function subscribe( $notice ) {
		if ( ! isset( $notice ) ) {
			return '';
		}

		global $current_user;
		$user = wp_get_current_user();

		if ( '' === $user->user_email ) {
			// translators: %s is a URL.
			return sprintf( __( 'You don’t seem to have an email address. Please use <a href="%s" target="_blank">this form</a> to sign up.', 'advanced-ads' ), 'http://eepurl.com/bk4z4P' );
		}

		$data = [
			'email'  => $user->user_email,
			'notice' => $notice,
		];

		$result = wp_remote_post(
			'https://wpadvancedads.com/remote/subscribe.php?source=plugin',
			[
				'method'      => 'POST',
				'timeout'     => 20,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'body'        => $data,
			]
		);

		if ( is_wp_error( $result ) ) {
			return __( 'How embarrassing. The email server seems to be down. Please try again later.', 'advanced-ads' );
		} else {
			// mark as subscribed and move notice from quere.
			$this->mark_as_subscribed();
			$this->remove_from_queue( $notice );

			// translators: the first %s is an email address, the seconds %s is a URL.
			return sprintf( __( 'Please check your email (%1$s) for the confirmation message. If you didn’t receive one or want to use another email address then please use <a href="%2$s" target="_blank">this form</a> to sign up.', 'advanced-ads' ), $user->user_email, 'http://eepurl.com/bk4z4P' );
		}
	}

	/**
	 * Check if blog is subscribed to the newsletter
	 */
	public function is_subscribed() {

		// respect previous settings.
		$options = $this->options();
		if ( isset( $options['is_subscribed'] ) ) {
			return true;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return true;
		}

		$subscribed = get_user_meta( $user_id, 'advanced-ads-subscribed', true );

		return $subscribed;
	}

	/**
	 * Check if a usesr can be subscribed to our newsletter
	 * check if is already subscribed or email is invalid
	 *
	 * @return bool true if user can subscribe
	 */
	public function user_can_subscribe() {

		// respect previous settings.
		$options = $this->options();
		if ( isset( $options['is_subscribed'] ) ) {
			return true;
		}

		$current_user = wp_get_current_user();

		if ( empty( $current_user->ID ) || empty( $current_user->user_email ) ) {
			return false;
		}

		$subscribed = get_user_meta( $current_user->ID, 'advanced-ads-subscribed', true );

		// secureserver.net email address belong to GoDaddy (?) and have very, very low open rates. Seems like only temporary setup.
		return ( ! $subscribed && is_email( $current_user->user_email ) && false === strpos( $current_user->user_email, 'secureserver.net' ) )
			? true : false;

	}

	/**
	 * Update information that the current user is subscribed
	 */
	private function mark_as_subscribed() {

		$user_id = get_current_user_id();

		if ( ! $this->is_subscribed() ) {
			update_user_meta( $user_id, 'advanced-ads-subscribed', true );
		}
	}

	/**
	 * Add AdSense tutorial notice
	 *
	 * @param Advanced_Ads_Ad $ad ad object.
	 * @param array           $types ad types.
	 */
	public function adsense_tutorial( $ad, $types = [] ) {

		$options = $this->options();
		$_notice = 'nl_adsense';

		if ( 'adsense' !== $ad->type || isset( $options['closed'][ $_notice ] ) ) {
			return;
		}

		include ADVADS_ABSPATH . '/admin/includes/notices.php';

		if ( ! isset( $advanced_ads_admin_notices[ $_notice ] ) ) {
			return;
		}

		$notice = $advanced_ads_admin_notices[ $_notice ];
		$text   = $notice['text'];
		include ADVADS_ABSPATH . '/admin/views/notices/inline.php';
	}

	/**
	 * Create the content of a welcome panel like WordPress core does
	 */
	public function get_welcome_panel() {

		ob_start();
		include ADVADS_ABSPATH . '/admin/views/notices/welcome-panel.php';

		return ob_get_clean();

	}
}
