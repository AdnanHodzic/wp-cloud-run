<?php

use AdvancedAds\Entities;
use AdvancedAds\Utilities\WordPress;

/**
 * An ad group object
 *
 * @package   Advanced_Ads_Group
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright 2014 Thomas Maier, Advanced Ads GmbH
 */
#[\AllowDynamicProperties]
class Advanced_Ads_Group {

	/**
	 * Default ad group weight
	 * previously called MAX_AD_GROUP_WEIGHT
	 */
	const MAX_AD_GROUP_DEFAULT_WEIGHT = 10;

	/**
	 * Term id of this ad group
	 */
	public $id = 0;

	/**
	 * Group type
	 *
	 * @since 1.4.8
	 */
	public $type = 'default';

	/**
	 * Name of the taxonomy
	 */
	public $taxonomy = '';

	/**
	 * Post type of the ads
	 */
	protected $post_type = '';

	/**
	 * The current loaded ad
	 */
	protected $current_ad = '';

	/**
	 * The name of the term
	 */
	public $name = '';

	/**
	 * The slug of the term
	 */
	public $slug = '';

	/**
	 * The description of the term
	 */
	public $description = '';

	/**
	 * Number of ads to display in the group block
	 */
	public $ad_count = 1;

	/**$slug
	 * contains other options
	 *
	 * @since 1.5.5
	 */
	public $options = [];

	/**
	 * Optional arguments passed to ads.
	 *
	 * @var array
	 */
	public $ad_args = [];

	/**
	 * Containing ad weights
	 */
	private $ad_weights;

	/**
	 * Array with post type objects (ads)
	 */
	private $ads = false;

	/**
	 * Multidimensional array contains information about the wrapper
	 *  each possible html attribute is an array with possible multiple elements.
	 *
	 * @since untagged
	 */
	public $wrapper = [];

	/**
	 * Displayed above the ad.
	 */
	public $label = '';

	/**
	 * Whether this group is in a head placement.
	 *
	 * @var bool
	 */
	private $is_head_placement;

	/**
	 * True, if this is an Advanced Ads Ad Group
	 *
	 * @var bool
	 */
	public $is_group = false;

	/**
	 * The decorated WP_Term object.
	 *
	 * @var \WP_Term
	 */
	private $group;

	/**
	 * Init ad group object
	 *
	 * @param int|WP_Term $group   Either id of the ad group or term object.
	 * @param iterable    $ad_args Optional arguments passed to ads.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $group, iterable $ad_args = [] ) {
		$this->taxonomy = Entities::TAXONOMY_AD_GROUP;

		$this->group = get_term( $group, $this->taxonomy );
		if ( $this->group === null || is_wp_error( $this->group ) ) {
			return;
		}

		$this->is_group                = true;
		$this->id                      = $this->group->term_id;
		$this->name                    = $this->group->name;
		$this->slug                    = $this->group->slug;
		$this->description             = $this->group->description;
		$this->post_type               = Entities::POST_TYPE_AD;
		$this->ad_args                 = $ad_args;
		$this->is_head_placement       = isset( $this->ad_args['placement_type'] ) && $this->ad_args['placement_type'] === 'header';
		$this->ad_args['is_top_level'] = ! isset( $this->ad_args['is_top_level'] );

		$this->load_additional_attributes();

		if ( ! $this->is_head_placement ) {
			$this->create_wrapper();
		}
	}

	/**
	 * If a property on the original WP_Term is requested, return it, otherwise null.
	 *
	 * @param string $name The requested property name.
	 *
	 * @return mixed|null
	 * @noinspection MagicMethodsValidityInspection -- We don't want to allow setting of properties.
	 */
	public function __get( string $name ) {
		return $this->group->$name ?? null;
	}

	/**
	 * Whether a property is set.
	 *
	 * @param string $name The requested property name.
	 *
	 * @return bool
	 * @noinspection MagicMethodsValidityInspection -- We don't want to allow setting of properties.
	 */
	public function __isset( string $name ): bool {
		return isset( $this->group->$name );
	}

