<?php
/**
 * Advanced Ads Ad.
 *
 * @package   Advanced_Ads_Ad
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright 2013-2020 Thomas Maier, Advanced Ads GmbH
 */

use AdvancedAds\Entities;
use AdvancedAds\Utilities\WordPress;

/**
 * An ad object
 *
 * @package Advanced_Ads_Ad
 * @author  Thomas Maier <support@wpadvancedads.com>
 * @deprecated since version 1.5.3 (May 6th 2015)
 *  might still be needed if some old add-ons are running somewhere
 */
if ( ! class_exists( 'Advads_Ad', false ) ) {
	class Advads_Ad extends Advanced_Ads_Ad {

	}
}

/**
 * An ad object
 *
 * @package Advanced_Ads_Ad
 * @author  Thomas Maier <support@wpadvancedads.com>
 */
class Advanced_Ads_Ad {

	/**
	 * Id of the post type for this ad
	 *
	 * @var int $id
	 */
	public $id = 0;

	/**
	 * True, if this is an Advanced Ads Ad post type
	 *
	 * @var bool $is_ad
	 */
	public $is_ad = false;

	/**
	 * Ad type
	 *
	 * @var string $type ad type.
	 */
	public $type = 'content';

	/**
	 * Notes about the ad usage
	 *
	 * @var string $description
	 */
	public $description = '';

	/**
	 * Ad width
	 *
	 * @var int $width width of the ad.
	 */
	public $width = 0;

	/**
	 * Target url
	 *
	 * @var string $url ad URL parameter.
	 */
	public $url = '';

	/**
	 * Ad height
	 *
	 * @var int $height height of the ad.
	 */
	public $height = 0;

	/**
	 * Object of current ad type
	 *
	 * @var Advanced_Ads_Ad_Type_Abstract $type_obj object of the current ad type.
	 */
	protected $type_obj;

	/**
	 * Content of the ad
	 *
	 * Only needed for ad types using the post content field
	 *
	 * @var string $content content of the ad.
	 */
	public $content = '';

	/**
	 * Conditions of the ad display
	 *
	 * @var array $conditions display and visitor conditions.
	 */
	public $conditions = [];

	/**
	 * Status of the ad (e.g. publish, pending)
	 *
	 * @var string $status status of the ad.
	 */
	public $status = '';

	/**
	 * Array with meta field options aka parameters
	 *
	 * @var array $options ad options.
	 */
	protected $options;

	/**
	 * Name of the meta field to save options to
	 *
	 * @var string $options_meta_field under which post meta key the ad options are stored.
	 */
	public static $options_meta_field = 'advanced_ads_ad_options';

	/**
	 * Additional arguments set when ad is loaded, overwrites or extends options
	 *
	 * @var array $args
	 */
	public $args = [];

	/**
	 * Multidimensional array contains information about the wrapper
	 * Each possible html attribute is an array with possible multiple elements
	 *
	 * @var array $wrapper options of the ad wrapper.
	 */
	public $wrapper = [];

	/**
	 * Will the ad be tracked?
	 *
	 * @var mixed $global_output
	 */
	public $global_output;

	/**
	 * Title of the ad
	 *
	 * @var string $title
	 */
	public $title = '';

	/**
	 * Displayed above the ad.
	 *
	 * @var string $label ad label.
	 */
	protected $label = '';

	/**
	 * Inline CSS object, one instance per ad.
	 *
	 * @var Advanced_Ads_Inline_Css
	 */
	private $inline_css;
	/**
	 * Timestamp if ad has an expiration date.
	 *
	 * @var int
	 */
	public $expiry_date = 0;

	/**
	 * The ad expiration object.
	 *
	 * @var Advanced_Ads_Ad_Expiration
	 */
	private $ad_expiration;

	/**
	 * The saved output options.
	 *
	 * @var array
	 */
	public $output;

	/**
	 * Whether the current ad is in a head placement.
	 *
	 * @var bool
	 */
	public $is_head_placement;

