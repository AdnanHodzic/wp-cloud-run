<?php
/**
 * Uncategorised functionality.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.x.x
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Constants;
use Advanced_Ads_Admin_Notices;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Importers\XML_Importer;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Class Misc
 */
class Misc implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'gettext', [ $this, 'replace_cheating_message' ], 20, 2 );
		add_filter( 'get_user_option_user-settings', [ $this, 'reset_view_mode_option' ] );
		add_action( 'in_admin_header', [ $this, 'register_admin_notices' ] );
		add_action( 'plugins_api_result', [ $this, 'recommend_suitable_add_ons' ], 11, 3 );
		add_action( 'admin_action_advanced_ads_starter_setup', [ $this, 'import_starter_setup' ] );
	}

	/**
	 * Replace 'You need a higher level of permission.' message if user role does not have required permissions.
	 *
	 * @param string $translated_text Translated text.
	 * @param string $untranslated_text Text to translate.
	 *
	 * @return string $translation  Translated text.
	 */
	public function replace_cheating_message( $translated_text, $untranslated_text ): string {
		global $typenow;

		if (
			isset( $typenow )
			&& 'You need a higher level of permission.' === $untranslated_text
			&& Constants::POST_TYPE_AD === $typenow
		) {
			$translated_text = __( 'You don’t have access to ads. Please deactivate and re-enable Advanced Ads again to fix this.', 'advanced-ads' )
				. '&nbsp;<a href="https://wpadvancedads.com/manual/user-capabilities/?utm_source=advanced-ads&utm_medium=link&utm_campaign=wrong-user-role#You_dont_have_access_to_ads" target="_blank">' . __( 'Get help', 'advanced-ads' ) . '</a>';
		}

		return (string) $translated_text;
	}

	/**
	 * Set the removed post list mode to "List", if it was set to "Excerpt".
	 *
	 * @param string $user_options Query string containing user options.
	 *
	 * @return string
	 */
	public function reset_view_mode_option( $user_options ): string {
		return str_replace( '&posts_list_mode=excerpt', '&posts_list_mode=list', $user_options );
	}

	/**
	 * Registers Advanced Ads admin notices
	 * prevents other notices from showing up on our own pages
	 *
	 * @return void
	 */
	public function register_admin_notices(): void {
		/**
		 * Remove all registered admin_notices from AA screens
		 * we need to use this or some users have half or more of their viewports cluttered with unrelated notices
		 */
		if ( Conditional::is_screen_advanced_ads() ) {
			remove_all_actions( 'admin_notices' );
		}

		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
	}

	/**
	 * Initiate the admin notices class
	 *
	 * @return void
	 */
	public function admin_notices(): void {
		// Display ad block warning to everyone who can edit ads.
		if (
			Conditional::user_can( 'advanced_ads_edit_ads' )
			&& Conditional::is_screen_advanced_ads()
		) {
			$ad_blocker_notice_id = wp_advads()->get_frontend_prefix() . 'abcheck-' . md5( microtime() );
			wp_register_script( $ad_blocker_notice_id . '-adblocker-notice', false, [], ADVADS_VERSION, true );
			wp_enqueue_script( $ad_blocker_notice_id . '-adblocker-notice' );
			wp_add_inline_script(
				$ad_blocker_notice_id . '-adblocker-notice',
				"jQuery( document ).ready( function () {
					if ( typeof advanced_ads_adblocker_test === 'undefined' ) {
						jQuery( '#" . esc_attr( $ad_blocker_notice_id ) . ".message' ).show();
					}
				} );"
			);
			include_once ADVADS_ABSPATH . 'admin/views/notices/adblock.php';
		}

		// Show success notice after starter setup was imported. Registered here because it will be visible only once.
		if ( 'advanced-ads-starter-setup-success' === Params::get( 'message' ) ) {
			add_action( 'advanced-ads-admin-notices', [ $this, 'starter_setup_success_message' ] );
		}

		/*
		Register our own notices on Advanced Ads pages, except
		-> the overview page where they should appear in the notices section,
		-> revision page to prevent duplicate revision controls.
		*/
		$screen = get_current_screen();
		if (
			Conditional::user_can( 'advanced_ads_edit_ads' )
			&& ( ! isset( $screen->id ) || 'toplevel_page_advanced-ads' !== $screen->id )
			&& 'revision' !== $screen->id
		) {

			echo '<div class="wrap">';
			Advanced_Ads_Admin_Notices::get_instance()->display_notices();

			// Allow other Advanced Ads plugins to show admin notices at this late stage.
			do_action( 'advanced-ads-admin-notices' );
			echo '</div>';
		}
	}

	/**
	 * Show success message after starter setup was created.
	 *
	 * @return void
	 */
	public function starter_setup_success_message(): void {
		$last_post      = get_posts( [ 'numberposts' => 1 ] );
		$last_post_link = isset( $last_post[0]->ID ) ? get_permalink( $last_post[0]->ID ) : false;

		include ADVADS_ABSPATH . 'admin/views/notices/starter-setup-success.php';
	}

	/**
	 * Recommend additional add-ons
	 *
	 * @param object|WP_Error $result Response object or WP_Error.
	 * @param string          $action The type of information being requested from the Plugin Installation API.
	 * @param object          $args Plugin API arguments.
	 *
	 * @return object|WP_Error Response object or WP_Error.
	 */
	public function recommend_suitable_add_ons( $result, $action, $args ) {
		if (
			empty( $args->browse )
			|| ! in_array( $args->browse, [ 'featured', 'recommended', 'popular' ], true )
			|| ( isset( $result->info['page'] ) && $result->info['page'] > 1 )
		) {
			return $result;
		}

		// Grab all slugs from the api results.
		$result_slugs = wp_list_pluck( $result->plugins, 'slug' );

		// Recommend AdSense In-Feed add-on.
		$result = $this->recommend_plugin(
			'advanced-ads-adsense-in-feed',
			'advanced-ads-adsense-in-feed/advanced-ads-in-feed.php',
			$args,
			$result,
			$result_slugs
		);

		// Recommend Genesis Ads add-on.
		if ( defined( 'PARENT_THEME_NAME' ) && 'Genesis' === PARENT_THEME_NAME ) {
			$result = $this->recommend_plugin(
				'advanced-ads-genesis',
				'advanced-ads-genesis/genesis-ads.php',
				$args,
				$result,
				$result_slugs
			);
		}

		// Recommend WP Bakery (former Visual Composer) add-on.
		if ( defined( 'WPB_VC_VERSION' ) ) {
			$result = $this->recommend_plugin(
				'ads-for-visual-composer',
				'ads-for-visual-composer/advanced-ads-vc.php',
				$args,
				$result,
				$result_slugs
			);
		}

		return $result;
	}

	/**
	 * Import a starter setup for new users
	 *
	 * @return void
	 */
	public function import_starter_setup(): void {
		if (
			'advanced_ads_starter_setup' !== Params::get( 'action' )
			|| ! Conditional::user_can( 'advanced_ads_edit_ads' )
		) {
			return;
		}

		check_admin_referer( 'advanced-ads-starter-setup' );

		$xml = file_get_contents( ADVADS_ABSPATH . 'admin/assets/xml/starter-setup.xml' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		( new XML_Importer() )->import_content( $xml );

		// Redirect to the ads overview page.
		wp_safe_redirect( admin_url( 'edit.php?post_type=advanced_ads&message=advanced-ads-starter-setup-success' ) );
	}

	/**
	 * Recommends a plugin based on the provided slug and file path.
	 *
	 * This function checks if the plugin is already active or recommended. If not,
	 * it fetches the plugin data using the WordPress Plugin API and adds it to the result.
	 *
	 * @param string $slug         The slug of the plugin to recommend.
	 * @param string $file_path    The file path of the plugin.
	 * @param object $args         Additional arguments for the recommendation.
	 * @param object $result       The current result object containing recommended plugins.
	 * @param array  $result_slugs An array of slugs of already recommended plugins.
	 *
	 * @return object The updated result object with the recommended plugin added.
	 */
	private function recommend_plugin( $slug, $file_path, $args, $result, $result_slugs ) {
		// Check if the plugin is already active or recommended.
		if (
			is_plugin_active( $file_path )
			|| is_plugin_active_for_network( $file_path )
			|| in_array( $slug, $result_slugs, true )
		) {
			return $result;
		}

		// Prepare query arguments to fetch plugin data.
		$query_args  = [
			'slug'   => $slug,
			'fields' => [
				'icons'             => true,
				'active_installs'   => true,
				'short_description' => true,
				'group'             => true,
			],
		];
		$plugin_data = plugins_api( 'plugin_information', $query_args );

		// Add plugin data to the result if fetched successfully.
		if ( ! is_wp_error( $plugin_data ) ) {
			if ( 'featured' === $args->browse ) {
				array_push( $result->plugins, $plugin_data );
			} else {
				array_unshift( $result->plugins, $plugin_data );
			}
		}

		return $result;
	}
}
