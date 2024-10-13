<?php
/**
 * The class handles the registration of custom post types and taxonomies in the plugin.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Entities.
 */
class Entities implements Integration_Interface {

	/**
	 * Post type slug
	 *
	 * @var string
	 */
	const POST_TYPE_AD = 'advanced_ads';

	/**
	 * Ad group slug
	 *
	 * @var string
	 */
	const TAXONOMY_AD_GROUP = 'advanced_ads_groups';

	/**
	 * Hook into WordPress.
	 */
	public function hooks() {
		$this->register_ad_post_type();
		$this->register_group_taxonomy();
	}

	/**
	 * Register ad post type.
	 *
	 * @return void
	 */
	private function register_ad_post_type(): void {
		// Early bail!!
		if ( post_type_exists( self::POST_TYPE_AD ) ) {
			return;
		}

		$labels = [
			'name'               => __( 'Ads', 'advanced-ads' ),
			'singular_name'      => __( 'Ad', 'advanced-ads' ),
			'add_new'            => __( 'New Ad', 'advanced-ads' ),
			'add_new_item'       => __( 'Add New Ad', 'advanced-ads' ),
			'edit'               => __( 'Edit', 'advanced-ads' ),
			'edit_item'          => __( 'Edit Ad', 'advanced-ads' ),
			'new_item'           => __( 'New Ad', 'advanced-ads' ),
			'view'               => __( 'View', 'advanced-ads' ),
			'view_item'          => __( 'View the Ad', 'advanced-ads' ),
			'search_items'       => __( 'Search Ads', 'advanced-ads' ),
			'not_found'          => __( 'No Ads found', 'advanced-ads' ),
			'not_found_in_trash' => __( 'No Ads found in Trash', 'advanced-ads' ),
			'parent'             => __( 'Parent Ad', 'advanced-ads' ),
		];

		$supports = [ 'title', 'author' ];
		if ( defined( 'ADVANCED_ADS_ENABLE_REVISIONS' ) ) {
			$supports[] = 'revisions';
		}

		$args = [
			'labels'       => $labels,
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => false,
			'hierarchical' => false,
			'capabilities' => [
				// Meta capabilities.
				'edit_post'              => 'advanced_ads_edit_ads',
				'read_post'              => 'advanced_ads_edit_ads',
				'delete_post'            => 'advanced_ads_edit_ads',
				'edit_page'              => 'advanced_ads_edit_ads',
				'read_page'              => 'advanced_ads_edit_ads',
				'delete_page'            => 'advanced_ads_edit_ads',
				// Primitive capabilities used outside of map_meta_cap().
				'edit_posts'             => 'advanced_ads_edit_ads',
				'publish_posts'          => 'advanced_ads_edit_ads',
				'read_private_posts'     => 'advanced_ads_edit_ads',
				// Primitive capabilities used within map_meta_cap().
				'read'                   => 'advanced_ads_edit_ads',
				'delete_posts'           => 'advanced_ads_edit_ads',
				'delete_private_posts'   => 'advanced_ads_edit_ads',
				'delete_published_posts' => 'advanced_ads_edit_ads',
				'edit_private_posts'     => 'advanced_ads_edit_ads',
				'edit_published_posts'   => 'advanced_ads_edit_ads',
				'create_posts'           => 'advanced_ads_edit_ads',
			],
			'has_archive'  => false,
			'query_var'    => false,
			'rewrite'      => false,
			'supports'     => $supports,
			'taxonomies'   => [ self::TAXONOMY_AD_GROUP ],
		];

		register_post_type(
			self::POST_TYPE_AD,
			apply_filters( 'advanced-ads-post-type-params', $args )
		);
	}

	/**
	 * Register group taxonomy.
	 *
	 * @return void
	 */
	private function register_group_taxonomy(): void {
		// Early bail!!
		if ( taxonomy_exists( self::TAXONOMY_AD_GROUP ) ) {
			return;
		}

		$labels = [
			'name'              => _x( 'Ad Groups & Rotations', 'ad group general name', 'advanced-ads' ),
			'singular_name'     => _x( 'Ad Group', 'ad group singular name', 'advanced-ads' ),
			'search_items'      => __( 'Search Ad Groups', 'advanced-ads' ),
			'all_items'         => __( 'All Ad Groups', 'advanced-ads' ),
			'parent_item'       => __( 'Parent Ad Groups', 'advanced-ads' ),
			'parent_item_colon' => __( 'Parent Ad Groups:', 'advanced-ads' ),
			'edit_item'         => __( 'Edit Ad Group', 'advanced-ads' ),
			'update_item'       => __( 'Update Ad Group', 'advanced-ads' ),
			'add_new_item'      => __( 'New Ad Group', 'advanced-ads' ),
			'new_item_name'     => __( 'New Ad Groups Name', 'advanced-ads' ),
			'menu_name'         => __( 'Groups', 'advanced-ads' ),
			'not_found'         => __( 'No Ad Group found', 'advanced-ads' ),
		];

		$args = [
			'public'            => false,
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_in_nav_menus' => false,
			'show_in_menu'      => false,
			'show_tagcloud'     => false,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'advanced_ads_edit_ads',
				'edit_terms'   => 'advanced_ads_edit_ads',
				'delete_terms' => 'advanced_ads_edit_ads',
				'assign_terms' => 'advanced_ads_edit_ads',
			],
		];

		register_taxonomy(
			self::TAXONOMY_AD_GROUP,
			self::POST_TYPE_AD,
			apply_filters( 'advanced-ads-group-taxonomy-params', $args )
		);
	}

	/**
	 * Placement description
	 *
	 * @return string
	 */
	public static function get_placement_description(): string {
		return __( 'Placements are customizable ad spots on your site. Use them to see and change all the assigned ads and groups on this page. Furthermore, you can set up exclusive features like Cache Busting, Lazy Loading, AdBlocker fallbacks, or Parallax effects.', 'advanced-ads' );
	}

	/**
	 * Group description
	 *
	 * @return string
	 */
	public static function get_group_description(): string {
		return __( 'Ad Groups are a flexible method to bundle ads. Use them to create ad rotations, run split tests, and organize your ads in the backend. An ad can belong to multiple ad groups.', 'advanced-ads' );
	}
}