	/**
	 * Load additional attributes for groups that are not part of the WP terms
	 *
	 * @since 1.4.8
	 */
	protected function load_additional_attributes() {
		// -TODO should abstract (i.e. only call once per request)
		$all_groups = get_option( 'advads-ad-groups', [] );

		if ( ! isset( $all_groups[ $this->id ] ) || ! is_array( $all_groups[ $this->id ] ) ) { return; }

		if ( isset( $this->ad_args['change-group'] ) ) {
			// some options was provided by the user
			$group_data = Advanced_Ads_Utils::merge_deep_array( [ $all_groups[ $this->id ], $this->ad_args['change-group'] ] ) ;
		} else {
			$group_data = $all_groups[ $this->id ];
		}

		if ( isset( $group_data['type'] ) ) {
			$this->type = $group_data['type'];
		}

		// get ad count; default is 1
		if ( isset( $group_data['ad_count'] ) ) {
			$this->ad_count = $group_data['ad_count'] === 'all' ? 'all' : (int) $group_data['ad_count'];
		}

		if ( isset( $group_data['options'] ) ) {
			$this->options = isset( $group_data['options'] ) ? $group_data['options'] : [];
		}
	}

	/**
	 * Control the output of the group by type and amount of ads
	 *
	 * @param array $ordered_ad_ids Ordered ids of the ads that belong to the group.
	 *
	 * @return string $output output of ad(s) by ad
	 * @since 1.4.8
	 */
	public function output( $ordered_ad_ids ) {
		if ( empty( $ordered_ad_ids ) ) {
			return '';
		}

		// load the ad output
		$output = [];
		$ads_displayed = 0;
		$ad_count = apply_filters( 'advanced-ads-group-ad-count', $this->ad_count, $this );

		$ad_select = Advanced_Ads_Select::get_instance();

		// the Advanced_Ads_Ad obj can access this info
		$this->ad_args['group_info'] = [
			'id' => $this->id,
			'name' => $this->name,
			'type' => $this->type,
			'refresh_enabled' => ! empty( $this->options['refresh']['enabled'] ),
		];
		$this->ad_args['ad_label'] = 'disabled';

		if( is_array( $ordered_ad_ids ) ){
			foreach ( $ordered_ad_ids as $_ad_id ) {
				$this->ad_args['group_info']['ads_displayed'] = $ads_displayed;

				// load the ad output
				$ad = $ad_select->get_ad_by_method( $_ad_id, Advanced_Ads_Select::AD, $this->ad_args );

				if ( ! empty( $ad ) ) {
					$output[] = $ad;
					$ads_displayed++;
					// break the loop when maximum ads are reached
					if( $ads_displayed === $ad_count ) {
						break;
					}
				}
			}
		}

		$global_output = ! isset( $this->ad_args['global_output'] ) || $this->ad_args['global_output'];
		if ( $global_output ) {
			// add the group to the global output array
			$advads = Advanced_Ads::get_instance();
			$advads->current_ads[] = ['type' => 'group', 'id' => $this->id, 'title' => $this->name];
		}

		if ( $output === [] || ! is_array( $output ) ) {
			return '';
		}

		// filter grouped ads output
		$output_array = apply_filters( 'advanced-ads-group-output-array', $output, $this );

		// make sure the right format comes through the filter
		if ( $output_array === [] || ! is_array( $output_array ) ) {
			return '';
		}

		$output_string = implode( '', $output_array );

		// Adds inline css to the wrapper.
		if ( ! empty( $this->ad_args['inline-css'] ) && $this->ad_args['is_top_level'] ) {
			$inline_css    = new Advanced_Ads_Inline_Css();
			$this->wrapper = $inline_css->add_css( $this->wrapper, $this->ad_args['inline-css'], $global_output );
		}

		if ( ! $this->is_head_placement && $this->wrapper !== [] ) {
			$output_string = '<div' . Advanced_Ads_Utils::build_html_attributes( $this->wrapper ) . '>'
			. $this->label
			. apply_filters( 'advanced-ads-output-wrapper-before-content-group', '', $this )
			. $output_string
			. apply_filters( 'advanced-ads-output-wrapper-after-content-group', '', $this )
			. '</div>';
		}

		if ( ! empty( $this->ad_args['is_top_level'] ) && ! empty( $this->ad_args['placement_clearfix'] ) ) {
			$output_string .= '<br style="clear: both; display: block; float: none;"/>';
		}

		// filter final group output
		return apply_filters( 'advanced-ads-group-output', $output_string, $this );
	}

