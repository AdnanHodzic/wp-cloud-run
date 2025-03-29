<?php
/**
 * This class is serving as the base for various group types and providing a foundation for defining common group attributes and methods.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Abstracts;

use Advanced_Ads;
use Advanced_Ads_Utils;
use Advanced_Ads_Inline_Css;
use AdvancedAds\Traits;
use AdvancedAds\Constants;
use AdvancedAds\Frontend\Stats;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Interfaces\Group_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Group.
 */
class Group extends Data {

	use Traits\Entity;
	use Traits\Wrapper;

	/**
	 * This is the object type.
	 *
	 * @var string
	 */
	protected $object_type = 'group';

	/**
	 * The label for the group.
	 *
	 * @var string|null
	 */
	private $label = null;

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @var array
	 */
	protected $data = [
		'title'      => '',
		'content'    => '',
		'status'     => false,
		'slug'       => '',
		'type'       => 'default',
		'ad_count'   => 1,
		'options'    => [],
		'ad_weights' => [],
	];

	/**
	 * Hold ads within the group
	 *
	 * @var Ad[]
	 */
	private $ads = null;

	/**
	 * Hold sorted ads within the group
	 *
	 * @var Ad[]
	 */
	private $sorted_ads = null;

	/**
	 * Wrapper for the group.
	 *
	 * @var array|null
	 */
	private $wrapper = null;