	/**
	 * Advanced_Ads_Ad constructor.
	 *
	 * @param int      $id   Ad ID.
	 * @param iterable $args Additional arguments.
	 */
	public function __construct( int $id, iterable $args = [] ) {
		if ( empty( $id ) ) {
			return;
		}

		$this->id   = $id;
		$this->args = is_array( $args ) ? $args : [];

		$post_data = get_post( $id );
		if ( $post_data === null || $post_data->post_type !== Entities::POST_TYPE_AD ) {
			return;
		}

		$this->is_ad    = true;
		$this->type     = $this->options( 'type' );
		$this->title    = $post_data->post_title;
		$this->type_obj = Advanced_Ads::get_instance()->ad_types[ $this->type ] ?? new Advanced_Ads_Ad_Type_Abstract();

		// filter the positioning options.
		new Advanced_Ads_Ad_Positioning( $this );

		$this->url                  = $this->get_url();
		$this->width                = absint( $this->options( 'width' ) );
		$this->height               = absint( $this->options( 'height' ) );
		$this->conditions           = $this->options( 'conditions' );
		$this->description          = $this->options( 'description' );
		$this->output               = $this->options( 'output' );
		$this->status               = $post_data->post_status;
		$this->expiry_date          = (int) $this->options( 'expiry_date' );
		$this->is_head_placement    = isset( $this->args['placement_type'] ) && 'header' === $this->args['placement_type'];
		$this->args['is_top_level'] = ! isset( $this->args['is_top_level'] );

		// load content based on ad type.
		$this->content = $this->type_obj->load_content( $post_data );

		if ( ! $this->is_head_placement ) {
			$this->maybe_create_label();
			$this->wrapper = $this->load_wrapper_options();

			// set wrapper conditions.
			$this->wrapper = apply_filters( 'advanced-ads-set-wrapper', $this->wrapper, $this );
			// add unique wrapper id.
			if ( is_array( $this->wrapper )
				 && [] !== $this->wrapper
				 && ! isset( $this->wrapper['id'] ) ) {
				// create unique id if not yet given.
				$this->wrapper['id'] = $this->create_wrapper_id();
			}
		}

		$this->ad_expiration = new Advanced_Ads_Ad_Expiration( $this );

		// dynamically add sanitize filters for condition types.
		$condition_types = array_unique( array_column( Advanced_Ads::get_instance()->get_model()->get_ad_conditions(), 'type' ) );
		foreach ( $condition_types as $condition_type ) {
			$method_name = 'sanitize_condition_' . $condition_type;
			if ( method_exists( $this, $method_name ) ) {
				add_filter( 'advanced-ads-sanitize-condition-' . $condition_type, [ $this, $method_name ], 10, 1 );
			} elseif ( function_exists( 'advads_sanitize_condition_' . $condition_type ) ) {
				// check for public function to sanitize this.
				add_filter( 'advanced-ads-sanitize-condition-' . $condition_type, 'advads_sanitize_condition_' . $condition_type, 10, 1 );
			}
		}

		// whether the ad will be tracked.
		$this->global_output = ! isset( $this->args['global_output'] ) || (bool) $this->args['global_output'];

		// Run constructor to check early if ajax cache busting already created inline css.
		$this->inline_css = new Advanced_Ads_Inline_Css();
	}