	/**
	 * Get ordered ids of the ads that belong to the group
	 *
	 * @return array
	 */
	public function get_ordered_ad_ids() {
		// load ads
		$ads = $this->load_all_ads();
		if ( ! is_array( $ads ) ) {
			return [];
		}

		// get ad weights serving as an order here
		$weights = $this->get_ad_weights( array_keys( $ads ) );
		$ad_ids  = wp_list_pluck( $ads, 'ID' );

		// remove ads with 0 ad weight and unavailable ads (e.g. drafts).
		foreach ( $weights as $ad_id => $ad_weight ) {
			if ( $ad_weight === 0 || ! in_array( $ad_id, $ad_ids, true ) ) {
				unset( $weights[ $ad_id ] );
			}
		}

		// order ads based on group type
		if ( $this->type === 'ordered' ) {
			$ordered_ad_ids = $this->shuffle_ordered_ads( $weights );
		} else { // default
			$ordered_ad_ids = $this->shuffle_ads( $ads, $weights );
		}

		return apply_filters( 'advanced-ads-group-output-ad-ids', $ordered_ad_ids, $this->type, $ads, $weights, $this );
	}

	/**
	 * Return all ads from this group
	 *
	 * @since 1.0.0
	 */
	public function get_all_ads() {
		return $this->load_all_ads();
	}

	/**
	 * Load all public ads for this group
	 *
	 * @since 1.0.0
	 * @update 1.1.0 load only public ads
	 * @update allow to cache groups for few minutes
	 *
	 * @return bool|WP_Post[] $ads array with ad (post) objects
	 */
	private function load_all_ads() {

		if ( ! $this->id ) {
			return [];
		}

		if ( false !== $this->ads ) {
			return $this->ads;
		}

		// Much more complex than needed: one of the three queries is not needed and the last query gets slow quiet fast.
		$args = [
			'post_type'      => $this->post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'taxonomy'       => $this->taxonomy,
			'term'           => $this->slug,
			'orderby'        => 'id', // Might want to avoid sorting as not needed for most calls and fast in PHP; slight I/O blocking concern.
		];

		if ( ! empty( $this->ad_args['post_status'] ) ) {
			$args['post_status'] = array_unique( array_merge( (array) $args['post_status'], (array) $this->ad_args['post_status'] ) );
		}

		$ads = new WP_Query( $args );

		if ( $ads->have_posts() ) {
			$this->ads = $this->add_post_ids( $ads->posts );
		} else {
			$this->ads = [];
		}

		return $this->ads;
	}

	/**
	 * Use post ids as keys for ad array
	 *
	 * @since 1.0.0
	 * @param array $ads array with post objects.
	 * @return array $ads array with post objects with post id as their key.
	 */
	private function add_post_ids( array $ads ) {
		return array_reduce(
			$ads,
			function( $ads, $ad ) {
				$ads[ $ad->ID ] = $ad;

				return $ads;
			},
			[]
		);
	}