	/**
	 * Get the group if ID is passed, otherwise the group is new and empty.
	 *
	 * @param Group|WP_Term|int $group Group to init.
	 */
	public function __construct( $group = 0 ) {
		parent::__construct();

		$this->set_group_id( $group );
		$this->data_store = wp_advads_get_group_repository();

		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/**
	 * Set the group ID depending on what was passed.
	 *
	 * @param Group|WP_Term|int $group Group instance, term instance or numeric.
	 *
	 * @return void
	 */
	private function set_group_id( $group ): void {
		if ( is_numeric( $group ) && $group > 0 ) {
			$this->set_id( $group );
		} elseif ( $group instanceof self ) {
			$this->set_id( absint( $group->get_id() ) );
		} elseif ( ! empty( $group->term_id ) ) {
			$this->set_id( absint( $group->term_id ) );
		} else {
			$this->set_object_read( true );
		}
	}

	/* Getter ------------------- */

	/**
	 * Get name.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_name( $context = 'view' ): string {
		return $this->get_title( $context );
	}

	/**
	 * Get display ad count.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int|string
	 */
	public function get_display_ad_count( $context = 'view' ) {
		$value = $this->get_prop( 'ad_count', $context );

		return 'all' === $value ? $value : absint( $value );
	}

	/**
	 * Get options.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_options( $context = 'view' ): array {
		return $this->get_prop( 'options', $context );
	}

	/**
	 * Get ad weights.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_ad_weights( $context = 'view' ): array {
		return $this->get_prop( 'ad_weights', $context );
	}

	/**
	 * Get max ad to show.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int|string
	 */
	public function get_ad_count( $context = 'view' ) {
		return $this->get_prop( 'ad_count', $context );
	}

	/**
	 * Get publish date
	 *
	 * @return string|bool
	 */
	public function get_publish_date() {
		return $this->get_prop( 'publish_date' );
	}

	/**
	 * Get modified date
	 *
	 * @return string|bool
	 */
	public function get_modified_date() {
		return $this->get_prop( 'modified_date' );
	}

	/* Setter ------------------- */

	/**
	 * Set name.
	 *
	 * @param string $name Group name.
	 *
	 * @return void
	 */
	public function set_name( $name ): void {
		$this->set_title( $name );
	}

	/**
	 * Set options.
	 *
	 * @param array $options Group options.
	 *
	 * @return void
	 */
	public function set_options( $options ): void {
		$this->set_prop( 'options', $options );
	}

	/**
	 * Set ad_weights.
	 *
	 * @param array $ad_weights Group ad_weights.
	 *
	 * @return void
	 */
	public function set_ad_weights( $ad_weights ): void {
		$this->set_prop( 'ad_weights', $ad_weights );
	}

	/**
	 * Set ad count.
	 *
	 * @param int $ad_count Group ad_count.
	 *
	 * @return void
	 */
	public function set_ad_count( $ad_count ): void {
		$ad_count = 'all' === $ad_count ? 'all' : absint( $ad_count );
		$this->set_prop( 'ad_count', $ad_count );
	}

	/**
	 * Set publish date
	 *
	 * @param string $date Publish Date.
	 *
	 * @return void
	 */
	public function set_publish_date( $date ): void {
		$this->set_prop( 'publish_date', $date );
	}

	/**
	 * Set modified date
	 *
	 * @param string $date Modified Date.
	 *
	 * @return void
	 */
	public function set_modified_date( $date ): void {
		$this->set_prop( 'modified_date', $date );
	}

	/* Conditional ------------------- */

	/**
	 * Checks if the group is placed in the header.
	 *
	 * @return bool
	 */
	public function is_head_placement(): bool {
		return null !== $this->get_parent() && $this->get_parent()->is_type( 'header' );
	}

	/**
	 * Determines whether the group can be displayed.
	 *
	 * @return bool True if the group can be displayed, false otherwise.
	 */
	public function can_display(): bool {
		return apply_filters( 'advanced-ads-can-display-group', true, $this->get_id(), $this );
	}

	/**
	 * Check if current user has the capability to edit the group taxonomy.
	 *
	 * @return bool
	 */
	public static function can_current_user_edit_group() {
		global $tax;

		$group_taxonomy = $tax ? $tax : get_taxonomy( Constants::TAXONOMY_GROUP );

		return current_user_can( $group_taxonomy->cap->edit_terms );
	}

	/* Additional methods ------------------- */

	/**
	 * Prepares the output for the group.
	 *
	 * @return string The prepared output.
	 */
	public function prepare_output(): string {
		$ordered_ad_ids = $this->get_ordered_ad_ids();

		$override = apply_filters( 'advanced-ads-ad-select-override-by-group', false, $this, $ordered_ad_ids, $this->get_data() );
		if ( false !== $override ) {
			return $override;
		}

		if ( empty( $ordered_ad_ids ) ) {
			return '';
		}

		$ad_args       = $this->get_prop( 'ad_args' );
		$ad_output     = $this->prepare_ad_output( $ordered_ad_ids );
		$global_output = $ad_args['global_output'] ?? true;

		// Maintain Stats.
		if ( $global_output && $ad_output ) {
			Stats::get()->add_entity( 'group', $this->get_id(), $this->get_title() );
		}

		$ad_output = apply_filters( 'advanced-ads-group-output-array', $ad_output, $this );
		if ( [] === $ad_output || ! is_array( $ad_output ) ) {
			return '';
		}

		$output_string = implode( '', $ad_output );
		$wrapper       = ! $this->is_head_placement() ? $this->create_wrapper() : [];

		// Adds inline css to the wrapper.
		if ( ! empty( $ad_args['inline-css'] ) && ! isset( $ad_args['is_top_level'] ) ) {
			$inline_css = new Advanced_Ads_Inline_Css();
			$wrapper    = $inline_css->add_css( $wrapper, $ad_args['inline-css'], $global_output );
		}

		if ( ! $this->is_head_placement() && [] !== $wrapper ) {
			$output_string = '<div' . Advanced_Ads_Utils::build_html_attributes( $wrapper ) . '>'
			. $this->get_label()
			. apply_filters( 'advanced-ads-output-wrapper-before-content-group', '', $this )
			. $output_string
			. apply_filters( 'advanced-ads-output-wrapper-after-content-group', '', $this )
			. '</div>';
		}

		if ( ! empty( $ad_args['is_top_level'] ) && ! empty( $ad_args['placement_clearfix'] ) ) {
			$output_string .= '<br style="clear: both; display: block; float: none;"/>';
		}

		return $output_string;
	}

	/**
	 * Get group type object
	 *
	 * @return Group_Type|bool
	 */
	public function get_type_object() {
		if ( ! wp_advads_has_group_type( $this->get_type() ) ) {
			wp_advads_create_group_type( $this->get_type() );
		}

		return wp_advads_get_group_type( $this->get_type() );
	}

	/**
	 * Get total ads attached.
	 *
	 * @return int
	 */
	public function get_ads_count(): int {
		return count( $this->get_ad_weights() );
	}

	/**
	 * Get ads attached to the group
	 *
	 * @return Ad[]
	 */
	public function get_ads(): array {
		if ( null !== $this->ads ) {
			return $this->ads;
		}

		$this->ads = [];
		$weights   = $this->get_ad_weights();

		foreach ( $weights as $ad_id => $weight ) {
			$ad = wp_advads_get_ad( $ad_id );
			if ( $ad ) {
				$this->ads[ $ad_id ] = $ad;
				$ad->set_prop( 'weight', $weight );
			}
		}

		return $this->ads;
	}

	/**
	 * Get ads ids attached to the group
	 *
	 * @return array
	 */
	public function get_ads_ids(): array {
		return array_keys( $this->get_ad_weights() );
	}

	/**
	 * Order the ad list by weight first and then by title.
	 *
	 * @return array<int, int>
	 */
	public function get_sorted_ads(): array {
		if ( null !== $this->sorted_ads ) {
			return $this->sorted_ads;
		}

		$this->sorted_ads = [];
		foreach ( $this->get_ads() as $ad ) {
			$this->sorted_ads[] = [
				'id'     => $ad->get_id(),
				'title'  => $ad->get_title(),
				'weight' => $ad->get_weight() ?? Constants::GROUP_AD_DEFAULT_WEIGHT,
			];
		}

		array_multisort(
			array_column( $this->sorted_ads, 'weight' ),
			SORT_DESC,
			array_column( $this->sorted_ads, 'title' ),
			SORT_ASC,
			$this->sorted_ads
		);

		$this->sorted_ads = array_combine( array_column( $this->sorted_ads, 'id' ), $this->sorted_ads );

		return $this->sorted_ads;
	}

	/**
	 * Get the max weight for group depending on number of ads and default value
	 *
	 * @return int
	 */
	public function get_max_weight(): int {
		$ads_count  = $this->get_ads_count();
		$max_weight = max( $ads_count, Constants::GROUP_AD_DEFAULT_WEIGHT );

		return apply_filters( 'advanced-ads-max-ad-weight', $max_weight, $ads_count );
	}

	/**
	 * Get group hints
	 *
	 * @return array
	 */
	public function get_hints(): array {
		$hints = [];

		// Early bail!!
		if ( ! Conditional::has_cache_plugins() || $this->get_ads_count() < 2 ) {
			return $hints;
		}

		if ( ! class_exists( 'Advanced_Ads_Pro' ) ) {
			$installed_plugins = get_plugins();

			$link       = 'https://wpadvancedads.com/add-ons/advanced-ads-pro/?utm_source=advanced-ads&utm_medium=link&utm_campaign=groups-CB';
			$link_title = __( 'Get this add-on', 'advanced-ads' );
			if ( isset( $installed_plugins['advanced-ads-pro/advanced-ads-pro.php'] ) ) {
				$link       = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=advanced-ads-pro/advanced-ads-pro.php', 'activate-plugin_advanced-ads-pro/advanced-ads-pro.php' );
				$link_title = __( 'Activate now', 'advanced-ads' );
			}

			$hints['cache'] = sprintf(
				wp_kses(
					/* translators: %1$s is an URL, %2$s is a URL text */
					__( 'It seems that a caching plugin is activated. Your ads might not rotate properly. The cache busting in Advanced Ads Pro will solve that. <a href="%1$s" target="_blank">%2$s.</a>', 'advanced-ads' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
						],
					]
				),
				$link,
				$link_title
			);
		}

		/**
		 * Allows to add new hints.
		 *
		 * @param string[] $hints Existing hints (escaped strings).
		 * @param Group    $group The group object.
		 */
		return apply_filters( 'advanced-ads-group-hints', $hints, $this );
	}