	/**
	 * Get options from meta field and return specific field
	 *
	 * @param string $field post meta key to be returned. Can be passed as array keys separated with `.`, i.e. 'parent.child' to retrieve multidimensional array values.
	 * @param array  $default default options.
	 *
	 * @return mixed meta field content
	 */
	public function options( $field = '', $default = null ) {
		// retrieve options, if not given yet
		if ( is_null( $this->options ) ) {
			// may return false.
			$meta = get_post_meta( $this->id, self::$options_meta_field, true );
			if ( $meta && is_array( $meta ) ) {
				// merge meta with arguments given on ad load.
				$this->options = Advanced_Ads_Utils::merge_deep_array( [ $meta, $this->args ] );
			} else {
				// load arguments given on ad load.
				$this->options = $this->args;
			}

			if ( isset( $this->options['change-ad'] ) ) {
				// some options was provided by the user.
				$this->options = Advanced_Ads_Utils::merge_deep_array(
					[
						$this->options,
						$this->options['change-ad'],
					]
				);
			}
		}

		// return all options if no field given.
		if ( empty( $field ) ) {
			return $this->options;
		}

		$field = preg_replace( '/\s/', '', $field );
		$value = $this->options;
		foreach ( explode( '.', $field ) as $key ) {
			if ( ! isset( $value[ $key ] ) ) {
				$value = $default;
				break;
			}
			$value = $value[ $key ];
		}

		if ( is_null( $value ) ) {
			$value = $default;
		}

		/**
		 * Filter the option value retrieved for $field.
		 * `$field` parameter makes dynamic hook portion.
		 *
		 * @var mixed           $value The option value (may be set to default).
		 * @var Advanced_Ads_Ad $this  The current Advanced_Ads_Ad instance.
		 */
		return apply_filters( "advanced-ads-ad-option-{$field}", $value, $this );
	}

	/**
	 * Set an option of the ad
	 *
	 * @param string $option name of the option.
	 * @param mixed  $value value of the option.
	 *
	 * @since 1.1.0
	 */
	public function set_option( $option = '', $value = '' ) {
		if ( '' === $option ) {
			return;
		}

		// get current options.
		$options = $this->options();

		// set options.
		$options[ $option ] = $value;

		// save options.
		$this->options = $options;

	}


	/**
	 * Return ad content for frontend output
	 *
	 * @param array $output_options output options.
	 *
	 * @return string $output ad output
	 * @since 1.0.0
	 */
	public function output( $output_options = [] ) {
		if ( ! $this->is_ad ) {
			return '';
		}

		$this->global_output             = isset( $output_options['global_output'] ) ? $output_options['global_output'] : $this->global_output;
		$output_options['global_output'] = $this->global_output;

		// switch between normal and debug mode.
		// check if debug output should only be displayed to admins.
		$user_can_manage_ads = WordPress::user_can( 'advanced_ads_manage_options' );
		if ( $this->options( 'output.debugmode' )
			 && ( $user_can_manage_ads || ( ! $user_can_manage_ads && ! defined( 'ADVANCED_ADS_AD_DEBUG_FOR_ADMIN_ONLY' ) ) ) ) {
			$debug = new Advanced_Ads_Ad_Debug();

			return $debug->prepare_debug_output( $this );
		} else {
			$output = $this->prepare_frontend_output();
		}

		// add the ad to the global output array.
		$advads = Advanced_Ads::get_instance();
		if ( $output_options['global_output'] ) {
			$new_ad = [
				'type'   => 'ad',
				'id'     => $this->id,
				'title'  => $this->title,
				'output' => $output,
			];
			// if ( method_exists( 'Advanced_Ads_Tracking_Plugin' , 'check_ad_tracking_enabled' ) ) {
			// if ( class_exists( 'Advanced_Ads_Tracking_Plugin', false ) ) {
			if ( defined( 'AAT_VERSION' ) && - 1 < version_compare( AAT_VERSION, '1.4.2' ) ) {

				$new_ad['tracking_enabled'] = Advanced_Ads_Tracking_Plugin::get_instance()->check_ad_tracking_enabled( $this );

				$tracking_options = Advanced_Ads_Tracking_Plugin::get_instance()->options();
				if ( isset( $tracking_options['method'] ) && 'frontend' === $tracking_options['method'] && isset( $this->output['placement_id'] ) ) {
					$new_ad['placement_id'] = $this->output['placement_id'];
				}
			}

			$advads->current_ads[] = $new_ad;
		}

		// action when output is created.
		do_action( 'advanced-ads-output', $this, $output, $output_options );

		return apply_filters( 'advanced-ads-output-final', $output, $this, $output_options );
	}