	/**
	 * Shuffle ads based on ad weight.
	 *
	 * @param array $ads     ad objects.
	 * @param array $weights ad weights, indexed by ad id.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function shuffle_ads( $ads, $weights ) {
		// get a random ad for every ad there is
		$shuffled_ads = [];
		// while non-zero weights are set select random next
		// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition -- prevents code duplication.
		while ( null !== ( $random_ad_id = $this->get_random_ad_by_weight( $weights ) ) ) {
			// remove chosen ad from weights array
			unset( $weights[ $random_ad_id ] );
			// put random ad into shuffled array
			if ( ! empty( $ads[ $random_ad_id ] ) ) {
				$shuffled_ads[] = $random_ad_id;
			}
		}

		return $shuffled_ads;
	}

	/**
	 * Shuffle ads that have the same width.
	 *
	 * @since untagged
	 * @param array $weights Array of $ad_id => weight pairs.
	 * @return array $ordered_ad_ids Ordered ad ids.
	 */
	public function shuffle_ordered_ads( array $weights ) {
		// order to highest weight first
		arsort( $weights );
		$ordered_ad_ids = array_keys( $weights );

		$weights = array_values( $weights );
		$count = count( $weights );
		$pos = 0;
		for ( $i = 1; $i <= $count; $i++ ) {
			if ( $i == $count || $weights[ $i ] !== $weights[ $i - 1] ) {
				$slice_len = $i - $pos;
				if ( $slice_len !== 1 ) {
					$shuffled = array_slice( $ordered_ad_ids, $pos, $slice_len );
					shuffle ( $shuffled );
					// Replace the unshuffled chunk with the shuffled one.
					array_splice( $ordered_ad_ids, $pos, $slice_len, $shuffled );
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
	 * @param array $ad_weights Indexed by ad_id [int $ad_id => int $weight].
	 * @source applied with fix for order http://stackoverflow.com/a/11872928/904614
	 *
	 * @return null|int
	 */
	private function get_random_ad_by_weight(array $ad_weights) {

		// use maximum ad weight for ads without this
		// ads might have a weight of zero (0); to avoid mt_rand fail assume that at least 1 is set.
		$max = array_sum( $ad_weights );
		if ( $max < 1 ) {
			return null;
		}

		$rand = mt_rand( 1, $max );
		foreach ( $ad_weights as $ad_id => $weight ) {
			$rand -= $weight;
			if ( $rand <= 0 ) {
				return $ad_id;
			}
		}

		return null;
	}

	/**
	 * Get weights of ads in this group
	 *
	 * @param array $ad_ids Ids of ads assigned to this group.
	 *
	 * @return array
	 */
	public function get_ad_weights( $ad_ids = [] ) {
		if ( is_array( $this->ad_weights ) ) {
			return $this->ad_weights;
		}

		$weights          = get_option( 'advads-ad-weights', [] );
		$this->ad_weights = [];
		if ( array_key_exists( $this->id, $weights ) ) {
			$this->ad_weights = $weights[ $this->id ];
		}
		asort( $this->ad_weights );

		// add ads whose weight has not yet been saved with the default value.
		foreach ( $ad_ids as $ad_id ) {
			if ( ! array_key_exists( $ad_id, $this->ad_weights ) ) {
				$this->ad_weights[ $ad_id ] = self::MAX_AD_GROUP_DEFAULT_WEIGHT;
			}
		}

		return $this->ad_weights;
	}

	/**
	 * Save ad group information that are not included in terms or ad weight
	 *
	 * @since 1.4.8
	 * @param arr $args group arguments
	 */
	public function save($args = []) {

		$defaults = [ 'type' => 'default', 'ad_count' => 1, 'options' => [] ];
		$args = wp_parse_args($args, $defaults);

		// get global ad group option
		$groups = get_option( 'advads-ad-groups', [] );

		$groups[$this->id] = $args;

		update_option( 'advads-ad-groups', $groups );
	}

	/**
	 * Delete all the ad weights for a group by id
	 *
	 * @since 1.0.0
	 */
	public static function delete_ad_weights($group_id){
	    $all_weights = get_option( 'advads-ad-weights', [] );
	    if ($all_weights && isset($all_weights[$group_id])){
	        unset($all_weights[$group_id]);
	        update_option( 'advads-ad-weights', $all_weights );
	    }
	}

	/**
	 * Create a wrapper to place around the group.
	 */
	private function create_wrapper() {
		$this->wrapper = [];

		if ( $this->ad_args['is_top_level'] ) {
			// Add label.
			$placement_state = isset( $this->ad_args['ad_label'] ) ? $this->ad_args['ad_label'] : 'default';
			$this->label = Advanced_Ads::get_instance()->get_label( $placement_state );

			// Add placement class.
			if ( ! empty( $this->ad_args['output']['class'] ) && is_array( $this->ad_args['output']['class'] ) ) {
				$this->wrapper['class'] = $this->ad_args['output']['class'];
			}

			// ad Health Tool add class wrapper
			if ( WordPress::user_can('advanced_ads_edit_ads') ) {
				$frontend_prefix = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();
				$this->wrapper['class'][] = $frontend_prefix . 'highlight-wrapper';
			}

			if ( isset( $this->ad_args['output']['wrapper_attrs'] ) && is_array( $this->ad_args['output']['wrapper_attrs'] ) ) {
				foreach ( $this->ad_args['output']['wrapper_attrs'] as $key => $value ) {
					$this->wrapper[$key] = $value;
				}
			}

			if ( ! empty( $this->ad_args['placement_position'] ) ) {
				switch ( $this->ad_args['placement_position'] ) {
					case 'left' :
						$this->wrapper['style']['float'] = 'left';
						break;
					case 'right' :
						$this->wrapper['style']['float'] = 'right';
						break;
					case 'center' :
						// We don't know whether the 'add_wrapper_sizes' option exists and width is set.
						$this->wrapper['style']['text-align'] = 'center';
						break;
				}
			}
		}

		$this->wrapper = (array) apply_filters( 'advanced-ads-output-wrapper-options-group', $this->wrapper, $this );

		if ( ( $this->wrapper !== [] || $this->label ) && ! isset( $this->wrapper['id'] ) ) {
			$prefix = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();
			$this->wrapper['id'] = $prefix . mt_rand();
		}
	}

	/**
	 * Calculate the number of available weights for a group depending on
	 * number of ads and default value.
	 *
	 * @param int $num_ads Number of ads in the group.
	 * @since 1.8.22
	 *
	 * @return  max weight used in group settings
	 */
	public static function get_max_ad_weight( $num_ads = 1 ){

		// use default if lower than default.
		$num_ads = absint( $num_ads );

		// use number of ads or max ad weight value, whatever is higher
		$max_weight = $num_ads < self::MAX_AD_GROUP_DEFAULT_WEIGHT ? self::MAX_AD_GROUP_DEFAULT_WEIGHT : $num_ads;

		// allow users to manipulate max ad weight
		return apply_filters( 'advanced-ads-max-ad-weight', $max_weight, $num_ads );
	}

	/**
	 * Get group hints.
	 *
	 * @param Advanced_Ads_Group $group The group object.
	 *
	 * @return string[] Group hints (escaped strings).
	 */
	public static function get_hints( Advanced_Ads_Group $group ) {
		$hints = [];

		if (
			! Advanced_Ads_Checks::cache()
			|| count( $group->get_all_ads() ) < 2
		) {
			return $hints;
		}

		if ( ! class_exists( 'Advanced_Ads_Pro' ) ) {
			$installed_plugins = get_plugins();

			if ( isset( $installed_plugins['advanced-ads-pro/advanced-ads-pro.php'] ) ) {
				$link       = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=advanced-ads-pro/advanced-ads-pro.php', 'activate-plugin_advanced-ads-pro/advanced-ads-pro.php' );
				$link_title = __( 'Activate now', 'advanced-ads' );
			} else {
				$link       = 'https://wpadvancedads.com/add-ons/advanced-ads-pro/?utm_source=advanced-ads&utm_medium=link&utm_campaign=groups-CB';
				$link_title = __( 'Get this add-on', 'advanced-ads' );
			}

			$hints[] = sprintf(
				wp_kses(
				// translators: %1$s is an URL, %2$s is a URL text
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
		 * @param string[]           $hints Existing hints (escaped strings).
		 * @param Advanced_Ads_Group $group The group object.
		 */
		return apply_filters( 'advanced-ads-group-hints', $hints, $group );
	}


}
