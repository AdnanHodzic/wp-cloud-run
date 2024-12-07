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
	 * The singleton
	 *
	 * @var Module
	 */
	private static $instance;

	/**
	 * User meta key where the dismiss flag is stored.
	 *
	 * @var string
	 */
	const USER_META = 'advanced_ads_pef_dismiss';

	/**
	 * Current running features
	 *
	 * @var array[]
	 */
	private $features;

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
	 * Singleton design
	 */
	private function __construct() {
		$this->set_features();

		// Wait for `admin_init` to get the current user.
		add_action( 'admin_init', [ $this, 'admin_init' ] );
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
	 * Get a random feature based on weights and a random number
	 *
	 * @return array
	 */
	public function get_winner_feature() {
		$random_weight  = wp_rand( 1, $this->weight_sum );
		$current_weight = 0;
		foreach ( $this->features as $id => $feature ) {
			$current_weight += $this->weights[ $id ];
			if ( $random_weight <= $current_weight ) {
				return array_merge(
					[
						'id'     => $id,
						'weight' => $this->weights[ $id ],
					],
					$this->features[ $id ]
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
		// Early bail!!
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
		$utm_source   = 'advanced-ads';
		$utm_medium   = 'link';
		$utm_campaign = sprintf( '%s-aa-labs', $screen );
		$utm_term     = sprintf(
			'b%sw%d-%d',
			str_replace( '.', '-', ADVADS_VERSION ),
			$winner['weight'],
			$this->weight_sum
		);
		$utm_content  = $winner['id'];

		return sprintf(
			'https://wpadvancedads.com/advanced-ads-labs/?utm_source=%s&utm_medium=%s&utm_campaign=%s&utm_term=%s&utm_content=%s',
			$utm_source,
			$utm_medium,
			$utm_campaign,
			$utm_term,
			$utm_content
		);
	}

	/**
	 * Set the features/banners
	 *
	 * @return void
	 */
	private function set_features() {
		$this->features = [
			'labs-campaign-manager-ay' => [
				'subheading' => __( 'FROM THE ADVANCED ADS LABS:', 'advanced-ads' ),
				'heading'    => __( 'The Campaign Manager', 'advanced-ads' ),
				'weight'     => 1,
				'text'       => __( 'Advanced Ads’ upcoming new product, the Campaign Manager, will shake up how you sell ad space to clients directly. It bundles a decade of users’ requests and ideas into one standalone product. The core feature set includes a brilliant advertisement schedule screen, grouping ads and reports by client, brushed-up email notifications for timed ads, and more.<br><br>Our team is in the early stages of development, and we would like to see if this product resonates with you.', 'advanced-ads' ),
				'cta'        => __( 'Are you interested in this product concept?', 'advanced-ads' ),
				'cta_button' => __( 'Yes, I want to know more!', 'advanced-ads' ),
			],
			'labs-campaign-manager-be' => [
				'subheading' => __( 'FROM THE ADVANCED ADS LABS:', 'advanced-ads' ),
				'heading'    => __( 'The Campaign Manager', 'advanced-ads' ),
				'weight'     => 1,
				'text'       => __( 'Advanced Ads’ upcoming new product, the Campaign Manager, will shake up how you sell ad space to clients directly. It bundles a decade of users’ requests and ideas into one standalone product. The core feature set includes a brilliant advertisement schedule screen, grouping ads and reports by client, brushed-up email notifications for timed ads, and more.<br><br>Our team is in the early stages of development, and we would like to see if this product resonates with you.', 'advanced-ads' ),
				'cta'        => __( 'Are you interested in this product concept?', 'advanced-ads' ),
				'cta_button' => __( 'Yes, I want to know more!', 'advanced-ads' ),
			],
		];
	}

	/**
	 * Collect feature ID with their weight as recorded in the class constant. Also calculate the weight sum
	 */
	private function collect_weights() {
		if ( 0 !== $this->weight_sum ) {
			return;
		}
		foreach ( $this->features as $id => $feature ) {
			$this->weights[ $id ] = (int) $feature['weight'];
			$this->weight_sum    += $this->weights[ $id ];
		}
	}
}
