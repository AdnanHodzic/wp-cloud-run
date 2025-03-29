<?php
/**
 * Rest OnBoarding.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Rest;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use AdvancedAds\Options;
use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Ad;
use Advanced_Ads_AdSense_Data;
use AdvancedAds\Abstracts\Placement;
use AdvancedAds\Framework\Interfaces\Routes_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Rest OnBoarding.
 */
class OnBoarding implements Routes_Interface {

	/**
	 * Registers routes with WordPress.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			Constants::REST_BASE,
			'/onboarding',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_options' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Save onbaording options
	 *
	 * @param WP_REST_Request $request Request data.
	 *
	 * @return mixed
	 */
	public function save_options( $request ) {
		$task_code    = $request->get_param( 'taskOption' );
		$allowed_task = [
			'google_adsense',
			'ad_image',
			'ad_code',
		];

		if ( ! in_array( $task_code, $allowed_task, true ) ) {
			return new WP_Error(
				'task_not_allowed',
				esc_html__( 'The task code posted is not allowed', 'advanced-ads' )
			);
		}

		$item = $this->$task_code( $request );
		update_option( '_advanced_ads_data_usage', $request->get_param( 'agreement' ) ? true : false );

		// First post link.
		$posts = get_posts(
			[
				'posts_per_page' => 1,
				'post_type'      => 'post',
			]
		);

		update_option( Constants::OPTION_WIZARD_COMPLETED, true );

		$pub_id      = Options::instance()->get( 'adsense.adsense-id' );
		$adsense_url = $pub_id ? "https://www.google.com/adsense/new/u/0/$pub_id/myads/sites" : '';

		return [
			'success'        => true,
			'itemEditLink'   => $item ? $item->get_edit_link() : '',
			'postLink'       => ! empty( $posts ) ? get_permalink( $posts[0] ) : '',
			'adsenseAccount' => $adsense_url,
		];
	}

	/**
	 * Create ad by google adsense
	 *
	 * @param WP_REST_Request $request Request data.
	 *
	 * @return bool|Placement|Ad
	 */
	private function google_adsense( $request ) {
		$ads_placement = $request->get_param( 'googleAdsPlacement' );

		if ( 'auto_ads' === $ads_placement ) {
			$ads_options = $request->get_param( 'autoAdsOptions' );
			$options     = Advanced_Ads_AdSense_Data::get_instance()->get_options();

			$options['page-level-enabled'] = in_array( 'enable', $ads_options, true );

			if ( isset( $options['amp'] ) && ! in_array( 'enableAmp', $ads_options, true ) ) {
				unset( $options['amp'] );
			} else {
				$options['amp']['auto_ads_enabled'] = 1;
			}

			update_option( GADSENSE_OPT_NAME, $options );
		} elseif ( 'manual' === $ads_placement ) {
			$ad = wp_advads_create_new_ad( 'adsense' );
			$ad->save();

			$placement = wp_advads_create_new_placement();
			$placement->set_title( 'AdSense Placement from wizard # ' . wp_rand() );
			$placement->set_item( 'ad_' . $ad->get_id() );
			$placement->save();

			return $ad;
		}

		return false;
	}

	/**
	 * Create ad by image
	 *
	 * @param WP_REST_Request $request Request data.
	 *
	 * @return Placement
	 */
	private function ad_image( $request ): Placement {
		$image     = $request->get_param( 'adImage' );
		$image_url = $request->get_param( 'adImageUrl' );

		$ad = wp_advads_create_new_ad( 'image' );
		$ad->set_title( 'Test image ad from wizard # ' . wp_rand() );
		$ad->set_image_id( $image['id'] );
		$ad->set_url( $image_url );
		$ad->save();

		return $this->create_placement( $ad );
	}

	/**
	 * Create ad by code
	 *
	 * @param WP_REST_Request $request Request data.
	 *
	 * @return Placement
	 */
	private function ad_code( $request ): Placement {
		$ad = wp_advads_create_new_ad( 'plain' );
		$ad->set_title( 'Test ad from wizard # ' . wp_rand() );
		$ad->set_content( $request->get_param( 'adCode' ) );
		$ad->save();

		return $this->create_placement( $ad );
	}

	/**
	 * Create placement with the ad
	 *
	 * @param Ad $ad Ad instance to use as item.
	 *
	 * @return Placement
	 */
	private function create_placement( Ad $ad ): Placement {
		$placement = wp_advads_create_new_placement( 'post_content' );
		$placement->set_title( 'Test Placement from wizard # ' . wp_rand() );
		$placement->set_item( 'ad_' . $ad->get_id() );
		$placement->set_prop( 'position', 'after' );
		$placement->set_prop( 'index', 3 );
		$placement->set_prop( 'tag', 'p' );
		$placement->save();

		return $placement;
	}
}
