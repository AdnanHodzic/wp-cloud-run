<?php
/**
 * Dashboard screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use Advanced_Ads;
use Advanced_Ads_Overview_Widgets_Callbacks;
use AdvancedAds\Interfaces\Screen_Interface;
use AdvancedAds\Utilities\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Dashboard.
 */
class Dashboard implements Screen_Interface {

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		$has_ads = Advanced_Ads::get_number_of_ads( [ 'any', 'trash' ] );

		add_menu_page(
			__( 'Dashboard', 'advanced-ads' ),
			'Advanced Ads',
			WordPress::user_cap( 'advanced_ads_see_interface' ),
			ADVADS_SLUG,
			[ $this, 'display' ],
			$this->get_icon_svg(),
			'58.74'
		);

		add_submenu_page(
			ADVADS_SLUG,
			__( 'Dashboard', 'advanced-ads' ),
			__( 'Dashboard', 'advanced-ads' ),
			WordPress::user_cap( $has_ads ? 'advanced_ads_edit_ads' : 'advanced_ads_see_interface' ),
			ADVADS_SLUG,
			$has_ads ? '' : [ $this, 'display' ]
		);
	}

	/**
	 * Display screen content.
	 *
	 * @return void
	 */
	public function display(): void {
		include ADVADS_ABSPATH . 'views/admin/screens/dashboard.php';
	}

	/**
	 * Return Advanced Ads logo in base64 format for use in WP Admin menu. * Highlights the 'Advanced Ads->Ads' item in the menu when an ad edit page is open
	 *
	 * @return string
	 */
	private function get_icon_svg(): string {
		return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE4LjEuMSwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJFYmVuZV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDY0Ljk5MyA2NS4wMjQiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDY0Ljk5MyA2NS4wMjQ7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIHN0eWxlPSJmaWxsOiNFNEU0RTQ7IiBkPSJNNDYuNTcxLDI3LjY0MXYyMy4xMzNIMTQuMjVWMTguNDUzaDIzLjExOGMtMC45NTYtMi4xODMtMS40OTQtNC41OS0xLjQ5NC03LjEyNg0KCWMwLTIuNTM1LDAuNTM4LTQuOTQyLDEuNDk0LTcuMTI0aC02Ljk1N0gwdjQ5LjQ5M2wxLjYxOCwxLjYxOEwwLDUzLjY5NmMwLDYuMjU2LDUuMDY4LDExLjMyNiwxMS4zMjQsMTEuMzI4djBoMTkuMDg3aDMwLjQxMlYyNy42MTENCgljLTIuMTkxLDAuOTY0LTQuNjA5LDEuNTA5LTcuMTU3LDEuNTA5QzUxLjE0MiwyOS4xMiw0OC43NDYsMjguNTg4LDQ2LjU3MSwyNy42NDF6Ii8+DQo8Y2lyY2xlIHN0eWxlPSJmaWxsOiM5ODk4OTg7IiBjeD0iNTMuNjY2IiBjeT0iMTEuMzI4IiByPSIxMS4zMjgiLz4NCjwvc3ZnPg0K';
	}
}
