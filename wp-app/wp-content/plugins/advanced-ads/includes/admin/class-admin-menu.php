<?php
/**
 * The class is responsible for adding menu and submenu pages for the plugin in the WordPress admin area.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin;

use Advanced_Ads_Checks;
use Advanced_Ads_Ad_Health_Notices;
use AdvancedAds\Constants;
use AdvancedAds\Admin\Pages;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

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
	 * Hold screen hooks
	 *
	 * @var array
	 */
	private $screen_ids = null;

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_menu', [ $this, 'add_pages' ], 15 );
		add_action( 'admin_head', [ $this, 'highlight_menu_item' ] );
		add_filter( 'admin_body_class', [ $this, 'add_body_class' ] );
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
		 * @deprecated 2.0.0 use `advanced-ads-add-screen` instead.
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

		$has_ads      = WordPress::get_count_ads();
		$notices      = Advanced_Ads_Ad_Health_Notices::get_number_of_notices();
		$notice_alert = '&nbsp;<span class="update-plugins count-' . $notices . '"><span class="update-count">' . $notices . '</span></span>';

		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( current_user_can( Conditional::user_cap( 'advanced_ads_manage_options' ) ) ) {
			$submenu['advanced-ads'][] = [
				__( 'Support', 'advanced-ads' ),
				Conditional::user_cap( 'advanced_ads_manage_options' ),
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
					Conditional::user_cap( 'advanced_ads_manage_options' ),
					admin_url( 'admin.php?page=advanced-ads-settings#top#licenses' ),
					__( 'Licenses', 'advanced-ads' ),
				];
			}
		}
		// phpcs:enable
	}

	/**
	 * Get a screen by its id
	 *
	 * @param string $id Screen id.
	 *
	 * @return Screen|null
	 */
	public function get_screen( string $id ) {
		$screens = $this->get_screens();

		return $screens[ $id ] ?? null;
	}

	/**
	 * Get the hook of a screen by its id
	 *
	 * @param string $id Screen id.
	 *
	 * @return string|null
	 */
	public function get_hook( $id ) {
		$screen = $this->get_screen( $id );

		return $screen ? $screen->get_hook() : null;
	}

	/**
	 * Add a screen to the list of screens
	 *
	 * @param string $screen Screen class name.
	 *
	 * @return void
	 */
	public function add_screen( string $screen ): void {
		$screen = new $screen();

		$this->screens[ $screen->get_id() ] = $screen;
	}

	/**
	 * Get screens
	 *
	 * @return array
	 */
	public function get_screens(): array {
		if ( ! empty( $this->screens ) ) {
			return $this->screens;
		}

		$this->add_screen( Pages\Dashboard::class );
		$this->add_screen( Pages\Ads::class );
		$this->add_screen( Pages\Ads_Editing::class );
		$this->add_screen( Pages\Groups::class );
		$this->add_screen( Pages\Placements::class );
		$this->add_screen( Pages\Settings::class );
		$this->add_screen( Pages\Tools::class );
		$this->add_screen( Pages\Onboarding::class );

		if ( defined( 'ADVADS_UI_KIT' ) && ADVADS_UI_KIT ) {
			$this->add_screen( Pages\Ui_Toolkit::class );
		}

		/**
		 * Let developers add their own screens.
		 *
		 * @param array $screens
		 */
		do_action( 'advanced-ads-add-screen', $this );

		// Order screens using the order property.
		uasort(
			$this->screens,
			static function ( $a, $b ) {
				$order_a = $a->get_order();
				$order_b = $b->get_order();

				if ( $order_a === $order_b ) {
					return 0;
				}

				return ( $order_a < $order_b ) ? -1 : 1;
			}
		);

		return $this->screens;
	}

	/**
	 * Get screen ids
	 *
	 * @return array
	 */
	public function get_screen_ids(): array {
		if ( null !== $this->screen_ids ) {
			return $this->screen_ids;
		}

		$screens = $this->get_screens();

		foreach ( $screens as $screen ) {
			$this->screen_ids[] = $screen->get_hook();
		}

		return $this->screen_ids;
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
		if ( Constants::POST_TYPE_AD === $post_type ) {
			$parent_file  = ADVADS_SLUG;
			$submenu_file = 'edit.php?post_type=' . Constants::POST_TYPE_AD;
		}
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Add a custom class to the body tag of Advanced Ads screens.
	 *
	 * @param string $classes Space-separated class list.
	 *
	 * @return string
	 */
	public function add_body_class( string $classes ): string {
		$screen_ids = $this->get_screen_ids();
		$wp_screen  = get_current_screen();

		if ( in_array( $wp_screen->id, $screen_ids, true ) ) {
			$classes .= ' advads-page';
		}

		return $classes;
	}
}
