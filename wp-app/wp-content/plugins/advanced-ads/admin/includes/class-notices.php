<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Container class for admin notices
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.x.x
 */

use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Arr;

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
	 * Advanced_Ads_Admin_Notices constructor to load notices
	 */
	public function __construct() {
		$this->load_notices();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		static $instance;

		// if the single instance hasn't been set, set it now.
		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Determines if a notice can be displayed.
	 *
	 * @param string $notice The notice identifier.
	 *
	 * @return bool Returns true if the notice can be displayed, false otherwise.
	 */
	public function can_display( $notice ) {
		$options = $this->options();
		$closed  = $options['closed'] ?? [];

		return ! array_key_exists( $notice, $closed );
	}

	/**
	 * Load admin notices
	 */
	public function load_notices() {
		$options        = $this->options();
		$plugin_options = Advanced_Ads::get_instance()->options();

		// load notices from queue.
		$this->notices  = isset( $options['queue'] ) ? $options['queue'] : [];
		$notices_before = $this->notices;

		// check license notices.
		$this->register_license_notices();

		// check wizard notice.
		$this->register_wizard_notice();

		// check non org plugins update.
		$this->check_non_org_plugins();

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
	 * Check various notices conditions
	 */
	public function check_notices() {
		$internal_options = Advanced_Ads::get_instance()->internal_options();
		$now              = time();
		$activation       = ( isset( $internal_options['installed'] ) ) ? $internal_options['installed'] : $now; // activation time.

		$options = $this->options();
		$closed  = isset( $options['closed'] ) ? $options['closed'] : [];
		$queue   = isset( $options['queue'] ) ? $options['queue'] : [];
		$paused  = isset( $options['paused'] ) ? $options['paused'] : [];

		// offer free add-ons if not yet subscribed.
		if ( Conditional::user_can_subscribe( 'nl_free_addons' ) && ! in_array( 'nl_free_addons', $queue, true ) && ! isset( $closed['nl_free_addons'] ) ) {
			// get number of ads.
			if ( WordPress::get_count_ads() ) {
				$this->notices[] = 'nl_free_addons';
			}
		}
		$number_of_ads = 0;
		// needed error handling due to a weird bug in the piklist plugin.
		try {
			$number_of_ads = WordPress::get_count_ads();
		} catch ( Exception $e ) { // phpcs:ignore
			// no need to catch anything since we just use TRY/CATCH to prevent an issue caused by another plugin.
		}

		// ask for a review after 2 days and when 3 ads were created and when not paused.
		if (
			! in_array( 'review', $queue, true )
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
	 * Register wizard notice.
	 */
	public function register_wizard_notice() {
		if ( ! Conditional::is_screen_advanced_ads() ) {
			return;
		}

		$options = $this->options();
		$queue   = isset( $options['queue'] ) ? $options['queue'] : [];

		if ( Advanced_Ads_Checks::can_launch_wizard() ) {
			if ( ! in_array( 'monetize_wizard', $queue, true ) ) {
				$this->notices[] = 'monetize_wizard';
			}
		} else {
			$this->remove_from_queue( 'monetize_wizard' );
		}
	}

	/**
	 * Check for updates of non wp.org plugins
	 */
	public function check_non_org_plugins() {
		if ( ! Conditional::is_screen_advanced_ads() ) {
			return;
		}

		$addons  = \AdvancedAds\Constants::ADDONS_NON_COMPATIBLE_VERSIONS;
		$plugins = WordPress::get_wp_plugins();
		$options = $this->options();
		$queue   = isset( $options['queue'] ) ? $options['queue'] : [];
		$closed  = isset( $options['closed'] ) ? $options['closed'] : [];

		foreach ( $addons as $version => $slug ) {
			$addon = $plugins[ $slug ] ?? null;
			if ( ! $addon ) {
				continue;
			}

			$notice = $slug . '_upgrade';

			if ( version_compare( $addon['version'], $version, '<=' ) ) {
				if ( ! in_array( $notice, $queue, true ) && ! array_key_exists( $notice, $closed ) ) {
					$this->notices[] = $notice;
				}
			} else {
				$this->remove_from_queue( $notice );
			}
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
		if ( wp_doing_ajax() ) {
			return;
		}

		// register Black Friday 2023 deals.
		if ( time() > 1700654400 &&
			time() <= 1701172800 && Conditional::is_screen_advanced_ads() ) {
			$options = $this->options();
			$closed  = isset( $options['closed'] ) ? $options['closed'] : [];

			if ( ! isset( $closed['bfcm23'] ) ) {
				$this->notices[] = 'bfcm23';
			}
		}

		if ( [] === $this->notices ) {
			return;
		}

		include_once ADVADS_ABSPATH . '/admin/includes/notices.php';

		// Iterate through notices.
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
			if (
				( ! isset( $advanced_ads_admin_notices[ $_notice ]['global'] ) || ! $advanced_ads_admin_notices[ $_notice ]['global'] )
				&& ! Conditional::is_screen_advanced_ads()
			) {
				continue;
			}

			// don't display license nag if ADVANCED_ADS_SUPPRESS_PLUGIN_ERROR_NOTICES is defined.
			if ( defined( 'ADVANCED_ADS_SUPPRESS_PLUGIN_ERROR_NOTICES' ) && 'plugin_error' === $advanced_ads_admin_notices[ $_notice ]['type'] ) {
				continue;
			}

			$hash = [
				'info'         => '/admin/views/notices/info.php',
				'subscribe'    => '/admin/views/notices/subscribe.php',
				'plugin_error' => '/admin/views/notices/plugin_error.php',
				'promo'        => '/admin/views/notices/promo.php',
			];

			$locate_tempalte = isset( $hash[ $type ] ) ? $hash[ $type ] : '/admin/views/notices/error.php';
			include ADVADS_ABSPATH . $locate_tempalte;

			// phpcs:disable
			// if ( self::MAX_NOTICES === ++$count ) {
			// 	break;
			// }
			// phpcs:enable
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

		$user = wp_get_current_user();

		if ( '' === $user->user_email ) {
			/* translators: %s: is a URL. */
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
		}

		// Mark as subscribed and move notice from queue.
		$this->mark_as_subscribed( $notice );
		$this->remove_from_queue( $notice );

		/* translators: the first %s is an email address, the seconds %s is a URL. */
		return sprintf( __( 'Please check your email (%1$s) for the confirmation message. If you didn’t receive one or want to use another email address then please use <a href="%2$s" target="_blank">this form</a> to sign up.', 'advanced-ads' ), $user->user_email, 'http://eepurl.com/bk4z4P' );
	}

	/**
	 * Update information that the current user is subscribed
	 *
	 * @param string $notice notice slug.
	 */
	private function mark_as_subscribed( $notice ) {
		// Early bail!!
		if ( empty( $notice ) || ! Conditional::user_can_subscribe( $notice ) ) {
			return;
		}

		$user_id            = get_current_user_id();
		$subscribed_notices = get_user_meta( $user_id, 'advanced-ads-subscribed', true );

		// backward compatibility.
		if ( ! is_array( $subscribed_notices ) ) {
			$subscribed_notices = [];
		}

		$subscribed_notices[ $notice ] = true;

		update_user_meta( $user_id, 'advanced-ads-subscribed', $subscribed_notices );
	}

	/**
	 * Check if a usesr can be subscribed to our newsletter
	 * check if is already subscribed or email is invalid
	 *
	 * @deprecated version 2.0 use Conditional::user_can_subscribe() instead
	 *
	 * @return bool true if user can subscribe
	 */
	public function user_can_subscribe() {
		_deprecated_function( __METHOD__, '2.0', '\AdvancedAds\Utilities\Conditional::user_can_subscribe()' );

		return Conditional::user_can_subscribe( 'nl_first_steps' );
	}

	/**
	 * Add AdSense tutorial notice
	 *
	 * @param Ad $ad ad object.
	 */
	public function adsense_tutorial( $ad ) {
		$options = $this->options();
		$_notice = 'nl_adsense';

		if ( 'adsense' !== $ad->get_type() || isset( $options['closed'][ $_notice ] ) ) {
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
}
