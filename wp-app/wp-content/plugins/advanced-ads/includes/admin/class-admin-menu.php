<?php
/**
 * The class is responsible for adding menu and submenu pages for the plugin in the WordPress admin area.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin;

use Advanced_Ads;
use Advanced_Ads_Ad_Health_Notices;
use Advanced_Ads_Checks;
use AdvancedAds\Entities;
use AdvancedAds\Admin\Pages\Ads;
use AdvancedAds\Admin\Pages\Dashboard;
use AdvancedAds\Admin\Pages\Groups;
use AdvancedAds\Admin\Pages\Placements;
use AdvancedAds\Admin\Pages\Settings;
use AdvancedAds\Framework\Interfaces\Integration_Interface;
use AdvancedAds\Utilities\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Admin Menu.
 */
class Admin_Menu implements Integration_Interface {

	/**
	 * Hold screens
	 *
	 * @var array
	 */
	private $screens = [];

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_menu', [ $this, 'add_pages' ] );
		add_action( 'admin_head', [ $this, 'highlight_menu_item' ] );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_pages(): void {
		foreach ( $this->get_screens() as $renderer ) {
			$renderer->register_screen();
		}

		$this->register_forward_links();

		/**
		 * Allows extensions to insert sub menu pages.
		 *
		 * @since untagged Added the `$hidden_page_slug` parameter.
		 *
		 * @param string $plugin_slug      The slug slug used to add a visible page.
		 * @param string $hidden_page_slug The slug slug used to add a hidden page.
		 */
		do_action( 'advanced-ads-submenu-pages', ADVADS_SLUG, 'advanced_ads_hidden_page_slug' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
	}

	/**
	 * Register forward links
	 *
	 * @return void
	 */
	private function register_forward_links(): void {
		global $submenu;

		$has_ads      = Advanced_Ads::get_number_of_ads( [ 'any', 'trash' ] );
		$notices      = Advanced_Ads_Ad_Health_Notices::get_number_of_notices();
		$notice_alert = '&nbsp;<span class="update-plugins count-' . $notices . '"><span class="update-count">' . $notices . '</span></span>';

		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( current_user_can( WordPress::user_cap( 'advanced_ads_manage_options' ) ) ) {
			$submenu['advanced-ads'][] = [
				__( 'Support', 'advanced-ads' ),
				WordPress::user_cap( 'advanced_ads_manage_options' ),
				admin_url( 'admin.php?page=advanced-ads-settings#top#support' ),
				__( 'Support', 'advanced-ads' ),
			];

			if ( $has_ads ) {
				$submenu['advanced-ads'][0][0] .= $notice_alert;
			} else {
				$submenu['advanced-ads'][1][0] .= $notice_alert;
			}

			// Link to license tab if they are invalid.
			if ( Advanced_Ads_Checks::licenses_invalid() ) {
				$submenu['advanced-ads'][] = [
					__( 'Licenses', 'advanced-ads' )
						. '&nbsp;<span class="update-plugins count-1"><span class="update-count">!</span></span>',
					WordPress::user_cap( 'advanced_ads_manage_options' ),
					admin_url( 'admin.php?page=advanced-ads-settings#top#licenses' ),
					__( 'Licenses', 'advanced-ads' ),
				];
			}
		}
		// phpcs:enable
	}

	/**
	 * Get screens
	 *
	 * @return array
	 */
	private function get_screens(): array {
		if ( ! empty( $this->screens ) ) {
			return $this->screens;
		}

		$this->screens['dashboard']  = new Dashboard();
		$this->screens['ads']        = new Ads();
		$this->screens['groups']     = new Groups();
		$this->screens['placements'] = new Placements();
		$this->screens['settings']   = new Settings();

		return $this->screens;
	}

	/**
	 * Highlights the 'Advanced Ads->Ads' item in the menu when an ad edit page is open
	 *
	 * @see the 'parent_file' and the 'submenu_file' filters for reference
	 *
	 * @return void
	 */
	public function highlight_menu_item(): void {
		global $parent_file, $submenu_file, $post_type;

		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( Entities::POST_TYPE_AD === $post_type ) {
			$parent_file  = ADVADS_SLUG;
			$submenu_file = 'edit.php?post_type=' . Entities::POST_TYPE_AD;
		}
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
	}
}