	/**
	 * Check if the ad can be displayed in frontend due to its own conditions
	 *
	 * @param array $check_options check options.
	 *
	 * @return bool $can_display true if can be displayed in frontend
	 * @since 1.0.0
	 */
	public function can_display( $check_options = [] ) {
		$check_options = wp_parse_args(
			$check_options,
			[
				'passive_cache_busting' => false,
				'ignore_debugmode'      => false,
			]
		);

		// prevent ad to show up through wp_head, if this is not a header placement.
		if ( doing_action( 'wp_head' ) && isset( $this->options['placement_type'] ) && 'header' !== $this->options['placement_type']
			&& ! Advanced_Ads_Compatibility::can_inject_during_wp_head() ) {
			return false;
		}

		// Check If the current ad is requested using a shortcode placed in the content of the current ad.
		if ( isset( $this->options['shortcode_ad_id'] ) && (int) $this->options['shortcode_ad_id'] === $this->id ) {
			return false;
		}

		// force ad display if debug mode is enabled.
		if ( isset( $this->output['debugmode'] ) && ! $check_options['ignore_debugmode'] ) {
			return true;
		}

		if ( ! $check_options['passive_cache_busting'] ) {
			// don’t display ads that are not published or private for users not logged in.
			if ( 'publish' !== $this->status && ! ( 'private' === $this->status && is_user_logged_in() ) ) {
				return false;
			}

			if ( ! $this->can_display_by_visitor() ) {
				return false;
			}
		} elseif ( 'publish' !== $this->status ) {
			return false;
		}

		if ( $this->ad_expiration->is_ad_expired() ) {
			return false;
		}

		// add own conditions to flag output as possible or not.
		return apply_filters( 'advanced-ads-can-display', true, $this, $check_options );
	}

