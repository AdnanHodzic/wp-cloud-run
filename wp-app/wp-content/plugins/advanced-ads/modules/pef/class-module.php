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
	 * User meta key where the dismiss flag is stored.
	 *
	 * @var string
	 */
	const USER_META = 'advanced_ads_pef_dismiss';

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
	 * Return the singleton. Create it if needed
	 *
	 * @return Module
	 */
	public static function get_instance(): Module {
		static $instance;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Singleton design
	 */
	private function __construct() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Initialization
	 *
	 * @return void
	 */
	public function admin_init(): void {
		$meta = get_user_meta( get_current_user_id(), self::USER_META, true );
		if ( $meta && $this->get_minor_version( ADVADS_VERSION ) === $this->get_minor_version( $meta ) ) {
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
	public function dismiss(): void {
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
	public function get_winner_feature(): array {
		$random_weight  = wp_rand( 1, $this->weight_sum );
		$current_weight = 0;
		foreach ( $this->get_features() as $id => $feature ) {
			$current_weight += $this->weights[ $id ];
			if ( $random_weight <= $current_weight ) {
				return array_merge(
					[
						'id'     => $id,
						'weight' => $this->weights[ $id ],
					],
					$this->get_features()[ $id ]
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
	public function render( $screen ): void { // phpcs:ignore
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
	public function get_minor_version( $version ): string {
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
	public function build_link( $winner, $screen ): string {
		$utm_source   = 'advanced-ads';
		$utm_medium   = 'link';
		$utm_campaign = sprintf( '%s-aa-labs', $screen );
		$utm_term     = sprintf(
			'b%s-w%d-%d',
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
	 * @return array
	 */
	public function get_features(): array {
		return [
			'labs-amazon-integration' => [
				'subheading' => __( 'FROM THE ADVANCED ADS LABS:', 'advanced-ads' ),
				'heading'    => __( 'The Amazon Integration', 'advanced-ads' ),
				'weight'     => 1,
				'text'       => __( 'Our latest product concept puts Amazon affiliate marketing at your fingertips—right within Advanced Ads. It offers features like direct product import via Amazon API, multiple product display formats, and efficient ad tracking. We aim to create a one-stop solution for featuring Amazon products on your site without resorting to expensive third-party plugins.', 'advanced-ads' ),
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
		foreach ( $this->get_features() as $id => $feature ) {
			$this->weights[ $id ] = (int) $feature['weight'];
			$this->weight_sum    += $this->weights[ $id ];
		}
	}
}
