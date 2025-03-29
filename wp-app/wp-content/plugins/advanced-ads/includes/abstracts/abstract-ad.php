<?php
/**
 * This class is serving as the base for various ad types and providing a foundation for defining common ad attributes and methods.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Abstracts;

use Advanced_Ads;
use Advanced_Ads_Inline_Css;
use Advanced_Ads_Utils;
use Advanced_Ads_Visitor_Conditions;
use AdvancedAds\Compatibility\Compatibility;
use AdvancedAds\Constants;
use AdvancedAds\Framework\Utilities\Arr;
use AdvancedAds\Framework\Utilities\Formatting;
use AdvancedAds\Framework\Utilities\Str;
use AdvancedAds\Frontend\Stats;
use AdvancedAds\Interfaces\Ad_Type;
use AdvancedAds\Traits;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Utilities\WordPress;
use DateInterval;
use DateTime;

defined( 'ABSPATH' ) || exit;

/**
 * Ad.
 */
abstract class Ad extends Data {

	use Traits\Entity;
	use Traits\Wrapper;

	/**
	 * This is the object type.
	 *
	 * @var string
	 */
	protected $object_type = 'ad';

	/**
	 * The label for the ad.
	 *
	 * @var string|null
	 */
	private $label = null;

	/**
	 * Hold groups within the ad
	 *
	 * @var Group[]
	 */
	private $groups = null;

	/**
	 * Wrapper for the ad.
	 *
	 * @var array|null
	 */
	protected $wrapper = null;

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @var array
	 */
	protected $data = [
		'title'            => '',
		'slug'             => '',
		'status'           => false,
		'author_id'        => 0,
		'content'          => '',
		'description'      => '',
		'width'            => 0,
		'height'           => 0,
		'visitors'         => [],
		'conditions'       => [],
		'has_weekdays'     => false,
		'weekdays'         => [],
		'type'             => 'dummy',
		'url'              => '',
		'expiry_date'      => false,
		'allow_php'        => false,
		'allow_shortcodes' => false,
		'reserve_space'    => false,
		'debugmode'        => false,
		'wrapper-id'       => '',
		'wrapper-class'    => '',
		'position'         => 'none',
		'clearfix'         => false,
		'margin'           => [
			'top'    => 0,
			'right'  => 0,
			'bottom' => 0,
			'left'   => 0,
		],
	];

	/**
	 * Get the ad if ID is passed, otherwise the ad is new and empty.
	 *
	 * @param Ad|WP_Post|int $ad Ad to init.
	 */
	public function __construct( $ad = 0 ) {
		parent::__construct();

		$this->set_ad_id( $ad );
		$this->data_store = wp_advads_get_ad_repository();

		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/**
	 * Set the ad ID depending on what was passed.
	 *
	 * @param Ad|WP_Post|int $ad Ad instance, post instance or numeric.
	 *
	 * @return void
	 */
	private function set_ad_id( $ad ): void {
		if ( is_numeric( $ad ) && $ad > 0 ) {
			$this->set_id( $ad );
		} elseif ( $ad instanceof self ) {
			$this->set_id( absint( $ad->get_id() ) );
		} elseif ( ! empty( $ad->ID ) ) {
			$this->set_id( absint( $ad->ID ) );
		} else {
			$this->set_object_read( true );
		}
	}

	/* Getter ------------------- */

	/**
	 * Get description.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_description( $context = 'view' ): string {
		return $this->get_prop( 'description', $context );
	}

	/**
	 * Get url.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_url( $context = 'view' ): string {
		return $this->get_prop( 'url', $context );
	}

	/**
	 * Get the expiry date of the ad.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_expiry_date( $context = 'view' ): int {
		return $this->get_prop( 'expiry_date', $context );
	}

	/**
	 * Get the weekdays.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_weekdays( $context = 'view' ): array {
		return $this->get_prop( 'weekdays', $context );
	}

	/**
	 * Get the width of the ad in pixels.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_width( $context = 'view' ): int {
		return $this->get_prop( 'width', $context );
	}

	/**
	 * Get the height of the ad in pixels.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_height( $context = 'view' ): int {
		return $this->get_prop( 'height', $context );
	}

	/**
	 * Get the display conditions for the ad.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_display_conditions( $context = 'view' ): array {
		return $this->get_prop( 'conditions', $context );
	}

	/**
	 * Get the visitor conditions for the ad.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_visitor_conditions( $context = 'view' ): array {
		return array_filter(
			$this->get_prop( 'visitors', $context ),
			function ( $value ) {
				return 'unknown' !== $value['type'];
			}
		);
	}

	/**
	 * Get clearfix.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function get_clearfix( $context = 'view' ): bool {
		return $this->get_prop( 'clearfix', $context );
	}

	/**
	 * Get position.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_position( $context = 'view' ): string {
		return $this->get_prop( 'position', $context );
	}

	/**
	 * Get the margin setting for the ad.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_margin( $context = 'view' ): array {
		return $this->get_prop( 'margin', $context );
	}

	/**
	 * Get the wrapper classes for the ad.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_weight( $context = 'view' ): int {
		return $this->get_prop( 'weight', $context );
	}

	/**
	 * Retrieves the number of clicks for the ad.
	 *
	 * @return int
	 */
	public function get_clicks(): int {
		$post = get_post();

		if ( isset( $post->clicks ) ) {
			return absint( $post->clicks );
		}

		global $wpdb;
		$clicks = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				'SELECT SUM(count) FROM ' . $wpdb->prefix . 'advads_clicks WHERE ad_id = %d',
				$this->get_id()
			)
		);

