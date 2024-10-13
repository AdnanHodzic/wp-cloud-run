<?php // phpcs:ignore

use AdvancedAds\Entities;
use AdvancedAds\Utilities\WordPress;

/**
 * Functions around promoting upgrades
 *
 * @package Advanced Ads
 */
class Advanced_Ads_Admin_Upgrades {

	/**
	 * Advanced_Ads_Admin_Upgrades constructor.
	 */
	public function __construct() {
		// Show premium ad types on the ad edit page.
		add_action( 'advanced-ads-ad-types', [ $this, 'ad_types' ], 1000 );
		// Show notice in Ad Parameters when someone uses an Ad Manager ad in the plain text code field.
		add_filter( 'advanced-ads-ad-notices', [ $this, 'ad_notices' ], 10, 3 );
		// Show AMP options on ad edit page of AdSense ads.
		add_action( 'advanced-ads-gadsense-extra-ad-param', [ $this, 'adsense_type_amp_options' ] );
		// Add Duplicate link to ad overview list.
		add_filter( 'post_row_actions', [ $this, 'render_duplicate_link' ], 10, 2 );
		// Add Duplicate link to post submit box.
		add_action( 'post_submitbox_start', [ $this, 'render_duplicate_link_in_submit_box' ] );
	}

	/**
	 * Add premium ad types to ad type list on ad edit screens.
	 *
	 * @param array $ad_types ad types registered with Advanced Ads.
	 * @return array ad types.
	 */
	public function ad_types( $ad_types ) {
		// Add Google Ad Manager ad type.
		if ( ! defined( 'AAGAM_VERSION' ) ) {
			$ad_types['upgrade-gam']              = new stdClass();
			$ad_types['upgrade-gam']->ID          = 'gam';
			$ad_types['upgrade-gam']->title       = 'Google Ad Manager'; // Do not translate.
			$ad_types['upgrade-gam']->description = __( 'Load ad units directly from your Google Ad Manager account.', 'advanced-ads' );
			$ad_types['upgrade-gam']->is_upgrade  = true;
			$ad_types['upgrade-gam']->upgrade_url = 'https://wpadvancedads.com/add-ons/google-ad-manager/';
		}

		// Add AMP ad type from Responsive Ads add-on.
		if ( ! defined( 'AAR_VERSION' ) ) {
			$ad_types['upgrade-amp']              = new stdClass();
			$ad_types['upgrade-amp']->ID          = 'amp';
			$ad_types['upgrade-amp']->title       = 'AMP'; // Do not translate.
			$ad_types['upgrade-amp']->description = __( 'Ads that are visible on Accelerated Mobile Pages.', 'advanced-ads' );
			$ad_types['upgrade-amp']->is_upgrade  = true;
			$ad_types['upgrade-amp']->upgrade_url = 'https://wpadvancedads.com/add-ons/responsive-ads/';
		}

		return $ad_types;
	}

	/**
	 * Show an upgrade link
	 *
	 * @param string $title link text.
	 * @param string $url target URL.
	 * @param string $utm_campaign utm_campaign value to attach to the URL.
	 */
	public static function upgrade_link( $title = '', $url = '', $utm_campaign = 'upgrade' ) {
		$title = ! empty( $title ) ? $title : __( 'Upgrade', 'advanced-ads' );
		$url   = ! empty( $url ) ? $url : 'https://wpadvancedads.com/add-ons/';

		$url = add_query_arg( [
			'utm_source'   => 'advanced-ads',
			'utm_medium'   => 'link',
			'utm_campaign' => $utm_campaign,
		], $url );

		include ADVADS_ABSPATH . 'admin/views/upgrades/upgrade-link.php';
	}

	/**
	 * Show an Advanced Ads Pro upsell pitch
	 *
	 * @param string $utm_campaign utm_campaign value to attach to the URL.
	 * @deprecated use upgrade_link()
	 */
	public static function pro_feature_link( $utm_campaign = '' ) {
		self::upgrade_link(
			__( 'Pro Feature', 'advanced-ads' ),
			'https://wpadvancedads.com/advanced-ads-pro/',
			$utm_campaign
		);
	}

	/**
	 * Show notices in the Ad Parameters meta box
	 *
	 * @param array   $notices Notices.
	 * @param array   $box current meta box.
	 * @param WP_Post $post post object.
	 * @return array $notices Notices.
	 */
	public function ad_notices( $notices, $box, $post ) {
		// Show notice when someone uses an Ad Manager ad in the plain text code field.
		if ( ! defined( 'AAGAM_VERSION' ) && 'ad-parameters-box' === $box['id'] ) {
			$ad = \Advanced_Ads\Ad_Repository::get( $post->ID );
			if ( 'plain' === $ad->type && strpos( $ad->content, 'div-gpt-ad-' ) ) {
				$notices[] = [
					'text' => sprintf(
					// Translators: %1$s opening a tag, %2$s closing a tag.
						esc_html__( 'This looks like a Google Ad Manager ad. Use the %1$sGAM Integration%2$s.', 'advanced' ),
						'<a href="https://wpadvancedads.com/add-ons/google-ad-manager/?utm_source=advanced-ads&utm_medium=link&utm_campaign=upgrade-ad-parameters-gam" target="_blank">',
						'</a>'
					) . ' ' . __( 'A quick and error-free way of implementing ad units from your Google Ad Manager account.', 'advanced-ads' ),
				];
			}
		}

		return $notices;
	}

	/**
	 * AMP options for AdSense ads in the Ad Parameters on the ad edit page.
	 */
	public function adsense_type_amp_options() {
		if ( ! defined( 'AAR_VERSION' ) && Advanced_Ads_Checks::active_amp_plugin() ) {
			include ADVADS_ABSPATH . 'admin/views/upgrades/adsense-amp.php';
		}
	}

	/**
	 * Add the link to action list for post_row_actions
	 *
	 * @param array   $actions list of existing actions.
	 * @param WP_Post $post Post object.
	 *
	 * @return array with actions.
	 */
	public function render_duplicate_link( $actions, $post ) {
		if (
			 ! defined( 'AAP_VERSION' )
			 && Entities::POST_TYPE_AD === $post->post_type
			 && WordPress::user_can( 'advanced_ads_edit_ads' )
		) {
			$actions['copy-ad'] = $this->create_duplicate_link();
		}

		return $actions;
	}

	/**
	 * Add the link to the submit box on the ad edit screen.
	 */
	public function render_duplicate_link_in_submit_box() {
		global $post;
		if (
			! defined( 'AAP_VERSION' )
			 && $post->filter === 'edit' // only for already saved ads.
			 && Entities::POST_TYPE_AD === $post->post_type
			 && WordPress::user_can( 'advanced_ads_edit_ads' )
		) {
			?>
			<div>
				<?php echo wp_kses_post( $this->create_duplicate_link() ); ?>
			</div>
			<?php
		}
	}

	/**
	 * Generate text and upgrade link for the Duplicate function
	 */
	public function create_duplicate_link() {
		ob_start();
		self::upgrade_link( null, 'https://wpadvancedads.com/checkout/?edd_action=add_to_cart&download_id=1742', 'duplicate-ad' );

		return sprintf(
			'%1$s (%2$s)',
			esc_html__( 'Duplicate', 'advanced-ads' ),
			trim( ob_get_clean() )
		);
	}
}
