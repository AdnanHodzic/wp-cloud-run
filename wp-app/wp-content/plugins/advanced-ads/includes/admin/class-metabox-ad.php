<?php
/**
 * The class is responsible for adding metaboxes to the ad edit screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin;

use Advanced_Ads_AdSense_Data;
use Advanced_Ads_AdSense_Admin;
use Advanced_Ads_Ad_Type_Adsense;
use AdvancedAds\Constants;
use AdvancedAds\Utilities\Validation;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Ad Metabox.
 */
class Metabox_Ad implements Integration_Interface {

	/**
	 * Ad being shown on the screen.
	 *
	 * @var Ad|null
	 */
	protected $ad = null;

	/**
	 * Our boxes ids.
	 *
	 * @var array
	 */
	protected $meta_box_ids = [
		'advads-pro-pitch',
		'advads-tracking-pitch',
		'revisionsdiv',
		'advanced_ads_groupsdiv',
	];

	/**
	 * Hold metaboxes objects
	 *
	 * @var array
	 */
	protected $metaboxes = [];

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		$this->register_metaboxes();

		add_action( 'add_meta_boxes_' . Constants::POST_TYPE_AD, [ $this, 'add_meta_boxes' ] );
		add_filter( 'hidden_meta_boxes', [ $this, 'unhide_meta_boxes' ], 10, 2 );
		add_filter( 'postbox_classes_advanced_ads_ad-types-box', [ $this, 'add_classes_ad_type' ] );
		add_filter( 'pre_wp_unique_post_slug', [ $this, 'pre_wp_unique_post_slug' ], 10, 5 );

