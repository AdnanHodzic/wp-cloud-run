<?php
/**
 * Rest Forms.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Rest;

use WP_REST_Request;
use AdvancedAds\Constants;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Arr;
use AdvancedAds\Framework\Interfaces\Routes_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Placement screen modal forms handling
 */
class Placements implements Routes_Interface {
	/**
	 * Registers routes with WordPress.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			Constants::REST_BASE,
			'/placement',
			[

				[
					'methods'             => \WP_REST_Server::ALLMETHODS,
					'callback'            => [ $this, 'call_endpoint' ],
					'permission_callback' => function () {
						return Conditional::user_can( 'advanced_ads_manage_placements' );
					},
				],
			]
		);
	}

	/**
	 * Call the appropriate endpoint handler
	 *
	 * @param WP_REST_Request $request the request object.
	 *
	 * @return array
	 */
	public function call_endpoint( $request ) {
		switch ( $request->get_method() ) {
			case 'POST':
				return $this->create( $request );
			case 'PUT':
				return $this->update( $request );
			case 'GET':
				return $this->read( $request );
			default:
		}

		return [ 'error' => __( 'No endpoint found', 'advanced-ads' ) ];
	}

	/**
	 * Get placement data
	 *
	 * @param WP_REST_Request $request the request object.
	 *
	 * @return array
	 */
	public function read( $request ) {
		$params    = $request->get_params();
		$placement = wp_advads_get_placement( (int) $params['id'] );

		if ( ! $placement ) {
			return [ 'error' => __( 'No placement found', 'advanced-ads' ) ];
		}

		/**
		 * Allow add-ons to send other placement props.
		 */
		$placement_data = apply_filters( 'advanced-ads-placement-read', $placement->get_data() );

		return $placement_data;
	}

	/**
	 * Create placement
	 *
	 * @param WP_REST_Request $request the request object.
	 *
	 * @return mixed
	 */
	public function create( $request ) {
		$body = json_decode( $request->get_body(), JSON_UNESCAPED_UNICODE );
		parse_str( $body['fields'], $payload );

		if ( ! wp_verify_nonce( sanitize_key( $payload['nonce'] ), 'advads-create-placement' ) ) {
			return [ 'error' => __( 'Not authorized create', 'advanced-ads' ) ];
		}

		$placement_data = wp_unslash( $payload['advads'] );

		if ( ! isset( $placement_data['placement'] ) ) {
			return [ 'error' => __( 'No placement data provided', 'advanced-ads' ) ];
		}

		$placement_data = wp_unslash( $placement_data['placement'] );
		$placement      = wp_advads_create_new_placement( $placement_data['type'] ?? 'default' );
		$placement->set_props( $placement_data );
		$placement->save();

		return apply_filters(
			'advanced-ads-placements-updated',
			[
				'action'         => 'create',
				'placement_data' => $placement->get_data(),
				'reload'         => true,
				'redirectUrl'    => admin_url( 'edit.php?post_type=' . Constants::POST_TYPE_PLACEMENT ),
			],
			$placement
		);
	}

	/**
	 * Update placement
	 *
	 * @param WP_REST_Request $request the request object.
	 *
	 * @return mixed
	 */
	public function update( $request ) {
		$body = json_decode( $request->get_body(), JSON_UNESCAPED_UNICODE );
		parse_str( $body['fields'], $payload );

		if ( ! wp_verify_nonce( sanitize_key( $payload['nonce'] ), 'advads-update-placement' ) ) {
			return [ 'error' => __( 'Not authorized update', 'advanced-ads' ) ];
		}

		$placement = wp_advads_get_placement( (int) $payload['post_ID'] );

		if ( ! $placement ) {
			return [ 'error' => __( 'Placement not found', 'advanced-ads' ) ];
		}

		$placement->set_title( sanitize_text_field( $payload['post_title'] ) );
		$placement->set_status( sanitize_text_field( $payload['post_status'] ) );

		if ( isset( $payload['advads']['placements'] ) ) {
			$placement_data = wp_unslash( $payload['advads']['placements'] );
			$placement->set_item( $placement_data['item'] );
			$placement->set_props( $placement_data['options'] );

			if ( ! Arr::get( $payload, 'advads.placements.options.repeat' ) && $placement->get_prop( 'repeat' ) ) {
				$placement->set_prop( 'repeat', null );
			}

			if ( ! Arr::get( $payload, 'advads.placements.options.start_from_bottom' ) && $placement->get_prop( 'start_from_bottom' ) ) {
				$placement->set_prop( 'start_from_bottom', null );
			}
		}

		// Allow add-ons to trigger a page refresh or show errors if needed.
		$results = apply_filters(
			'advanced-ads-placements-updated',
			[
				'action'         => 'update',
				'payload'        => $payload,
				'title'          => $placement->get_title(),
				'item'           => $placement->get_item(),
				'placement_data' => $placement->get_data(),
				'reload'         => false,
			],
			$placement
		);

		$placement->save();

		return $results;
	}
}