	/**
	 * Build html for group hints.
	 *
	 * @return string
	 */
	public function get_hints_html(): string {
		$hints_html = '';
		foreach ( $this->get_hints() as $hint ) {
			$hints_html .= '<p class="advads-notice-inline advads-error">' . $hint . '</p>';
		}

		return $hints_html;
	}

	/**
	 * Get the group edit link
	 *
	 * @return string
	 */
	public function get_edit_link() {
		return add_query_arg(
			[
				'page'                     => 'advanced-ads-groups',
				'advads-last-edited-group' => $this->get_id(),
			],
			admin_url( 'admin.php' ) . '#modal-group-edit-' . $this->get_id()
		);
	}

	/**
	 * Get ordered ids of the ads that belong to the group
	 *
	 * @return array
	 */
	public function get_ordered_ad_ids() {
		$ordered_ad_ids = $this->is_type( 'ordered' )
			? $this->shuffle_ordered_ads()
			: $this->shuffle_ads();

		return apply_filters( 'advanced-ads-group-output-ad-ids', $ordered_ad_ids, $this->get_type(), $this->get_ads(), $this->get_ad_weights(), $this );
	}

	/**
	 * Shuffle ads based on ad weight.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function shuffle_ads(): array {
		$shuffled_ads = [];

		$random_id = 0;
		$ads       = $this->get_ads();
		$weights   = $this->get_ad_weights();

		while ( null !== $random_id ) {
			$random_id = $this->get_random_ad_by_weight( $weights );
			unset( $weights[ $random_id ] );
			if ( ! empty( $ads[ $random_id ] ) ) {
				$shuffled_ads[] = $random_id;
			}
		}

		return $shuffled_ads;
	}

	/**
	 * Prepare the output for the ads.
	 *
	 * @param array $ordered_ad_ids Ordered ad IDs.
	 *
	 * @return array
	 */
	private function prepare_ad_output( $ordered_ad_ids ): array {
		$output        = [];
		$ads_displayed = 0;
		$ad_count      = apply_filters( 'advanced-ads-group-ad-count', $this->get_ad_count(), $this );

		if ( is_array( $ordered_ad_ids ) ) {
			foreach ( $ordered_ad_ids as $ad_id ) {
				$ad = wp_advads_get_ad( $ad_id );
				if ( ! $ad ) {
					continue;
				}

				$ad->set_prop_temp( 'ad_args', $this->get_prop( 'ad_args' ) );
				$ad->set_parent( $this );
				$ad->set_prop_temp( 'ad_label', 'disabled' );
				$ad_output = $ad->can_display() ? $ad->output() : '';

				if ( ! empty( $ad_output ) ) {
					$output[] = $ad_output;
					++$ads_displayed;
					// Break the loop when maximum ads are reached.
					if ( $ads_displayed === $ad_count ) {
						break;
					}
				}
			}
		}

		return $output;
	}