		return absint( $clicks );
	}

	/**
	 * Retrieves the number of impressions for the ad.
	 *
	 * @return int
	 */
	public function get_impressions(): int {
		$post = get_post();

		if ( isset( $post->impressions ) ) {
			return absint( $post->impressions );
		}

		global $wpdb;

		$impressions = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				'SELECT SUM(count) FROM ' . $wpdb->prefix . 'advads_impressions WHERE ad_id = %d',
				$this->get_id()
			)
		);

		return absint( $impressions );
	}

	/**
	 * Retrieves the number of ctr for the ad.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int|string
	 */
	public function get_ctr( $context = 'view' ) {
		$post = get_post();
		$ctr  = $post->ctr ?? 0;

		if ( ! $ctr && $this->get_impressions() > 0 ) {
			$ctr = $this->get_clicks() / $this->get_impressions();
		}

		return 'view' === $context ? number_format_i18n( 100 * $ctr, 2 ) : $ctr;
	}

	/* Setter ------------------- */

	/**
	 * Set display conditions.
	 *
	 * @param string $conditions Ad conditions.
	 *
	 * @return void
	 */
	public function set_display_conditions( $conditions ): void {
		$this->set_prop( 'conditions', $conditions );
	}

	/**
	 * Set conditions.
	 *
	 * @param string $conditions Ad conditions.
	 *
	 * @return void
	 */
	public function set_visitor_conditions( $conditions ): void {
		$this->set_prop( 'visitors', $conditions );
	}

	/**
	 * Set clearfix.
	 *
	 * @param string $clearfix Ad clearfix.
	 *
	 * @return void
	 */
	public function set_clearfix( $clearfix ): void {
		$this->set_prop( 'clearfix', Formatting::string_to_bool( $clearfix ) );
	}

	/**
	 * Set position.
	 *
	 * @param string $position Ad position.
	 *
	 * @return void
	 */
	public function set_position( $position ): void {
		$this->set_prop( 'position', empty( $position ) ? 'none' : $position );
	}

	/**
	 * Set margin.
	 *
	 * @param array $margin Ad margin.
	 *
	 * @return void
	 */
	public function set_margin( $margin ): void {
		$margin = [
			'top'    => intval( $margin['top'] ?? 0 ),
			'right'  => intval( $margin['right'] ?? 0 ),
			'bottom' => intval( $margin['bottom'] ?? 0 ),
			'left'   => intval( $margin['left'] ?? 0 ),
		];

		$this->set_prop( 'margin', $margin );
	}

	/**
	 * Set expiry date.
	 *
	 * @param array|string $expiry_date Ad expiry date.
	 *
	 * @return void
	 */
	public function set_expiry_date( $expiry_date ): void {
		if ( is_array( $expiry_date ) ) {
			$expiry_date = $this->normalize_expiry_date( $expiry_date );
		}

		$this->set_prop( 'expiry_date', $expiry_date );
	}

	/**
	 * Set weekdays.
	 *
	 * @param array $weekdays Ad weekdays.
	 *
	 * @return void
	 */
	public function set_weekdays( $weekdays ): void {
		$this->set_prop( 'weekdays', $weekdays );
	}

	/**
	 * Set description.
	 *
	 * @param string $description Ad description.
	 *
	 * @return void
	 */
	public function set_description( $description ): void {
		$this->set_prop( 'description', $description );
	}

	/**
	 * Set url.
	 *
	 * @param string $url Ad url.
	 *
	 * @return void
	 */
	public function set_url( $url ): void {
		global $pagenow;

		// If the tracking add-on is not active.
		// If this is not the ad edit page.
		if ( ! defined( 'AAT_VERSION' ) && ! in_array( $pagenow, [ 'post.php', 'post-new.php' ], true ) ) {
			$placeholders = [
				'[POST_ID]',
				'[POST_SLUG]',
				'[CAT_SLUG]',
				'[AD_ID]',
			];

			$url = str_replace( $placeholders, '', $url );
		}

		$this->set_prop( 'url', trim( $url ) );
	}

	/**
	 * Set the width.
	 *
	 * @param float|string $width Total width.
	 */
	public function set_width( $width ) {
		$this->set_prop( 'width', '' === $width ? 0 : absint( $width ) );
	}

	/**
	 * Set the height.
	 *
	 * @param float|string $height Total height.
	 *
	 * @return void
	 */
	public function set_height( $height ): void {
		$this->set_prop( 'height', '' === $height ? 0 : absint( $height ) );
	}

	/**
	 * Set if debug mode is enabled.
	 *
	 * @param bool $enabled Is Enabled.
	 *
	 * @return void
	 */
	public function set_debugmode( $enabled ): void {
		$this->set_prop( 'debugmode', Formatting::string_to_bool( $enabled ) );
	}

	/**
	 * Set if php is allowed.
	 *
	 * @param bool|string $allowed Allowed.
	 *
	 * @return void
	 */
	public function set_allow_php( $allowed ): void {
		$this->set_prop( 'allow_php', Formatting::string_to_bool( $allowed ) );
	}

	/**
	 * Set if shortcodes is allowed.
	 *
	 * @param bool|string $allowed Allowed.
	 *
	 * @return void
	 */
	public function set_allow_shortcodes( $allowed ): void {
		$this->set_prop( 'allow_shortcodes', Formatting::string_to_bool( $allowed ) );
	}

	/**
	 * Set add  sizes is allowed.
	 *
	 * @param bool|string $allowed Allowed.
	 *
	 * @return void
	 */
	public function set_reserve_space( $allowed ): void {
		$this->set_prop( 'reserve_space', Formatting::string_to_bool( $allowed ) );
	}

	/**
	 * Set if has weekdays.
	 *
	 * @param bool|string $has_weekdays Has weekdays.
	 *
	 * @return void
	 */
	public function set_has_weekdays( $has_weekdays ): void {
		$this->set_prop( 'has_weekdays', Formatting::string_to_bool( $has_weekdays ) );
	}

	/* Conditional ------------------- */

	/**
	 * Check if debug mode is enabled within the ad.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function is_debug_mode( $context = 'view' ): bool {
		return $this->get_prop( 'debugmode', $context );
	}

	/**
	 * Check if PHP code execution is allowed within the ad.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function is_php_allowed( $context = 'view' ): bool {
		return $this->get_prop( 'allow_php', $context );
	}

	/**
	 * Check if shortcode execution is allowed within the ad.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function is_shortcode_allowed( $context = 'view' ): bool {
		return $this->get_prop( 'allow_shortcodes', $context );
	}

	/**
	 * Check if has weekdays.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function has_weekdays( $context = 'view' ): bool {
		return $this->get_prop( 'has_weekdays', $context );
	}

	/**
	 * Check if space is reserved.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function is_space_reserved( $context = 'view' ): bool {
		return $this->get_prop( 'reserve_space', $context );
	}

	/**
	 * Check whether this ad is expired.
	 *
	 * @return bool
	 */
	public function is_expired(): bool {
		if ( $this->get_expiry_date() <= 0 || $this->get_expiry_date() > time() ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the ad is placed in the header.
	 *
	 * @return bool
	 */
	public function is_head_placement(): bool {
		return null !== $this->get_parent() && $this->get_parent()->is_type( 'header' );
	}

	/**
	 * Check if the ad is a top-level ad.
	 *
	 * @return bool
	 */
	public function is_top_level(): bool {
		return ! $this->get_parent() || is_a_placement( $this->get_parent() );
	}

	/**
	 * Determines whether the ad can be displayed.
	 *
	 * @param array $check_options check options.
	 *
	 * @return bool True if the ad can be displayed, false otherwise.
	 */
	public function can_display( $check_options = [] ): bool {
		$check_options = wp_parse_args(
			$check_options,
			[
				'passive_cache_busting' => false,
				'ignore_debugmode'      => false,
			]
		);

		// Prevent ad to show up through wp_head, if this is not a header placement.
		if ( doing_action( 'wp_head' ) && ! $this->is_head_placement() && ! Compatibility::can_inject_during_wp_head() ) {
			return false;
		}

		// Check If the current ad is requested using a shortcode placed in the content of the current ad.
		$shortcode_ad_id = $this->get_prop( 'shortcode_ad_id' );
		if ( $shortcode_ad_id && absint( $shortcode_ad_id ) === $this->get_id() ) {
			return false;
		}

		/**
		 * Allows bypassing server side visitor conditions checks or ignoring debug mode
		 *
		 * @param array $check_options can display check options.
		 */
		$check_options = apply_filters( 'advanced-ads-can-display-ad-check-options', $check_options );

		// Force ad display if debug mode is enabled.
		if ( $this->is_debug_mode() && ! $check_options['ignore_debugmode'] ) {
			return true;
		}

		if ( ! $check_options['passive_cache_busting'] ) {
			// Don’t display ads that are not published or private for users not logged in.
			if ( ! $this->is_status( 'publish' ) && ! ( $this->is_status( 'private' ) && is_user_logged_in() ) ) {
				return false;
			}

			if ( ! $this->can_display_by_visitor() ) {
				return false;
			}
		} elseif ( ! $this->is_status( 'publish' ) ) {
			return false;
		}

		if ( $this->is_expired() ) {
			return false;
		}

		$can_display = apply_filters( 'advanced-ads-can-display-ad', true, $this, $check_options );

		// Add own conditions to flag output as possible or not.
		return apply_filters_deprecated( 'advanced-ads-can-display', [ $can_display, $this, $check_options ], '2.0.0', 'advanced-ads-can-display-ad', 'Use advanced-ads-can-display-ad instead.' );
	}

	/* Additional Methods ------------------- */

	/**
	 * Prepares the output for the group.
	 *
	 * @return string The prepared output.
	 */
	public function prepare_output(): string {
		$ad_args = $this->get_prop( 'ad_args' ) ?? [];
		$output  = $this->prepare_frontend_output();

		// Don’t deliver anything, if main ad content is empty.
		if ( empty( $output ) ) {
			return '';
		}

		$output_options = $this->get_prop( 'output_options' ) ?? [];
		$global_output  = $output_options['global_output'] ?? ! isset( $ad_args['global_output'] ) || $ad_args['global_output'];
		$this->set_prop_temp( 'global_output', $global_output );

		$output_options['global_output'] = $global_output;

		if ( ! $this->is_head_placement() ) {
			// Filter to manipulate the output before the wrapper is added.
			$output = apply_filters( 'advanced-ads-output-inside-wrapper', $output, $this );
			$output = $this->add_wrapper( $output, $global_output );

			// Add a clearfix, if set.
			if (
				( $this->is_top_level() && ! empty( $ad_args['placement_clearfix'] ) )
				|| $this->get_clearfix()
			) {
				$output .= '<br style="clear: both; display: block; float: none;"/>';
			}
		}

		// Add the ad to the global output array.
		if ( $output_options['global_output'] ) {
			Stats::get()->add_entity( 'ad', $this->get_id(), $this->get_title() );
		}

		do_action_deprecated(
			'advanced-ads-output',
			[ $this, $output, $output_options ],
			'2.0.0',
			'advanced-ads-ad-output-ready',
			'Use advanced-ads-ad-output-ready instead.'
		);

		return apply_filters_deprecated(
			'advanced-ads-output-final',
			[ $output, $this, $output_options ],
			'2.0.0',
			'advanced-ads-ad-output',
			'Use advanced-ads-ad-output instead.'
		);
	}

	/**
	 * Get groups
	 *
	 * @return bool|array
	 */
	public function get_groups() {
		if ( null === $this->groups ) {
			$this->groups = [];
			$terms        = wp_get_object_terms( $this->get_id(), Constants::TAXONOMY_GROUP );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$this->groups[ $term->term_id ] = wp_advads_get_group( $term );
				}
			}
		}

		return $this->groups;
	}

	/**
	 * Get ad type object
	 *
	 * @return Ad_Type|bool
	 */
	public function get_type_object() {
		if ( ! wp_advads_has_ad_type( $this->get_type() ) ) {
			wp_advads_create_ad_type( $this->get_type() );
		}

		return wp_advads_get_ad_type( $this->get_type() );
	}

	/**
	 * Get ad schedule details
	 *
	 * @return array
	 */
	public function get_ad_schedule_details(): array {
		$expiry_date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
		$status_strings     = [];
		$post_start         = get_post_time( 'U', true, $this->get_id() );
		$html_classes       = 'advads-filter-timing';

		$status_type = get_post_status( $this->get_id() ) ?? 'published';
		if ( 'publish' === $status_type ) {
			$status_type = 'published';
		}

		if ( $post_start > time() ) {
			$status_type   = 'future';
			$html_classes .= ' advads-filter-future';

			/* translators: %s is a date. */
			$status_strings[] = sprintf( __( 'starts %s', 'advanced-ads' ), get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $post_start ), $expiry_date_format ) );
		}

		if ( $this->get_expiry_date() ) {
			$expiry      = $this->get_expiry_date();
			$expiry_date = date_create( '@' . $expiry );
			$expiry_date->setTimezone( WordPress::get_timezone() );
			$html_classes .= ' advads-filter-any-exp-date';

			$tz = ' ( ' . WordPress::get_timezone_name() . ' )';

			if ( $expiry > time() ) {
				$status_type = 'expiring';
				/* translators: %s is a date. */
				$status_strings[] = sprintf( __( 'expires %s', 'advanced-ads' ), $expiry_date->format( $expiry_date_format ) ) . $tz;
			} elseif ( $expiry <= time() ) {
				$status_type   = 'expired';
				$html_classes .= ' advads-filter-expired';

				/* translators: %s is a date. */
				$status_strings[] = sprintf( __( 'expired %s', 'advanced-ads' ), $expiry_date->format( $expiry_date_format ) ) . $tz;
			}
		}

		switch ( $status_type ) {
			case 'published':
				$status_strings[] = __( 'Published', 'advanced-ads' );
				break;
			case 'draft':
				$status_strings[] = __( 'Draft', 'advanced-ads' );
				break;
			case 'trash':
				$status_strings[] = __( 'Trashed', 'advanced-ads' );
				break;
		}

		return compact( 'post_start', 'status_type', 'status_strings', 'html_classes' );
	}

	/**
	 * Load a template with the information on ad expiry
	 *
	 * @return string
	 */
	public function get_ad_schedule_html(): string {
		[
			'post_start' => $post_start,
			'status_type' => $status_type,
			'status_strings' => $status_strings
		] = $this->get_ad_schedule_details();

		if ( empty( $status_strings ) ) {
			return '';
		}

		ob_start();
		include ADVADS_ABSPATH . 'views/admin/tables/ads/icon-status.php';

		return ob_get_clean();
	}

	/**
	 * Get the post edit link
	 *
	 * @return string|null
	 */
	public function get_edit_link() {
		return get_edit_post_link( $this->get_id(), 'other' );
	}

	/**
	 * Normalize expiry date
	 *
	 * @param array $expiry_date Expiry date.
	 *
	 * @return int
	 */
	private function normalize_expiry_date( $expiry_date ): int {
		$is_enabled = boolval( $expiry_date['enabled'] ?? false );
		// Early bail!!
		if ( ! $is_enabled ) {
			return 0;
		}

		$year   = absint( $expiry_date['year'] );
		$month  = absint( $expiry_date['month'] );
		$day    = absint( $expiry_date['day'] );
		$hour   = absint( $expiry_date['hour'] );
		$minute = absint( $expiry_date['minute'] );

		$expiration_date = date_create( sprintf( '%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, '00' ) );

		if ( ! $expiration_date ) {
			return 0;
		}

		$wp_time = new DateTime( 'now', wp_timezone() );
		$expiration_date->sub( new DateInterval( 'PT' . $wp_time->getOffset() . 'S' ) );
		$gm_date = $expiration_date->format( 'Y-m-d-H-i' );

		[ $year, $month, $day, $hour, $minute ] = explode( '-', $gm_date );

		return gmmktime( $hour, $minute, 0, $month, $day, $year );
	}

	/**
	 * Process shortcodes.
	 *
	 * @param string $output Ad content.
	 *
	 * @return string
	 */
	protected function do_shortcode( $output ): string {
		$ad_args                    = $this->temp_data;
		$ad_args['shortcode_ad_id'] = $this->get_id();
		$output                     = preg_replace(
			'/\[(the_ad_group|the_ad_placement|the_ad) /',
			'[$1 ad_args="' . rawurlencode( wp_json_encode( $ad_args ) ) . '" ',
			$output
		);

		return do_shortcode( $output );
	}

	/**
	 * Add a wrapper arount the ad content if wrapper information are given
	 *
	 * @since 1.1.4
	 *
	 * @param string $ad_content content of the ad.
	 * @param bool   $global_output Global output.
	 *
	 * @return string $wrapper ad within the wrapper
	 */
	private function add_wrapper( $ad_content = '', $global_output = false ): string {
		$ad_args         = $this->get_prop( 'ad_args' ) ?? [];
		$label           = $this->get_label();
		$wrapper         = $this->create_wrapper();
		$wrapper_options = apply_filters( 'advanced-ads-output-wrapper-options', $wrapper, $this );

		// Create another wrapper so that the label does not reduce the height of the ad wrapper.
		if ( $label && ! empty( $wrapper_options['style']['height'] ) ) {
			$height = [ 'style' => [ 'height' => $wrapper_options['style']['height'] ] ];
			unset( $wrapper_options['style']['height'] );
			$ad_content = '<div' . Advanced_Ads_Utils::build_html_attributes( $height ) . '>' . $ad_content . '</div>';
		}

		// Adds inline css to the wrapper.
		if ( ! empty( $ad_args['inline-css'] ) && $this->is_top_level() ) {
			$wrapper_options = ( new Advanced_Ads_Inline_Css() )->add_css( $wrapper_options, $ad_args['inline-css'], $global_output );
		}

		if (
			! defined( 'ADVANCED_ADS_DISABLE_EDIT_BAR' ) &&
			Conditional::user_can( 'advanced_ads_edit_ads' ) &&
			$this->is_parent_placement()
		) {
			ob_start();
			include ADVADS_ABSPATH . 'public/views/ad-edit-bar.php';
			$ad_content                      = trim( ob_get_clean() ) . $ad_content;
			$wrapper_options['data-title'][] = $this->get_tooltip_title();
		}

		// Ad Health Tool add class and attribute in to ads and group.
		if ( Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			// Add the 'highlight-wrapper' class to the ad wrapper.
			if ( ! $this->is_parent_group() ) {
				$wrapper_options['class'][] = wp_advads()->get_frontend_prefix() . 'highlight-wrapper';
			}
		}

		if (
			Str::is_empty( $this->get_wrapper_id() ) &&
			( [] === $wrapper_options || ! Arr::accessible( $wrapper_options ) )
		) {
			return $label . $ad_content;
		}

		// Create unique id if not yet given.
		if ( empty( $wrapper_options['id'] ) ) {
			$wrapper_options['id'] = $this->create_wrapper_id();
		}

		$wrapper_element = ! empty( $this->get_prop( 'inline_wrapper_element' ) ) ? 'span' : 'div';

		// Build the box.
		$wrapper  = '<' . $wrapper_element . Advanced_Ads_Utils::build_html_attributes( $wrapper_options ) . '>';
		$wrapper .= $label;
		$wrapper .= apply_filters( 'advanced-ads-output-wrapper-before-content', '', $this );
		$wrapper .= $ad_content;
		$wrapper .= apply_filters( 'advanced-ads-output-wrapper-after-content', '', $this );
		$wrapper .= '</' . $wrapper_element . '>';

		return $wrapper;
	}

	/**
	 * Creates a wrapper array for the group.
	 *
	 * @return array
	 */
	public function create_wrapper(): array {
		// Early bail!!
		if ( null !== $this->wrapper ) {
			return $this->wrapper;
		}

		$this->wrapper = [];

		if ( ! $this->is_head_placement() ) {
			$position     = $this->get_position();
			$use_position = false;

			if ( $this->is_top_level() ) {
				$classes = $this->get_prop( 'ad_args.output.class' );
				if ( $classes && is_array( $classes ) ) {
					$this->wrapper['class'] = $classes;
				}

				if ( $this->is_parent_placement() && 'default' !== $this->get_parent()->get_prop( 'placement_position' ) ) {
					$use_position = true;
					$position     = $this->get_parent()->get_prop( 'placement_position' );
				}
			}

			$this->get_wrapper_styles( $this->wrapper, $position, $use_position );

			// Add manual classes.
			if ( Str::is_non_empty( $this->get_wrapper_class() ) ) {
				$classes = explode( ' ', $this->get_wrapper_class() );

				foreach ( $classes as $_class ) {
					$this->wrapper['class'][] = sanitize_html_class( $_class );
				}
			}

			$this->wrapper = apply_filters( 'advanced-ads-set-wrapper', $this->wrapper, $this );
			if ( is_array( $this->wrapper ) && [] !== $this->wrapper && ! isset( $this->wrapper['id'] ) ) {
				$this->wrapper['id'] = $this->create_wrapper_id();
			}
		}

		return $this->wrapper;
	}

	/**
	 * Retrieves the label for the ad.
	 *
	 * @return string The label for the ad.
	 */
	private function get_label(): string {
		if ( null === $this->label ) {
			$ad_args = $this->get_prop( 'ad_args' );
			$state   = $ad_args['ad_label'] ?? 'default';
			$label   = Advanced_Ads::get_instance()->get_label( $this, $state );

			$this->label = $this->is_top_level() && $label ? $label : '';
		}

		return $this->label;
	}

	/**
	 * Check visitor conditions
	 *
	 * @return bool $can_display true if can be displayed in frontend based on visitor settings
	 * @since 1.1.0
	 */
	private function can_display_by_visitor() {
		$wp_the_query = $this->get_prop( 'wp_the_query' );
		if ( $wp_the_query && ! empty( $wp_the_query['is_feed'] ) ) {
			return true;
		}

		$conditions = $this->get_visitor_conditions();
		if ( empty( $conditions ) ) {
			return true;
		}

		$last_result = false;
		$length      = count( $conditions );

		for ( $i = 0; $i < $length; ++$i ) {
			$_condition = current( $conditions );
			// Ignore OR if last result was true.
			if ( $last_result && isset( $_condition['connector'] ) && 'or' === $_condition['connector'] ) {
				next( $conditions );
				continue;
			}

			$result      = Advanced_Ads_Visitor_Conditions::frontend_check( $_condition, $this );
			$last_result = $result;
			if ( ! $result ) {
				// return false only, if the next condition doesn’t have an OR operator.
				$next = next( $conditions );
				if ( ! isset( $next['connector'] ) || 'or' !== $next['connector'] ) {
					return false;
				}
			} else {
				next( $conditions );
			}
		}

		// Check mobile condition.
		if ( isset( $conditions['mobile'] ) ) {
			switch ( $conditions['mobile'] ) {
				case 'only':
					if ( ! wp_is_mobile() ) {
						return false;
					}
					break;
				case 'no':
					if ( wp_is_mobile() ) {
						return false;
					}
					break;
			}
		}

		return true;
	}

	/**
	 * Create a random wrapper id
	 *
	 * @since 1.1.4
	 *
	 * @return string $id random id string
	 */
	public function create_wrapper_id(): string {
		$wrapper_id = sanitize_key( $this->get_wrapper_id() );
		if ( '' !== $wrapper_id ) {
			return $wrapper_id;
		}

		return wp_advads()->get_frontend_prefix() . wp_rand();
	}

	/**
	 * Sets the wrapper styles based on the given position.
	 *
	 * @param array  $wrapper  The wrapper array to store the styles.
	 * @param string $position The position of the ad.
	 * @param bool   $use_position Whether to use the position or not.
	 *
	 * @return void
	 */
	private function get_wrapper_styles( &$wrapper, $position, $use_position ): void {
		// Always keep margin before handling position.
		$margin = $this->get_margin();
		foreach ( $margin as $key => $value ) {
			if ( ! empty( $value ) ) {
				$wrapper['style'][ 'margin-' . $key ] = $value . 'px';
			}
		}

		switch ( $position ) {
			case 'left':
			case 'left_float':
			case 'left_nofloat':
				$wrapper['style']['float'] = 'left';
				break;
			case 'right':
			case 'right_float':
			case 'right_nofloat':
				$wrapper['style']['float'] = 'right';
				break;
			case 'center':
			case 'center_nofloat':
			case 'center_float':
				$wrapper['style']['margin-left']  = 'auto';
				$wrapper['style']['margin-right'] = 'auto';

				if ( empty( $this->get_width() ) || empty( $this->get_prop( 'add_wrapper_sizes' ) ) || $use_position ) {
					$wrapper['style']['text-align'] = 'center';
				}
				break;
			case 'clearfix':
				$wrapper['style']['clear'] = 'both';
				break;
		}

		if ( $this->is_space_reserved() ) {
			if ( ! empty( $this->get_width() ) ) {
				$wrapper['style']['width'] = $this->get_width() . 'px';
			}

			if ( ! empty( $this->get_height() ) ) {
				$wrapper['style']['height'] = $this->get_height() . 'px';
			}
		}

		if ( ! empty( $this->get_clearfix() ) ) {
			$wrapper['style']['clear'] = 'both';
		}
	}

	/**
	 * Generate the tooltip title for a placement with associated ads.
	 *
	 * @return string Tooltip title containing placement and ads name.
	 */
	private function get_tooltip_title(): string {
		$ads = $this->is_parent_group()
			? wp_list_pluck( $this->get_parent()->get_ads(), 'post_title' )
			: [ $this->get_title() ];

		// Construct and format the tooltip title using the placement ID and ad titles.
		return sprintf(
			/* translators: %1$s is a placement name, %2$s is the ads name. */
			__( 'Placement name: %1$s; Ads: %2$s', 'advanced-ads' ),
			esc_attr( $this->get_parent()->get_title() ),
			esc_attr( $ads ? implode( ',', $ads ) : '' )
		);
	}
}
