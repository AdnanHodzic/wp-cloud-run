<?php
/**
 * PEF module
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

namespace AdvancedAds\Modules\ProductExperimentationFramework;

/**
 * Module main class
 */
class Module {
	/**
	 * Current running features
	 *
	 * @var array[]
	 */
	const FEATURES = [
		'labs-video-ads'             => [
			'name'   => 'Video Ads',
			'weight' => 1,
			'text'   => 'The Advanced Ads team is developing a new feature to support video ads. This enables embedding videos from your WordPress media library and video ad networks. Like image ads, they would be fully compatible with all conditions and placements. We are curious whether our users want us to prioritize this feature.',
		],
		'labs-email-notifications'   => [
			'name'   => 'Email Notifications',
			'weight' => 1,
			'text'   => 'The Advanced Ads team is developing a new feature to improve email notifications. Imagine receiving timely reminders in your inbox, giving you ample time to take action. Whether renewing an ad, adjusting ad groups, or checking the statistics after a campaign has ended, you’ll be in the know every step of the way. We are curious whether our users want us to prioritize this feature.',
		],
		'labs-image-mapping'         => [
			'name'   => 'Image Mapping',
			'weight' => 1,
			'text'   => 'The Advanced Ads team is developing a new feature to allow serving multiple images with the same ad unit. This workflow can save you time in certain setups and is an alternative to ad groups when it comes to multiple campaigns from the same advertiser. We are curious whether our users want us to prioritize this feature.',
		],
		'labs-automated-split-tests' => [
			'name'   => 'Automated Split Tests',
			'weight' => 1,
			'text'   => 'The Advanced Ads team is working on a new feature to simplify your workload: automated split testing. Let us handle the heavy lifting for you by automatically maximizing your ad performance based on the CTR of your ad units. We are curious whether our users want us to prioritize this feature.',
		],
		'labs-ad-preview'            => [
			'name'   => 'Ad Preview',
			'weight' => 1,
			'text'   => 'The Advanced Ads team is developing a new feature: ad previews. Simply input the code in the Parameter box, and watch your ad come to life on the Edit Ad page. We are curious whether our users want us to prioritize this feature.',
		],
		'labs-animated-ads'          => [
			'name'   => 'Animated Ads',
			'weight' => 1,
			'text'   => 'The Advanced Ads team is developing a new feature to allow animating ads. Captivate your audience with eye-catching effects like flip, slider, and fade. Enhance engagement and leave a lasting impression with dynamic ad displays. We are curious whether our users want us to prioritize this feature.',
		],
		'labs-active-view-tracking'  => [
			'name'   => 'Active View Tracking',
			'weight' => 1,
			'text'   => 'The Advanced Ads team is developing a new feature to enable active view tracking. Gain precise insights into ad viewability and upgrade your metrics. With active view tracking, you’ll know if your ad is loaded and visible in the viewport. We are curious whether our users want us to prioritize this feature.',
		],
		'labs-ad-schedule-wizard'    => [
			'name'   => 'Ad Schedule Wizard',
			'weight' => 1,
			'text'   => 'The Advanced Ads team is developing a better ad scheduling and planning interface. Simplify your workflow and maximize efficiency. With an improved user experience, managing your ads could be easier than ever. We are curious whether our users want us to prioritize this feature.',
		],
		'labs-reports-insights'      => [
			'name'   => 'Reports & Insights',
			'weight' => 1,
			'text'   => 'The Advanced Ads team is improving the reporting. Customize your email reports to focus on the metrics that most matter to you and easily share them as PDFs. Empower yourself with actionable data and streamline communication with stakeholders. We are curious whether our users want us to prioritize this feature.',
		],
	];

	/**
	 * User meta key where the dismiss flag is stored.
	 *
	 * @var string
	 */
	const USER_META = 'advanced_ads_pef_dismiss';

	/**
	 * The singleton
	 *
	 * @var Module
	 */
	private static $instance;

	/**
	 * Sum of all weights
	 *
	 * @var int
	 */
	private $weight_sum = 0;

	/**
	 * ID => weight association
	 *
	 * @var int[]
	 */
	private $weights = [];

	/**
	 * Whether the PEF can be displayed based on user meta
	 *
	 * @var bool
	 */
	private $can_display = true;

	/**
	 * Private constructor
	 */
	private function __construct() {
		// Wait for `admin_init` to get the current user.
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Initialization
	 *
	 * @return void
	 */
	public function admin_init() {
		$meta = get_user_meta( get_current_user_id(), self::USER_META, true );
		if ( $this->get_minor_version( ADVADS_VERSION ) === $this->get_minor_version( $meta ) ) {
			$this->can_display = false;

			return;
		}
		$this->collect_weights();
		add_action( 'wp_ajax_advanced_ads_pef', [ $this, 'dismiss' ] );
	}

	/**
	 * Ajax action to hie PEF for the current user until next plugin update
	 *
	 * @return void
	 */
	public function dismiss() {
		if ( ! check_ajax_referer( 'advanced_ads_pef' ) ) {
			wp_send_json_error( 'Unauthorized', 401 );
		}
		update_user_meta( get_current_user_id(), self::USER_META, ADVADS_VERSION );
		wp_send_json_success( 'OK', 200 );
	}

	/**
	 * Collect feature ID with their weight as recorded in the class constant. Also calculate the weight sum
	 */
	private function collect_weights() {
		if ( 0 !== $this->weight_sum ) {
			return;
		}
		foreach ( self::FEATURES as $id => $feature ) {
			$this->weights[ $id ] = (int) $feature['weight'];
			$this->weight_sum    += $this->weights[ $id ];
		}
	}

	/**
	 * Get a random feature based on weights and a random number
	 *
	 * @return array
	 */
	public function get_winner_feature() {
		$random_weight  = mt_rand( 1, $this->weight_sum );
		$current_weight = 0;
		foreach ( self::FEATURES as $id => $feature ) {
			$current_weight += $this->weights[ $id ];
			if ( $random_weight <= $current_weight ) {
				return array_merge(
					[
						'id'     => $id,
						'weight' => $this->weights[ $id ],
					],
					self::FEATURES[ $id ]
				);
			}
		}
	}

	/**
	 * Render PEF
	 *
	 * @param string $screen the screen on which PEF is displayed, used in the utm_campaign parameter.
	 *
	 * @return void
	 */
	public function render( $screen ) {
		if ( ! $this->can_display ) {
			return;
		}
		$winner = $this->get_winner_feature();
		require_once DIR . '/views/template.php';
	}

	/**
	 * Get minor part of a version
	 *
	 * @param string $version version to get the minor part from.
	 *
	 * @return string
	 */
	public function get_minor_version( $version ) {
		return explode( '.', $version )[1] ?? '0';
	}

	/**
	 * Build the link for the winner feature with all its utm parameters
	 *
	 * @param array  $winner the winner feature.
	 * @param string $screen the screen on which it was displayed.
	 *
	 * @return string
	 */
	public function build_link( $winner, $screen ) {
		$link  = "https://wpadvancedads.com/advanced-ads-labs/?utm_source=advanced-ads&utm_medium=link&utm_campaign=$screen-aa-labs&utm_term=b";
		$link .= str_replace( '.', '-', ADVADS_VERSION ) . "w{$winner['weight']}-{$this->weight_sum}&utm_content={$winner['id']}";

		return $link;
	}

	/**
	 * Return the singleton. Create it if needed
	 *
	 * @return Module
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