	/**
	 * Check visitor conditions
	 *
	 * @return bool $can_display true if can be displayed in frontend based on visitor settings
	 * @since 1.1.0
	 */
	public function can_display_by_visitor() {
		if ( ! empty( $this->options['wp_the_query']['is_feed'] ) ) {
			return true;
		}

		$visitor_conditions = $this->options( 'visitors', [] );
		if ( empty( $visitor_conditions ) ) {
			return true;
		}

		$last_result = false;
		$length      = count( $visitor_conditions );

		for ( $i = 0; $i < $length; ++ $i ) {
			$_condition = current( $visitor_conditions );
			// ignore OR if last result was true.
			if ( $last_result && isset( $_condition['connector'] ) && 'or' === $_condition['connector'] ) {
				next( $visitor_conditions );
				continue;
			}
			$result      = Advanced_Ads_Visitor_Conditions::frontend_check( $_condition, $this );
			$last_result = $result;
			if ( ! $result ) {
				// return false only, if the next condition doesn’t have an OR operator.
				$next = next( $visitor_conditions );
				if ( ! isset( $next['connector'] ) || 'or' !== $next['connector'] ) {
					return false;
				}
			} else {
				next( $visitor_conditions );
			}
		}

		// check mobile condition.
		if ( isset( $visitor_conditions['mobile'] ) ) {
			switch ( $visitor_conditions['mobile'] ) {
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
	 * Check expiry date
	 *
	 * @return bool $can_display true if can be displayed in frontend based on expiry date
	 * @since 1.3.15
	 * @deprecated 1.31.0 This is an internal method and should not have been public.
	 */
	public function can_display_by_expiry_date() {
		return $this->ad_expiration->is_ad_expired();
	}

	/**
	 * Save an ad to the database
	 * takes values from the current state
	 */
	public function save() {
		global $wpdb;

		// remove slashes from content.
		$this->content = $this->prepare_content_to_save();

		$where = [ 'ID' => $this->id ];
		$wpdb->update( $wpdb->posts, [ 'post_content' => $this->content ], $where );

		// clean post from object cache.
		clean_post_cache( $this->id );

		// sanitize conditions
		// see sanitize_conditions function for example on using this filter.
		$conditions = self::sanitize_conditions_on_save( $this->conditions );

		// save other options to post meta field.
		$options = $this->options();

		$options['type'] = $this->type;
		$options['url']  = $this->url;
		// Inform the tracking add-on about the new url.
		unset( $options['tracking']['link'] );
		$options['width']       = $this->width;
		$options['height']      = $this->height;
		$options['conditions']  = $conditions;
		$options['expiry_date'] = $this->expiry_date;
		$options['description'] = $this->description;

		// save the plugin version, with every ad save.
		$options['last_save_version'] = ADVADS_VERSION;

		// sanitize container ID option.
		$options['output']['wrapper-id'] = isset( $options['output']['wrapper-id'] ) ? sanitize_key( $options['output']['wrapper-id'] ) : '';

		// sanitize options before saving
		$options = $this->prepare_options_to_save( $options );

		// filter to manipulate options or add more to be saved.
		$options = apply_filters( 'advanced-ads-save-options', $options, $this );

		update_post_meta( $this->id, self::$options_meta_field, $options );
	}

	/**
	 * Save ad options.
	 * Meant to be used from the outside of an ad.
	 *
	 * @param int   $ad_id post ID of the ad.
	 * @param array $options ad options.
	 */
	public static function save_ad_options( $ad_id, array $options ) {

		// don’t allow to clear options by accident.
		if ( [] === $options ) {
			return;
		}

		update_post_meta( $ad_id, self::$options_meta_field, $options );
	}

	/**
	 * Native filter for content field before being saved
	 *
	 * @return string $content ad content
	 */
	public function prepare_content_to_save() {

		$content = $this->content;

		// load ad type specific parameter filter
		// @todo this is just a hotfix for type_obj not set, yet the cause is still unknown. Likely when the ad is first saved
		if ( is_object( $this->type_obj ) ) {
			$content = $this->type_obj->sanitize_content( $content );
		}
		// apply a custom filter by ad type.
		$content = apply_filters( 'advanced-ads-pre-ad-save-' . $this->type, $content );

		return $content;
	}

	/**
	 * Sanitize ad options before being saved
	 * allows some ad types to sanitize certain values
	 *
	 * @param array $options ad options.
	 * @return array sanitized options.
	 */
	public function prepare_options_to_save( $options ) {

		// load ad type specific sanitize function.
		// we need to load the ad type object if not set (e.g., when the ad is saved for the first time)
		if ( ! is_object( $this->type_obj ) || ! $this->type_obj->ID ) {
			$types = Advanced_Ads::get_instance()->ad_types;
			if ( isset( $types[ $this->type ] ) ) {
				$this->type_obj = $types[ $this->type ];
			}
		}

		$options = $this->type_obj->sanitize_options( $options );

		return $options;
	}

	/**
	 * Prepare ads output
	 *
	 * @return string.
	 */
	public function prepare_frontend_output() {
		$options = $this->options();

		if ( isset( $options['change-ad']['content'] ) ) {
			// output was provided by the user.
			$output = $options['change-ad']['content'];
		} else {
			// load ad type specific content filter.
			$output = $this->type_obj->prepare_output( $this );
		}

		// don’t deliver anything, if main ad content is empty.
		if ( empty( $output ) ) {
			return '';
		}

		if ( ! $this->is_head_placement ) {
			// filter to manipulate the output before the wrapper is added
			$output = apply_filters( 'advanced-ads-output-inside-wrapper', $output, $this );

			// build wrapper around the ad.
			$output = $this->add_wrapper( $output );

			// add a clearfix, if set.
			if (
				( ! empty( $this->args['is_top_level'] ) && ! empty( $this->args['placement_clearfix'] ) )
				|| $this->options( 'output.clearfix' )
			) {
				$output .= '<br style="clear: both; display: block; float: none;"/>';
			}
		}

		// apply a custom filter by ad type.
		$output = apply_filters( 'advanced-ads-ad-output', $output, $this );

		return $output;
	}

	/**
	 * Sanitize ad display conditions when saving the ad
	 *
	 * @param array $conditions conditions array send via the dashboard form for an ad.
	 *
	 * @return array with sanitized conditions
	 * @since 1.0.0
	 */
	public function sanitize_conditions_on_save( $conditions = [] ) {

		global $advanced_ads_ad_conditions;

		if ( ! is_array( $conditions ) || [] === $conditions ) {
			return [];
		}

		foreach ( $conditions as $_key => $_condition ) {
			if ( 'postids' === $_key ) {
				// sanitize single post conditions
				if ( empty( $_condition['ids'] ) ) { // remove, if empty.
					$_condition['include'] = [];
					$_condition['exclude'] = [];
				} elseif ( isset( $_condition['method'] ) ) {
					switch ( $_condition['method'] ) {
						case 'include':
							$_condition['include'] = $_condition['ids'];
							$_condition['exclude'] = [];
							break;
						case 'exclude':
							$_condition['include'] = [];
							$_condition['exclude'] = $_condition['ids'];
							break;
					}
				}
			} else {
				if ( ! is_array( $_condition ) ) {
					$_condition = trim( $_condition );
				}
				if ( $_condition == '' ) {
					$conditions[ $_key ] = $_condition;
					continue;
				}
			}
			$type = ! empty( $advanced_ads_ad_conditions[ $_key ]['type'] ) ? $advanced_ads_ad_conditions[ $_key ]['type'] : 0;
			if ( empty( $type ) ) {
				continue;
			}

			// dynamically apply filters for each condition used.
			$conditions[ $_key ] = apply_filters( 'advanced-ads-sanitize-condition-' . $type, $_condition );
		}

		return $conditions;
	}

	/**
	 * Sanitize id input field(s) for pattern /1,2,3,4/
	 *
	 * @param mixed $cond input string/array.
	 *
	 * @return array/string $cond sanitized string/array
	 */
	public static function sanitize_condition_idfield( $cond = '' ) {
		// strip anything that is not comma or number.

		if ( is_array( $cond ) ) {
			foreach ( $cond as $_key => $_cond ) {
				$cond[ $_key ] = preg_replace( '#[^0-9,]#', '', $_cond );
			}
		} else {
			$cond = preg_replace( '#[^0-9,]#', '', $cond );
		}

		return $cond;
	}

	/**
	 * Sanitize radio input field
	 *
	 * @param string $string input string.
	 *
	 * @return string $string sanitized string.
	 */
	public static function sanitize_condition_radio( $string = '' ) {
		// only allow 0, 1 and empty.
		return preg_replace( '#[^01]#', '', $string );
	}

	/**
	 * Sanitize comma seperated text input field
	 *
	 * @param mixed $cond input string/array.
	 *
	 * @return array/string $cond sanitized string/array.
	 */
	public static function sanitize_condition_textvalues( $cond = '' ) {
		// strip anything that is not comma, alphanumeric, minus and underscore.
		if ( is_array( $cond ) ) {
			foreach ( $cond as $_key => $_cond ) {
				$cond[ $_key ] = preg_replace( '#[^0-9,A-Za-z-_]#', '', $_cond );
			}
		} else {
			$cond = preg_replace( '#[^0-9,A-Za-z-_]#', '', $cond );
		}

		return $cond;
	}

	/**
	 * Load wrapper options set with the ad
	 *
	 * @return array $wrapper options array ready to be use in add_wrapper() function.
	 * @since 1.3
	 */
	protected function load_wrapper_options() {
		$wrapper = [];

		$position          = $this->options( 'output.position', '' );
		$use_placement_pos = false;

		if ( $this->args['is_top_level'] ) {
			if ( isset( $this->output['class'] ) && is_array( $this->output['class'] ) ) {
				$wrapper['class'] = $this->output['class'];
			}
			if ( ! empty( $this->args['placement_position'] ) ) {
				// If not group, Set placement position instead of ad position.
				$use_placement_pos = true;
				$position          = $this->args['placement_position'];
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

				if (
					( ! $this->width || empty( $this->output['add_wrapper_sizes'] ) )
					|| $use_placement_pos
				) {
					$wrapper['style']['text-align'] = 'center';
				}

				// add css rule after wrapper to center the ad.
				break;
			case 'clearfix':
				$wrapper['style']['clear'] = 'both';
				break;
		}

		// add manual classes.
		if ( isset( $this->output['wrapper-class'] ) && '' !== $this->output['wrapper-class'] ) {
			$classes = explode( ' ', $this->output['wrapper-class'] );

			foreach ( $classes as $_class ) {
				$wrapper['class'][] = sanitize_text_field( $_class );
			}
		}

		if ( ! empty( $this->output['margin']['top'] ) ) {
			$wrapper['style']['margin-top'] = (int) $this->output['margin']['top'] . 'px';
		}
		if ( empty( $wrapper['style']['margin-right'] ) && ! empty( $this->output['margin']['right'] ) ) {
			$wrapper['style']['margin-right'] = (int) $this->output['margin']['right'] . 'px';
		}
		if ( ! empty( $this->output['margin']['bottom'] ) ) {
			$wrapper['style']['margin-bottom'] = (int) $this->output['margin']['bottom'] . 'px';
		}
		if ( empty( $wrapper['style']['margin-left'] ) && ! empty( $this->output['margin']['left'] ) ) {
			$wrapper['style']['margin-left'] = (int) $this->output['margin']['left'] . 'px';
		}

		if ( ! empty( $this->output['add_wrapper_sizes'] ) ) {
			if ( ! empty( $this->width ) ) {
				$wrapper['style']['width'] = $this->width . 'px';
			}
			if ( ! empty( $this->height ) ) {
				$wrapper['style']['height'] = $this->height . 'px';
			}
		}

		if ( ! empty( $this->output['clearfix_before'] ) ) {
			$wrapper['style']['clear'] = 'both';
		}

		return $wrapper;
	}

	/**
	 * Add a wrapper arount the ad content if wrapper information are given
	 *
	 * @param string $ad_content content of the ad.
	 *
	 * @return string $wrapper ad within the wrapper
	 * @since 1.1.4
	 */
	protected function add_wrapper( $ad_content = '' ) {
		$wrapper_options = apply_filters( 'advanced-ads-output-wrapper-options', $this->wrapper, $this );

		if ( $this->label && ! empty( $wrapper_options['style']['height'] ) ) {
			// Create another wrapper so that the label does not reduce the height of the ad wrapper.
			$height = [ 'style' => [ 'height' => $wrapper_options['style']['height'] ] ];
			unset( $wrapper_options['style']['height'] );
			$ad_content = '<div' . Advanced_Ads_Utils::build_html_attributes( $height ) . '>' . $ad_content . '</div>';
		}

		// Adds inline css to the wrapper.
		if ( ! empty( $this->options['inline-css'] ) && $this->args['is_top_level'] ) {
			$wrapper_options = $this->inline_css->add_css( $wrapper_options, $this->options['inline-css'], $this->global_output );
		}

		if (
			! defined( 'ADVANCED_ADS_DISABLE_EDIT_BAR' )
			// Add edit button for users with the appropriate rights.
			&& WordPress::user_can( 'advanced_ads_edit_ads' )
			// We need a wrapper. Check if at least the placement wrapper exists.
			&& ! empty( $this->args['placement_type'] )
		) {
			ob_start();
			include ADVADS_ABSPATH . 'public/views/ad-edit-bar.php';
			$ad_content = trim( ob_get_clean() ) . $ad_content;
			// Include the tooltip title from get_tooltip_title() in the 'title' attribute.
			$this->output['wrapper_attrs']['data-title'][] = $this->get_tooltip_title();
		}

		// ad Health Tool add class and attribute in to ads and group
		if ( WordPress::user_can('advanced_ads_edit_ads') ) {

			$has_group_info = isset($this->args['group_info']);
			$frontend_prefix = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();

			if (  ! $has_group_info ) {
				// Add the 'highlight-wrapper' class to the ad wrapper
				$wrapper_options['class'][] = $frontend_prefix . 'highlight-wrapper';
			}

		}

		if ('' === ($this->output['wrapper-id'] ?? '')
			&& ( [] === $wrapper_options || ! is_array($wrapper_options) )) {
			return $this->label . $ad_content;
		}

		// create unique id if not yet given.
		if ( empty( $wrapper_options['id'] ) ) {
			$wrapper_options['id'] = $this->create_wrapper_id();
			$this->wrapper['id']   = $wrapper_options['id'];
		}

		$wrapper_element = ! empty( $this->args['inline_wrapper_element'] ) ? 'span' : 'div';

		// build the box
		$wrapper = '<' . $wrapper_element . Advanced_Ads_Utils::build_html_attributes( array_merge(
			$wrapper_options,
			isset( $this->output['wrapper_attrs'] ) ? $this->output['wrapper_attrs'] : []
		) ) . '>';
		$wrapper .= $this->label;
		$wrapper .= apply_filters( 'advanced-ads-output-wrapper-before-content', '', $this );
		$wrapper .= $ad_content;
		$wrapper .= apply_filters( 'advanced-ads-output-wrapper-after-content', '', $this );
		$wrapper .= '</' . $wrapper_element . '>';

		return $wrapper;
	}

	/**
	 * Create a random wrapper id
	 *
	 * @return string $id random id string
	 * @since 1.1.4
	 */
	private function create_wrapper_id() {

		if ( isset( $this->output['wrapper-id'] ) ) {
			$id = sanitize_key( $this->output['wrapper-id'] );
			if ( '' !== $id ) {
				return $id;
			}
		}

		$prefix = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();

		return $prefix . mt_rand();
	}

	/**
	 * Create an "Advertisement" label if conditions are met.
	 */
	public function maybe_create_label() {
		$placement_state = isset( $this->args['ad_label'] ) ? $this->args['ad_label'] : 'default';

		$label = Advanced_Ads::get_instance()->get_label( $placement_state );

		if ( $this->args['is_top_level'] && $label ) {
			$this->label = $label;
		}
	}

	/**
	 * Get the ad url.
	 *
	 * @return string
	 */
	private function get_url() {
		$this->url = $this->options( 'url' );

		// If the tracking add-on is not active.
		if ( ! defined( 'AAT_VERSION' ) ) {
			global $pagenow;
			// If this is not the ad edit page.
			if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow && ! empty( $this->url ) ) {
				// Remove placeholders.
				$this->url = str_replace(
					[
						'[POST_ID]',
						'[POST_SLUG]',
						'[CAT_SLUG]',
						'[AD_ID]',
					],
					'',
					$this->url
				);
			}
		}

		return $this->url;
	}

	/**
	 * Generate the tooltip title for a placement with associated ads.
	 *
	 * @return string Tooltip title containing placement and ads name.
	 */
	private function get_tooltip_title() {
		$ads = [];

		// Check if a group ID is provided in the arguments.
		if ( isset( $this->args['group_info']['id'] ) ) {
			// Create an instance of Advanced_Ads_Group using the provided group ID.
			$group = new Advanced_Ads_Group( $this->args['group_info']['id'] );
			// Get all ads within the group and extract their post titles.
			$ads = wp_list_pluck( $group->get_all_ads(), 'post_title' );
		} else {

			// If no group ID is provided, get ads directly from the Advanced_Ads model.
			$ads =  wp_list_pluck(
						Advanced_Ads::get_instance()->get_model()->get_ads(
							[
								'post__in'           => [ $this->args['id'] ]
							]
						),
					'post_title'
					);
		}

		// Construct and format the tooltip title using the placement ID and ad titles.
		return sprintf(
				// translators: %1$s is a placement name, %2$s is the ads name.
					__( 'Placement name: %1$s; Ads: %2$s', 'advanced-ads' ),
					esc_attr( $this->args['output']['placement_id'] ?? '' ),
					esc_attr( $ads ? implode( ',', $ads ) : '' )
				);
	}
}
