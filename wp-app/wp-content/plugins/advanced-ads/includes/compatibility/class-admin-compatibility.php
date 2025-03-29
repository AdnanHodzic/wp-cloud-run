<?php
/**
 * This class is responsible for fixing compatibility issues in admin area.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Compatibility;

use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Compatibility.
 */
class Admin_Compatibility implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'dequeue_jnews_style' ], 100 );
		add_action( 'quads_meta_box_post_types', [ $this, 'fix_wpquadspro_issue' ], 11 );
		add_filter( 'wpml_admin_language_switcher_active_languages', [ $this, 'wpml_language_switcher' ] );
	}

	/**
	 * Fixes a WP QUADS PRO compatibility issue
	 * they inject their ad optimization meta box into our ad page, even though it is not a public post type
	 * using they filter, we remove AA from the list of post types they inject this box into
	 *
	 * @param array $allowed_post_types Array of allowed post types.
	 *
	 * @return array
	 */
	public function fix_wpquadspro_issue( $allowed_post_types ): array {
		unset( $allowed_post_types['advanced_ads'] );

		return $allowed_post_types;
	}

	/**
	 * Dequeue J-NEWS styles to prevent layout issues.
	 *
	 * @return void
	 */
	public function dequeue_jnews_style(): void {
		if ( ! Conditional::is_screen_advanced_ads() || ! defined( 'JNEWS_THEME_URL' ) ) {
			return;
		}

		wp_dequeue_style( 'jnews-admin' );
	}

	/**
	 * Show only all languages in language switcher on Advanced Ads pages if ads and groups are translated
	 *
	 * @param array $active_languages languages that can be used in language switcher.
	 *
	 * @return array
	 */
	public function wpml_language_switcher( $active_languages ): array {
		global $sitepress;

		$screen = get_current_screen();

		// Are we on group edit page and ad group translations are disabled.
		if ( isset( $screen->id ) && 'advanced-ads_page_advanced-ads-groups' === $screen->id ) {
			$translatable_taxonomies = $sitepress->get_translatable_taxonomies();
			if ( ! is_array( $translatable_taxonomies ) || ! in_array( 'advanced_ads_groups', $translatable_taxonomies, true ) ) {
				return [];
			}
		}

		// If ad post type is translatable.
		if ( isset( $screen->id ) && in_array( $screen->id, [ 'edit-advanced_ads', 'advanced_ads' ], true ) ) {
			$translatable_documents = $sitepress->get_translatable_documents();
			if ( empty( $translatable_documents['advanced_ads'] ) ) {
				return [];
			}
		}

		return $active_languages;
	}
}