		add_filter( 'wp_insert_post_data', [ $this, 'set_post_title' ] );
		add_action( 'save_post_advanced_ads', [ $this, 'save' ], 10, 2 );
	}

	/**
	 * Pre-fetch any data for the screen.
	 *
	 * @param int $post_id Post ID being shown.
	 *
	 * @return void
	 */
	protected function prepare_ad( $post_id ): void {
		if ( empty( $this->ad ) || $this->ad->get_id() !== $post_id ) {
			$this->ad = wp_advads_get_ad( $post_id );
		}
	}

	/**
	 * Add "close" class to collapse the ad-type metabox after ad was saved first
	 *
	 * @param array $classes Metabox classes.
	 *
	 * @return array
	 */
	public function add_classes_ad_type( $classes = [] ): array {
		global $post;

		if (
			isset( $post->ID ) &&
			'publish' === $post->post_status &&
			! in_array( 'closed', $classes, true )
		) {
			$classes[] = 'closed';
		}

		return $classes;
	}

	/**
	 * Force all AA related meta boxes to stay visible
	 *
	 * @param array     $hidden An array of hidden meta boxes.
	 * @param WP_Screen $screen Current screen instance.
	 *
	 * @return array
	 */
	public function unhide_meta_boxes( $hidden, $screen ): array {
		if ( ! isset( $screen->id ) || 'advanced_ads' !== $screen->id ) {
			return $hidden;
		}

		return array_diff( $hidden, (array) apply_filters( 'advanced-ads-unhide-meta-boxes', $this->meta_box_ids ) );
	}

	/**
	 * Add meta boxes
	 *
	 * @return void
	 */
	public function add_meta_boxes(): void {
		foreach ( $this->metaboxes as $metabox ) {
			$metabox->register( $this );
		}

		add_filter( 'wp_dropdown_cats', [ $this, 'remove_parent_group_dropdown' ], 10, 2 );
	}

	/**
	 * Display metaboxes by their id.
	 *
	 * @param WP_Post $post Post instance.
	 * @param array   $box  Meta box information.
	 *
	 * @return void
	 */
	public function display( $post, $box ): void {
		$this->prepare_ad( $post->ID );
		$ad      = $this->ad;
		$metabox = $this->metaboxes[ $box['id'] ] ?? false;

		if ( $metabox ) {
			$this->display_warnings( $box );
			$view = $metabox->get_view( $ad );
			if ( $view ) {
				include $view;
			}
			$this->display_handle_links( $metabox );
		}
	}

	/**
	 * Remove parent group dropdown from ad group taxonomy
	 *
	 * @param string $output    Parent group dropdown HTML.
	 * @param array  $arguments Additional parameters.
	 *
	 * @return string
	 */
	public function remove_parent_group_dropdown( $output, $arguments ): string {
		if ( 'newadvanced_ads_groups_parent' === $arguments['name'] ) {
			$output = '';
		}

		return $output;
	}

	/**
	 * Create a unique across all post types slug for the ad.
	 * Almost all code here copied from `wp_unique_post_slug()`.
	 *
	 * @param string $override_slug Short-circuit return value.
	 * @param string $slug The desired slug (post_name).
	 * @param int    $post_id Post ID.
	 * @param string $post_status The post status.
	 * @param string $post_type Post type.
	 *
	 * @return string|null
	 */
	public function pre_wp_unique_post_slug( $override_slug, $slug, $post_id, $post_status, $post_type ) {
		global $wpdb, $wp_rewrite;

		// Early bail!!
		if ( Constants::POST_TYPE_AD !== $post_type ) {
			return $override_slug;
		}

		$feeds = $wp_rewrite->feeds;
		if ( ! is_array( $feeds ) ) {
			$feeds = [];
		}

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		$check_sql       = "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND ID != %d LIMIT 1";
		$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $slug, $post_id ) );

		if ( $post_name_check || in_array( $slug, $feeds, true ) || 'embed' === $slug ) {
			$suffix = 2;
			do {
				$alt_post_name   = substr( $slug, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
				$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_id ) );
				++$suffix;
			} while ( $post_name_check );
			$override_slug = $alt_post_name;
		}
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

		return $override_slug;
	}

	/**
	 * Prepare main post data for ads when being saved.
	 *
	 * @param array $data An array of slashed post data.
	 *
	 * @return array
	 */
	public function set_post_title( $data ): array {
		if (
			Constants::POST_TYPE_AD === $data['post_type'] &&
			'' === $data['post_title']
		) {
			$created_time = function_exists( 'wp_date' )
				? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) )
				: $data['post_date'];

			/* translators: %s is the time the ad was first saved. */
			$data['post_title'] = sprintf( __( 'Ad created on %s', 'advanced-ads' ), $created_time );
		}

		return $data;
	}

	/**
	 * Save the ad post type to be saved.
	 *
	 * @param int    $post_id Post ID.
	 * @param object $post    Post object.
	 *
	 * @return void
	 */
	public function save( $post_id, $post ): void {
		$post_id = absint( $post_id );

		if (
			! Conditional::user_can( 'advanced_ads_edit_ads' ) ||
			! Validation::check_save_post( $post_id, $post )
		) {
			return;
		}

		// If new post.
		$post_data = self::get_post_data();
		if ( empty( $post_data ) ) {
			return;
		}

		// Maybe there is a type change so force it.
		$ad = wp_advads_get_ad( $post_id, wp_unslash( $post_data['type'] ) );
		$ad->set_props( $post_data );

		remove_action( 'save_post_advanced_ads', [ $this, 'save' ], 10, 2 );
		$ad->save();
		add_action( 'save_post_advanced_ads', [ $this, 'save' ], 10, 2 );
	}

	/**
	 * Get posted data for ad.
	 *
	 * @return array
	 */
	public static function get_post_data(): array {
		$data = Params::post( 'advanced_ad', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data = array_merge( $data ?? [], $data['output'] ?? [] );
		unset( $data['output'] );

		// Filters to manipulate options or add more to be saved.
		$data = apply_filters_deprecated(
			'advanced-ads-save-options',
			[ $data, null ],
			'1.48.2',
			'advanced-ads-ad-pre-save',
			__( 'Use advanced-ads-ad-pre-save action', 'advanced-ads' )
		);
		$data = apply_filters_deprecated(
			'advanced-ads-ad-settings-pre-save',
			[ $data ],
			'1.48.2',
			'advanced-ads-ad-pre-save',
			__( 'Use advanced-ads-ad-pre-save action', 'advanced-ads' )
		);

		return $data;
	}

	/**
	 * Render links for handles
	 *
	 * @param object $metabox Metabox instance.
	 *
	 * @return void
	 */
	private function display_handle_links( $metabox ): void {
		// Early bail!!
		if ( ! $metabox->get_handle_link() ) {
			return;
		}

		?>
		<span class="advads-hndlelinks hidden">
			<?php
			$link = $metabox->get_handle_link();
			if ( is_array( $link ) ) {
				$link = join( '', $link );
			}

			echo wp_kses(
				$link,
				[
					'a' => [
						'target' => [],
						'href'   => [],
						'class'  => [],
					],
				]
			);
			?>
		</span>
		<?php

		if ( 'ad-targeting-box' === $metabox->get_box_id() ) :
			?>
		<div class="advads-video-link-container" data-videolink='<iframe width="420" height="315" src="https://www.youtube-nocookie.com/embed/VjfrRl5Qn4I?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>'></div>
			<?php
		endif;
	}

	/**
	 * Render links for handles
	 *
	 * @param array $box Meta box information.
	 *
	 * @return void
	 */
	private function display_warnings( $box ): void {
		$warnings = [];
		$box_id   = $box['id'];

		/**
		 *  List general notices
		 *  elements in $warnings contain [text] and [class] attributes.
		 */
		if ( 'ad-parameters-box' === $box_id ) {
			$warnings[] = [
				'text'  => Advanced_Ads_AdSense_Admin::get_auto_ads_messages()[ Advanced_Ads_AdSense_Data::get_instance()->is_page_level_enabled() ? 'enabled' : 'disabled' ],
				'class' => 'advads-auto-ad-in-ad-content hidden advads-notice-inline advads-error',
			];

			// Show warning if ad contains https in parameters box.
			$https_message = Validation::is_ad_https( $this->ad );
			if ( $https_message ) {
				$warnings[] = [
					'text'  => $https_message,
					'class' => 'advads-ad-notice-https-missing advads-notice-inline advads-error',
				];
			}
		}

		// Let users know that they could use the Google AdSense ad type
		// when they enter an AdSense code.
		if ( 'ad-parameters-box' === $box_id && $this->has_adsense_on_plain_content_type() ) {
			$adsense_auto_ads = Advanced_Ads_AdSense_Data::get_instance()->is_page_level_enabled();
			$warnings[]       = [
				'class' => 'advads-adsense-found-in-content advads-notice-inline advads-error',
				'text'  => sprintf(
					/* translators: %1$s opening button tag, %2$s closing button tag. */
					esc_html__( 'This looks like an AdSense ad. Switch the ad type to “AdSense ad” to make use of more features. %1$sSwitch to AdSense ad%2$s.', 'advanced-ads' ),
					'<button class="button-secondary" id="switch-to-adsense-type">',
					'</button>'
				),
			];
		}

		$warnings = apply_filters( 'advanced-ads-ad-notices', $warnings, $box, $this->ad );

		echo '<ul id="' . esc_attr( $box_id ) . '-notices" class="advads-metabox-notices">';
		foreach ( $warnings as $warning ) {
			if ( isset( $warning['text'] ) ) {
				printf(
					'<li class="%s">%s</li>',
					esc_attr( $warning['class'] ?? '' ),
					$warning['text'], // phpcs:ignore
				);
			}
		}
		echo '</ul>';
	}

	/**
	 * Is using Google Adsense on plain and content ad type
	 *
	 * @return boolean
	 */
	private function has_adsense_on_plain_content_type(): bool {
		$content = $this->ad->get_content();
		if (
			Advanced_Ads_Ad_Type_Adsense::content_is_adsense( $content ) &&
			$this->ad->is_type( [ 'plain', 'content' ] ) &&
			false === strpos( $content, 'enable_page_level_ads' ) &&
			! preg_match( '/script[^>]+data-ad-client=/', $content )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Register metaboxes
	 *
	 * @return void
	 */
	private function register_metaboxes(): void {
		$this->register_metabox( Metaboxes\Ad_Usage::class );
		$this->register_metabox( Metaboxes\Ad_Types::class );
		$this->register_metabox( Metaboxes\Ad_Parameters::class );
		$this->register_metabox( Metaboxes\Ad_Layout::class );
		$this->register_metabox( Metaboxes\Ad_Targeting::class );
		$this->register_metabox( Metaboxes\Ad_Adsense::class );
	}

	/**
	 * Register metabox
	 *
	 * @param string $metabox_class Metabox class name.
	 *
	 * @return void
	 */
	private function register_metabox( $metabox_class ): void {
		$metabox                        = new $metabox_class();
		$metabox_id                     = $metabox->get_box_id();
		$this->meta_box_ids[]           = $metabox_id;
		$this->metaboxes[ $metabox_id ] = $metabox;
	}
}
