<?php
/**
 * Frontend Ad Renderer.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Frontend;

use AdvancedAds\Widget;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend Ad Renderer.
 */
class Ad_Renderer implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'advanced-ads-frontend', [ $this, 'init' ] );
		add_action( 'widgets_init', [ $this, 'register_widget' ] );
	}

	/**
	 * Inject ads into various sections of our site
	 *
	 * @return void
	 */
	public function init(): void {
		// TODO: move priority to 0 for head.
		add_action( 'wp_head', [ $this, 'inject_header' ], 20 );
		// TODO: move priority to 9999 for footer.
		add_action( 'wp_footer', [ $this, 'inject_footer' ], 20 );
	}

	/**
	 * Injected ad into header
	 *
	 * @return void
	 */
	public function inject_header(): void {
		$placements = wp_advads_get_placements_by_types( 'header' );
		foreach ( $placements as $placement ) {
			the_ad_placement( $placement->get_id() );
		}
	}

	/**
	 * Injected ads into footer
	 *
	 * @return void
	 */
	public function inject_footer(): void {
		$placements = wp_advads_get_placements_by_types( 'footer' );
		foreach ( $placements as $placement ) {
			the_ad_placement( $placement->get_id() );
		}
	}

	/**
	 * Register the Advanced Ads widget
	 *
	 * @return void
	 */
	public function register_widget(): void {
		register_widget( Widget::class );
	}
}
