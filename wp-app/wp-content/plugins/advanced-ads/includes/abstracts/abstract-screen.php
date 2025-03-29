<?php
/**
 * Abstracts Screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Abstracts;

use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Abstracts Screen.
 */
abstract class Screen {

	/**
	 * Hold hook.
	 *
	 * @var string
	 */
	private $hook = '';

	/**
	 * Hold tabs.
	 *
	 * @var array
	 */
	private $tabs = [];

	/**
	 * Get the hook.
	 *
	 * @return string
	 */
	public function get_hook(): string {
		return $this->hook;
	}

	/**
	 * Set the hook.
	 *
	 * @param string $hook Hook to set.
	 *
	 * @return void
	 */
	public function set_hook( $hook ): void {
		$this->hook = $hook;
	}

	/**
	 * Get the tabs.
	 *
	 * @return array
	 */
	public function get_tabs(): array {
		return $this->tabs;
	}

	/**
	 * Set the tabs.
	 *
	 * @param array $tabs Array of screen tabs.
	 *
	 * @return void
	 */
	public function set_tabs( $tabs ): void {
		$this->tabs = apply_filters( 'advanced-ads-screen-tabs-' . $this->get_id(), $tabs );
	}

	/**
	 * Screen unique id.
	 *
	 * @return string
	 */
	abstract public function get_id(): string;

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	abstract public function register_screen(): void;

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {}

	/**
	 * Display screen content.
	 *
	 * @return void
	 */
	public function display(): void {}

	/**
	 * Get the order value.
	 *
	 * @return int The order value, which is 10.
	 */
	public function get_order(): int {
		return 10;
	}

	/**
	 * Get current tab id.
	 *
	 * @return string
	 */
	public function get_current_tab_id(): string {
		$first = current( array_keys( $this->tabs ) );

		return Params::get( 'sub_page', $first );
	}

	/**
	 * Get admin page header
	 *
	 * @param array $args Arguments to be used in the template.
	 *
	 * @return void
	 */
	public function get_header( $args = [] ): void {
		$args = wp_parse_args(
			$args,
			[
				'manual_url' => '',
				'screen'     => get_current_screen(),
			]
		);

		extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		include ADVADS_ABSPATH . 'views/admin/ui/header.php';
	}

	/**
	 * Render tabs menu
	 *
	 * @param array $args Arguments to be used in the template.
	 *
	 * @return void
	 */
	public function get_tabs_menu( $args = [] ): void { // phpcs:ignore
		$tabs   = $this->tabs;
		$active = $this->get_current_tab_id();

		include ADVADS_ABSPATH . 'views/admin/ui/header-tabs.php';
	}

	/**
	 * Render tabs content
	 *
	 * @param array $args Arguments to be used in the template.
	 *
	 * @return void
	 */
	public function get_tab_content( $args = [] ): void { // phpcs:ignore
		$active = $this->get_current_tab_id();

		echo '<div class="advads-tab-content">';
		if ( isset( $this->tabs[ $active ]['callback'] ) ) {
			call_user_func( $this->tabs[ $active ]['callback'] );
		} elseif ( isset( $this->tabs[ $active ]['filename'] ) ) {
			include ADVADS_ABSPATH . $this->tabs[ $active ]['filename'];
		}

		echo '</div>';
	}
}
