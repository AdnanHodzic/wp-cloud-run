<?php
/**
 * Modules TrafficCop Page Parser.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Modules\OneClick;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Modules TrafficCop Page Parser.
 *
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */
class Page_Parser implements Integration_Interface {

	/**
	 * Hold page.
	 *
	 * @var string
	 */
	private $page = '';

	/**
	 * Get Parser Instance
	 *
	 * @return Page_Parser
	 */
	public static function get_instance() {
		static $instance;

		if ( null === $instance ) {
			$instance = new Page_Parser();
			$instance->hooks();
		}

		return $instance;
	}

	/**
	 * Hook into WordPress.
	 */
	public function hooks() {
		add_action( 'template_redirect', [ $this, 'start_buffer' ], -9999 );
		add_action( 'wp_footer', [ $this, 'flush_page' ], 9999 );
	}

	/**
	 * Get page
	 *
	 * @return string
	 */
	public function get_page() {
		return $this->page;
	}

	/**
	 * Start of buffer.
	 *
	 * @return void
	 */
	public function start_buffer() {
		ob_start( [ $this, 'parse' ] );
	}

	/**
	 * Parse page for script tag
	 *
	 * @param string $buffer Page buffer.
	 *
	 * @return string
	 */
	public function parse( $buffer ): string {
		$this->page = $buffer;
		$this->loop_script_tags();
		$this->page = apply_filters( 'pubguru_current_page', $this->page ); // phpcs:ignore

		return $this->page;
	}

	/**
	 * Flush page after footer
	 *
	 * @return void
	 */
	public function flush_page() {
		$buffer_status = ob_get_status();

		if (
			! empty( $buffer_status ) &&
			1 === $buffer_status['type'] &&
			get_class( $this ) . '::parse' === $buffer_status['name']
		) {
			ob_end_flush();
		}
	}

	/**
	 * Loop through script tags.
	 *
	 * @return void
	 */
	public function loop_script_tags() {
		// Early bail!!
		if ( ! has_filter( 'pubguru_page_script_tag' ) ) {
			return;
		}

		$scripts = $this->get_script_tags();

		foreach ( $scripts as $script ) {
			$find    = $script;
			$replace = apply_filters( 'pubguru_page_script_tag', $script );
			if ( false !== $replace ) {
				$this->page = str_replace( $find, $replace, $this->page );
			}
		}
	}

	/**
	 * Get script tags only.
	 *
	 * @return array
	 */
	private function get_script_tags() {
		$matches = [];
		preg_match_all( '/<script[\s\S]*?>[\s\S]*?<\/script>/i', $this->page, $matches );

		return isset( $matches[0] ) ? $matches[0] : [];
	}
}