	/**
	 * Shuffles the ordered ads based on their weights.
	 *
	 * @return array
	 */
	private function shuffle_ordered_ads(): array {
		$weights = $this->get_ad_weights();

		// Sort the ad IDs by their weights.
		arsort( $weights );
		$ordered_ad_ids = array_keys( $weights );
		$weights        = array_values( $weights );
		$count          = count( $weights );
		$pos            = 0;

		for ( $i = 1; $i <= $count; $i++ ) {
			if ( $i === $count || $weights[ $i ] !== $weights[ $i - 1 ] ) {
				$slice_len = $i - $pos;
				if ( 1 !== $slice_len ) {
					$shuffled = array_slice( $ordered_ad_ids, $pos, $slice_len );
					shuffle( $shuffled );
					array_splice( $ordered_ad_ids, $pos, $slice_len, $shuffled ); // Replace the unshuffled chunk with the shuffled one.
				}
				$pos = $i;
			}
		}

		return $ordered_ad_ids;
	}

	/**
	 * Get random ad by ad weight.
	 *
	 * @since 1.0.0
	 *
	 * @source applied with fix for order http://stackoverflow.com/a/11872928/904614
	 *
	 * @param array $weights Array of $ad_id => weight pairs.
	 *
	 * @return null|int
	 */
	private function get_random_ad_by_weight( $weights ) {

		// use maximum ad weight for ads without this
		// ads might have a weight of zero (0); to avoid mt_rand fail assume that at least 1 is set.
		$max = array_sum( $weights );
		if ( $max < 1 ) {
			return null;
		}

		$rand = wp_rand( 1, $max );
		foreach ( $weights as $ad_id => $weight ) {
			$rand -= $weight;
			if ( $rand <= 0 ) {
				return $ad_id;
			}
		}

		return null;
	}

	/**
	 * Retrieves the label for the group.
	 *
	 * @return string The label for the group.
	 */
	private function get_label(): string {
		if ( null === $this->label ) {
			$ad_args     = $this->get_prop( 'ad_args' );
			$state       = $ad_args['ad_label'] ?? 'default';
			$this->label = Advanced_Ads::get_instance()->get_label( $this, $state );
		}

		return $this->label;
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
		$ad_args       = $this->get_prop( 'ad_args' );
		if ( ! isset( $ad_args['is_top_level'] ) ) {
			$ad_args['is_top_level'] = true;
		}

		if ( $ad_args['is_top_level'] ) {
			// Add placement class.
			if ( ! empty( $ad_args['output']['class'] ) && is_array( $ad_args['output']['class'] ) ) {
				$this->wrapper['class'] = $ad_args['output']['class'];
			}

			// Ad Health Tool add class wrapper.
			if ( Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
				$frontend_prefix          = wp_advads()->get_frontend_prefix();
				$this->wrapper['class'][] = $frontend_prefix . 'highlight-wrapper';
			}

			if ( isset( $ad_args['output']['wrapper_attrs'] ) && is_array( $ad_args['output']['wrapper_attrs'] ) ) {
				foreach ( $ad_args['output']['wrapper_attrs'] as $key => $value ) {
					$this->wrapper[ $key ] = $value;
				}
			}

			if ( ! empty( $ad_args['placement_position'] ) ) {
				switch ( $ad_args['placement_position'] ) {
					case 'left':
						$this->wrapper['style']['float'] = 'left';
						break;
					case 'right':
						$this->wrapper['style']['float'] = 'right';
						break;
					case 'center':
						// We don't know whether the 'reserve_space' option exists and width is set.
						$this->wrapper['style']['text-align'] = 'center';
						break;
				}
			}
		}

		$this->wrapper = (array) apply_filters( 'advanced-ads-output-wrapper-options-group', $this->wrapper, $this );

		if ( ( [] !== $this->wrapper || $this->get_label() ) && ! isset( $this->wrapper['id'] ) ) {
			$this->wrapper['id'] = wp_advads()->get_frontend_prefix() . wp_rand();
		}

		return $this->wrapper;
	}
}
