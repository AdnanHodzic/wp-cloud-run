<?php
/**
 * The class provides plugin uninstallation routines.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Installation;

use Advanced_Ads;
use Advanced_Ads_Widget;
use Advanced_Ads_Ad_Blocker_Admin;
use AdvancedAds\Entities;
use AdvancedAds\Framework\Interfaces\Initializer_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Installation Uninstall.
 *
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
 */
class Uninstall implements Initializer_Interface {

	/**
	 * Runs this initializer.
	 *
	 * @return void
	 */
	public function initialize() {
		global $wpdb;

		$advads_options = Advanced_Ads::get_instance()->options();

		// Early bail!!
		if ( empty( $advads_options['uninstall-delete-data'] ) ) {
			return;
		}

		// Delete assets (main blog).
		Advanced_Ads_Ad_Blocker_Admin::get_instance()->clear_assets();
		( new Entities() )->hooks();

		if ( ! is_multisite() ) {
			$this->uninstall();
			return;
		}

		$site_ids = Install::get_sites();

		if ( empty( $site_ids ) ) {
			return;
		}

		foreach ( $site_ids as $site_id ) {
			switch_to_blog( $site_id );
			$this->uninstall();
			restore_current_blog();
		}
	}

	/**
	 * Fired for each blog when the plugin is uninstalled.
	 *
	 * @return void
	 */
	private function uninstall(): void { // phpcs:ignore Universal.CodeAnalysis.ConstructorDestructorReturn.ReturnTypeFound
		self::delete_ads();
		self::delete_groups();
		self::delete_options();
		self::delete_usermeta();

		wp_cache_flush();
	}

	/**
	 * Delete ads posts and postmeta.
	 *
	 * @return void
	 */
	private function delete_ads(): void {
		global $wpdb;

		$post_ids = $wpdb->get_col(
			$wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s", Entities::POST_TYPE_AD )
		);

		if ( $post_ids ) {
			$wpdb->delete(
				$wpdb->posts,
				[ 'post_type' => Entities::POST_TYPE_AD ],
				[ '%s' ]
			);

			$wpdb->query(
				$wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE post_id IN( %s )", implode( ',', $post_ids ) )
			);
		}

		// Delete from postmeta.
		delete_metadata( 'post', null, '_advads_ad_settings', '', true );
	}

	/**
	 * Delete groups.
	 *
	 * @return void
	 */
	private function delete_groups(): void {
		global $wpdb;

		$term_ids = $wpdb->get_col(
			$wpdb->prepare( "SELECT t.term_id FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s", Entities::TAXONOMY_AD_GROUP )
		);

		foreach ( $term_ids as $term_id ) {
			wp_delete_term( $term_id, Entities::TAXONOMY_AD_GROUP );
		}
	}

	/**
	 * Delete options.
	 *
	 * @return void
	 */
	private function delete_options(): void {
		delete_option( 'advanced-ads' );
		delete_option( 'advanced-ads-internal' );
		delete_option( 'advanced-ads-notices' );
		delete_option( 'advads-ad-groups' );
		delete_option( 'advanced_ads_groups_children' );
		delete_option( 'advads-ad-weights' );
		delete_option( 'advanced_ads_ads_txt' );
		delete_option( 'advanced-ads-ad-health-notices' );
		delete_option( 'advanced-ads-adsense' );
		delete_option( 'advanced_ads_adsense_report_domain' );
		delete_option( 'advanced_ads_adsense_report_unit' );
		delete_option( 'advanced-ads-adsense-dashboard-filter' );
		delete_option( 'advanced-ads-adsense-mapi' );
		delete_option( 'advanced-ads-licenses' );
		delete_option( 'advanced-ads-ab-module' );
		delete_option( 'widget_' . Advanced_Ads_Widget::get_base_id() );
		delete_option( 'advads-ads-placements' );

		// Transients.
		delete_transient( 'advanced-ads_add-on-updates-checked' );
	}

	/**
	 * Delete usermeta.
	 *
	 * @return void
	 */
	private function delete_usermeta(): void {
		delete_metadata( 'user', null, 'advanced-ads-hide-wizard', '', true );
		delete_metadata( 'user', null, 'advanced-ads-subscribed', '', true );
		delete_metadata( 'user', null, 'advanced-ads-ad-list-screen-options', '', true );
		delete_metadata( 'user', null, 'advanced-ads-admin-settings', '', true );
		delete_metadata( 'user', null, 'advanced-ads-role', '', true );
		delete_metadata( 'user', null, 'edit_advanced_ads_per_page', '', true );
		delete_metadata( 'user', null, 'meta-box-order_advanced_ads', '', true );
		delete_metadata( 'user', null, 'screen_layout_advanced_ads', '', true );
		delete_metadata( 'user', null, 'closedpostboxes_advanced_ads', '', true );
		delete_metadata( 'user', null, 'metaboxhidden_advanced_ads', '', true );
	}
}
