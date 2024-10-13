<?php
/**
 * Advanced Ads Widget
 *
 * @package   Advanced_Ads_Widget
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright 2014 Thomas Maier, Advanced Ads GmbH
 */

use AdvancedAds\Utilities\Groups;

/**
 * Ad widget
 */
class Advanced_Ads_Widget extends WP_Widget {

	/**
	 * Allows the REST API to see the widgets instance.
	 *
	 * @deprecated deprecated since WordPress version 5.8
	 * @var bool
	 */
	public $show_instance_in_rest = true;

	/**
	 * Advanced_Ads_Widget constructor.
	 */
	public function __construct() {
		$prefix    = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();
		$classname = $prefix . 'widget';

		$widget_ops  = [
			'classname'             => $classname,
			'show_instance_in_rest' => true,
			'description'           => __( 'Display Ads and Ad Groups.', 'advanced-ads' ),
		];
		$control_ops = [];
		$base_id     = self::get_base_id();

		parent::__construct( $base_id, 'Advanced Ads', $widget_ops, $control_ops );

		add_filter( 'q2w3-fixed-widgets', [ $this, 'q2w3_replace_frontend_id' ] );
	}

	/**
	 * Echoes the widget content.
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		$item_id = empty( $instance['item_id'] ) ? '' : $instance['item_id'];

		$output = self::output( $item_id );
		if ( ! $output ) {
			return;
		}

		$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '';
		$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : '';

		$before_widget = $this->maybe_replace_frontend_id( $before_widget, $instance );

		echo $before_widget;
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo $output;
		echo $after_widget;
	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * This function should check that `$new_instance` is set correctly. The newly-calculated
	 * value of `$instance` should be returned. If false is returned, the instance won't be
	 * saved/updated.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance            = $old_instance;
		$instance['title']   = $new_instance['title'];
		$instance['item_id'] = $new_instance['item_id'];

		// Allow to remove/replace id for new widgets and if it was allowed earlier.
		if ( [] === $old_instance || ! empty( $old_instance['remove-widget-id'] ) ) {
			$instance['remove-widget-id'] = true;
		}
		return $instance;
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$instance  = wp_parse_args(
			(array) $instance,
			[
				'title'   => '',
				'item_id' => '',
			]
		);
		$title     = strip_tags( $instance['title'] );
		$elementid = $instance['item_id'];

		?><p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'advanced-ads' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
												  <?php
													$items = array_merge( self::items_for_select(), self::widget_placements_for_select() );
													?>
		<select id="<?php echo esc_attr( $this->get_field_id( 'item_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'item_id' ) ); ?>">
			<option value=""><?php esc_html_e( '--empty--', 'advanced-ads' ); ?></option>
			<?php if ( isset( $items['placements'] ) ) : ?>
			<optgroup label="<?php esc_html_e( 'Placements', 'advanced-ads' ); ?>">
				<?php foreach ( $items['placements'] as $_item_id => $_item_title ) : ?>
			<option value="<?php echo esc_attr( $_item_id ); ?>" <?php selected( $_item_id, $elementid ); ?>><?php echo esc_attr( $_item_title ); ?></option>
			<?php endforeach; ?>
			</optgroup>
			<?php endif; ?>
			<?php if ( isset( $items['groups'] ) ) : ?>
			<optgroup label="<?php esc_html_e( 'Ad Groups', 'advanced-ads' ); ?>">
				<?php foreach ( $items['groups'] as $_item_id => $_item_title ) : ?>
			<option value="<?php echo esc_attr( $_item_id ); ?>" <?php selected( $_item_id, $elementid ); ?>><?php echo esc_html( $_item_title ); ?></option>
			<?php endforeach; ?>
			</optgroup>
			<?php endif; ?>
			<?php if ( isset( $items['ads'] ) ) : ?>
			<optgroup label="<?php esc_html_e( 'Ads', 'advanced-ads' ); ?>">
				<?php foreach ( $items['ads'] as $_item_id => $_item_title ) : ?>
			<option value="<?php echo esc_attr( $_item_id ); ?>" <?php selected( $_item_id, $elementid ); ?>><?php echo esc_html( $_item_title ); ?></option>
			<?php endforeach; ?>
			</optgroup>
			<?php endif; ?>
		</select>

		<?php
		$this->display_hints( $elementid );
	}

	/**
	 * Display hints related to the currently selected item in the dropdown.
	 *
	 * @param string $elementid Currently selected item.
	 */
	private function display_hints( $elementid ) {
		$elementid_parts = explode( '_', $elementid );
		if ( ! isset( $elementid_parts[1] ) || $elementid_parts[0] !== 'group' ) {
			return;
		}

		echo Groups::build_hints_html( new Advanced_Ads_Group( $elementid_parts[1] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	 /**
	  * Get items for widget select field
	  *
	  * @return array $select items for select field.
	  */
	public static function items_for_select() {
		$select = [];
		$model  = Advanced_Ads::get_instance()->get_model();

		// load all ads.
		$ads = $model->get_ads(
			[
				'orderby' => 'title',
				'order'   => 'ASC',
			]
		);
		foreach ( $ads as $_ad ) {
			$select['ads'][ 'ad_' . $_ad->ID ] = $_ad->post_title;
		}

		// load all ad groups.
		$groups = $model->get_ad_groups();
		foreach ( $groups as $_group ) {
			$select['groups'][ 'group_' . $_group->term_id ] = $_group->name;
		}

		return $select;
	}

	/**
	 * Get widget placements for select field
	 *
	 * @return array $items for select field.
	 */
	public static function widget_placements_for_select() {
		$select     = [];
		$placements = Advanced_Ads::get_instance()->get_model()->get_ad_placements_array();

		if ( is_array( $placements ) ) {
			ksort( $placements );
		}

		foreach ( $placements as $placement_slug => $placement ) {
			if ( isset( $placement['type'] ) && in_array( $placement['type'], [ 'sidebar_widget', 'default' ], true ) ) {
				$select['placements'][ 'placement_' . $placement_slug ] = $placement['name'];
			}
		}

		return $select;
	}

	/**
	 * Return content of an in a widget
	 *
	 * @param string $id slug of the display.
	 * @return bool|string
	 */
	public static function output( $id = '' ) {
		// get placement data for the slug.
		if ( empty( $id ) ) {
			return; }

		$item = explode( '_', $id, 2 );

		if ( isset( $item[1] ) ) {
			$item_id = $item[1];
		} elseif ( empty( $item_id ) ) {
			return;
		}

		// return either ad or group content.
		if ( 'ad' === $item[0] ) {
			return get_ad( absint( $item_id ) );
		} elseif ( 'group' === $item[0] ) {
			return get_ad_group( absint( $item_id ) );
		} elseif ( 'placement' === $item[0] ) {
			return get_ad_placement( $item_id );
		}
	}

	/**
	 * Get the base id of the widget
	 *
	 * @return string
	 */
	public static function get_base_id() {
		$options = Advanced_Ads_Plugin::get_instance()->options();

		// deprecated to keep previously changed prefixed working.
		$prefix2 = ( isset( $options['id-prefix'] ) && '' !== $options['id-prefix'] ) ? $options['id-prefix'] : 'advads_ad_';
		return $prefix2 . 'widget';
	}

	/**
	 * Get frontend widget id.
	 *
	 * @param int $number Unique ID number of the current widget instance.
	 * @return string
	 */
	private function get_frontend_id( $number ) {
		$prefix = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();
		return $prefix . 'widget-' . $number;
	}

	/**
	 * Make it harder for ad blockers to block the widget.
	 * removes the pre-defined widget ID (e.g., advads_ad_widget-20) and replaces it with one that uses the individual frontend prefix
	 *
	 * @param string $before_widget content before the widget.
	 * @param array  $instance Settings for the current widget instance.
	 * @return string $before_widget
	 */
	private function maybe_replace_frontend_id( $before_widget, $instance ) {
		if ( ! empty( $instance['remove-widget-id'] )
			|| defined( 'JNEWS_THEME_ID' ) // the JNews theme overrides the widget ID and resets it, so we target this specifically.
			) {
			$pattern = '#\sid=("|\')[^"\']+["\']#';
			if ( ( defined( 'ADVANCED_ADS_SHOW_WIDGET_ID' ) && ADVANCED_ADS_SHOW_WIDGET_ID )
				|| ! empty( $instance['q2w3_fixed_widget'] )
			) {
				// Replace id.
				$number        = ! empty( $this->number ) ? $this->number : '';
				$before_widget = preg_replace( $pattern, ' id=$01' . $this->get_frontend_id( $number ) . '$01', $before_widget );
			} else {
				// Remove id.
				$before_widget = preg_replace( $pattern, '', $before_widget );
			}
		}
		return $before_widget;
	}

	/**
	 * Provide the 'Q2W3 Fixed Widget' plugin with the new frontend widget id.
	 *
	 * @param array $sidebars_widgets existing sidebar widgets.
	 * @return array $sidebars_widgets
	 */
	public function q2w3_replace_frontend_id( $sidebars_widgets ) {
		foreach ( $sidebars_widgets as $sidebar => $widgets ) {
			foreach ( $widgets as $k => $widget ) {
				// after Fixed Widget 5.3.0, the widget option includes '#'. It didn’t before. We store the information since we need it later again.
				$has_hash    = strpos( $widget, '#' ) !== false;
				$widget      = str_replace( '#', '', $widget );
				$pos         = strrpos( $widget, '-' );
				$option_name = substr( $widget, 0, $pos );
				$number      = substr( $widget, $pos + 1 );

				if ( self::get_base_id() === $option_name ) {
					$widget_options = get_option( 'widget_' . $option_name );
					if ( ! empty( $widget_options[ $number ]['remove-widget-id'] ) ) {
						// add a hash if the widget had one before. See comment above
						$sidebars_widgets[ $sidebar ][ $k ] = $has_hash ? ( '#' . $this->get_frontend_id( $number ) ) : $this->get_frontend_id( $number );
					}
				}
			}
		}
		return $sidebars_widgets;
	}

}
