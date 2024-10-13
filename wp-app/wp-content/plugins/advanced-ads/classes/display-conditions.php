<?php
// phpcs:ignoreFile
/**
 * Display Conditions under which to (not) show an ad
 *
 * @since 1.7
 *
 */
class Advanced_Ads_Display_Conditions {
	/**
	 * Advanced_Ads_Display_Conditions
	 *
	 * @var Advanced_Ads_Display_Conditions
	 */
	protected static $instance;

	/**
	 * Conditonis
	 *
	 * Registered display conditions.
	 *
	 * @var $conditions
	 */
	public $conditions;

	/**
	 * Start of name in form elements
	 */
	const FORM_NAME = 'advanced_ad[conditions]';

	/**
	 * List of query var values
	 *
	 * @var array
	 */
	protected static $query_var_keys = [
		// 'is_single',
		'is_archive',
		'is_search',
		'is_home',
		'is_404',
		'is_attachment',
		'is_singular',
		'is_front_page',
		'is_feed',
	];

	/**
	 * List of options for the General Conditions
	 *
	 * @var array
	 */

	protected static $default_general_keys = [
		'is_front_page',
		'is_singular',
		'is_archive',
		'is_search',
		'is_404',
		'is_attachment',
		'is_main_query',
		'is_feed',
		'is_rest_api',
	];

	/**
	 * Constructor
	 */
	private function __construct() {

		// register filter.
		add_filter( 'advanced-ads-ad-select-args', [ $this, 'ad_select_args_callback' ] );
		add_filter( 'advanced-ads-can-display', [ $this, 'can_display' ], 10, 2 );

		// register conditions with init hook, register as late as possible so other plugins can use the same hook to add new taxonomies.
		add_action( 'init', [ $this, 'register_conditions' ], 100 );
	}

	/**
	 * Register display conditions
	 *
	 * @since 1.7.1.4
	 */
	public function register_conditions() {
		$conditions = [
			// post types condition.
			'posttypes'   => [
				'label'       => __( 'post type', 'advanced-ads' ),
				'description' => __( 'Choose the public post types on which to display the ad.', 'advanced-ads' ),
				'metabox'     => [ 'Advanced_Ads_Display_Conditions', 'metabox_post_type' ], // callback to generate the metabox.
				'check'       => [ 'Advanced_Ads_Display_Conditions', 'check_post_type' ], // callback for frontend check.
			],
			// post id condition.
			'postids'     => [
				'label'       => __( 'specific pages', 'advanced-ads' ),
				'description' => __( 'Choose on which individual posts, pages and public post type pages you want to display or hide ads.', 'advanced-ads' ),
				'metabox'     => [ 'Advanced_Ads_Display_Conditions', 'metabox_post_ids' ], // callback to generate the metabox.
				'check'       => [ 'Advanced_Ads_Display_Conditions', 'check_post_ids' ], // callback for frontend check.
			],
			// general conditions.
			'general'     => [
				'label'   => __( 'general conditions', 'advanced-ads' ),
				'metabox' => [ 'Advanced_Ads_Display_Conditions', 'metabox_general' ], // callback to generate the metabox.
				'check'   => [ 'Advanced_Ads_Display_Conditions', 'check_general' ], // callback for frontend check.
			],
			// author conditions.
			'author'      => [
				'label'   => __( 'author', 'advanced-ads' ),
				'metabox' => [ 'Advanced_Ads_Display_Conditions', 'metabox_author' ], // callback to generate the metabox.
				'check'   => [ 'Advanced_Ads_Display_Conditions', 'check_author' ], // callback for frontend check.
			],
			// display ads only in content older or younger than a specific age.
			'content_age' => [
				'label'       => __( 'content age', 'advanced-ads' ),
				'description' => __( 'Display ads based on age of the page.', 'advanced-ads' ),
				'metabox'     => [ 'Advanced_Ads_Display_Conditions', 'metabox_content_age' ], // callback to generate the metabox.
				'check'       => [ 'Advanced_Ads_Display_Conditions', 'check_content_age' ], // callback for frontend check.
			],
			// condition for taxonomies in general.
			'taxonomy'    => [
				'label'       => __( 'taxonomy', 'advanced-ads' ),
				'description' => __( 'Display ads based on the taxonomy of an archive page.', 'advanced-ads' ),
				'metabox'     => [ 'Advanced_Ads_Display_Conditions', 'metabox_taxonomies' ], // callback to generate the metabox.
				'check'       => [ 'Advanced_Ads_Display_Conditions', 'check_taxonomy' ], // callback for frontend check.
			],
		];

		// register a condition for each taxonomy for posts.
		$taxonomies = get_taxonomies(
			[
				'public'             => true,
				'publicly_queryable' => true,
			],
			'objects',
			'or'
		);

		$tax_label_counts = [];

		foreach ( $taxonomies as $_tax ) :
			if ( in_array( $_tax->name, [ 'advanced_ads_groups' ], true ) ) {
				continue;
			}

			/**
			 * Count names of taxonomies and adjust label if there are duplicates.
			 * we can’t use `array_count_values` here because "label" might not always be a simple string (though it should be)
			 */
			$tax_label_counts[ $_tax->label ] = isset( $tax_label_counts[ $_tax->label ] ) ? $tax_label_counts[ $_tax->label ] + 1 : $tax_label_counts[ $_tax->label ] = 1;

			// add tax type to label if we find it multiple times.
			if ( $tax_label_counts[ $_tax->label ] < 2 ) {
				$archive_label = $_tax->label;
				$label         = $archive_label;
			} else {
				$label         = sprintf( '%s (%s)', $_tax->label, $_tax->name );
				$archive_label = sprintf( '%s (%s)', $_tax->labels->singular_name, $_tax->name );
			}

			$conditions[ 'taxonomy_' . $_tax->name ] = [
				'label'    => $label,
				'metabox'  => [ 'Advanced_Ads_Display_Conditions', 'metabox_taxonomy_terms' ], // callback to generate the metabox.
				'check'    => [ 'Advanced_Ads_Display_Conditions', 'check_taxonomies' ], // callback for frontend check.
				'taxonomy' => $_tax->name, // unique for this type: the taxonomy name.
			];

			$conditions[ 'archive_' . $_tax->name ] = [
				'label'    => sprintf(
				// translators: %s is a label of an archive page.
					__( 'archive: %s', 'advanced-ads' ),
					$archive_label
				),
				'metabox'  => [ 'Advanced_Ads_Display_Conditions', 'metabox_taxonomy_terms' ], // callback to generate the metabox.
				'check'    => [ 'Advanced_Ads_Display_Conditions', 'check_taxonomy_archive' ], // callback for frontend check.
				'taxonomy' => $_tax->name, // unique for this type: the taxonomy name.
			];
		endforeach;

		$this->conditions = apply_filters( 'advanced-ads-display-conditions', $conditions );
	}

	/**
	 * Instance of Advanced_Ads_Display_Conditions
	 *
	 * @return Advanced_Ads_Display_Conditions
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Get the conditions array alphabetically by label
	 *
	 * @since 1.8.12
	 */
	public function get_conditions() {
		uasort( $this->conditions, 'Advanced_Ads_Admin::sort_condition_array_by_label' );

		return $this->conditions;
	}

	/**
	 * Controls frontend checks for conditions
	 *
	 * @param array $options options of the condition.
	 * @param mixed $ad false or Advanced_Ads_Ad object.
	 *
	 * @return bool false, if ad can’t be delivered
	 */
	public static function frontend_check( $options = [], $ad = false ) {
		$display_conditions = self::get_instance()->conditions;

		if ( is_array( $options ) && isset( $options['type'] ) && isset( $display_conditions[ $options['type'] ]['check'] ) ) {
			$check = $display_conditions[ $options['type'] ]['check'];
		} else {
			return true;
		}

		// call frontend check callback.
		if ( method_exists( $check[0], $check[1] ) ) {
			return call_user_func( [ $check[0], $check[1] ], $options, $ad );
		}

		return true;
	}

	/**
	 * Render the list of set display conditions.
	 *
	 * @param array  $set_conditions array of existing conditions.
	 * @param string $list_target ID of the list with the conditions.
	 * @param string $form_name prefix of the form field name attribute.
	 * @param array  $options {
	 *     Optional. Render options.
	 *
	 *     @type string/array $in May be:
	 *     - set to `all` (default) to include all conditions, including not global.
	 *     - set to `global` to include all conditions, except for not global.
	 *     - set to array of condition ID's to include only them.
	 * }
	 */
	public static function render_condition_list( array $set_conditions, $list_target = '', $form_name = '', $options = [] ) {
		$conditions = self::get_instance()->get_conditions();

		if ( isset( $options['in'] ) ) {
			if ( 'global' === $options['in'] ) {
				$conditions = array_filter(
					$conditions,
					function ( $condition ) {
						return ! isset( $condition['options']['global'] ) || $condition['options']['global'];
					}
				);
			} elseif ( is_array( $options['in'] ) ) {
				// Include already set condition types.
				$set_types = [];
				foreach ( $set_conditions as $set_condition ) {
					if ( isset( $set_condition['type'] ) ) {
						$set_types[] = $set_condition['type'];
					}
				}

				$in         = array_merge( $options['in'], $set_types );
				$conditions = array_intersect_key( $conditions, array_flip( $in ) );
			}
		}

		// use default form name if not given explicitly.
		// @todo create a random form name, in case we have more than one form per page and the parameter is not given.
		$form_name = ! $form_name ? self::FORM_NAME : $form_name;

		include ADVADS_ABSPATH . 'admin/views/conditions/display-conditions-list.php';

		/**
		 * Prepare condition form
		 *
		 * @todo if needed, allow to disable the form to add new conditions
		 */

		// add mockup conditions if add-ons are missing.
		$pro_conditions = [];
		if ( ! defined( 'AAP_VERSION' ) ) {
			$pro_conditions[] = __( 'parent page', 'advanced-ads' );
			$pro_conditions[] = __( 'post meta', 'advanced-ads' );
			$pro_conditions[] = __( 'page template', 'advanced-ads' );
			$pro_conditions[] = __( 'url parameters', 'advanced-ads' );
		}
		if ( ! defined( 'AAR_VERSION' ) ) {
			$pro_conditions[] = __( 'accelerated mobile pages', 'advanced-ads' );
		}
		asort( $pro_conditions );

		// the action to call using AJAX.
		$action            = 'load_display_conditions_metabox';
		$connector_default = 'or';

		$empty_options = ! is_array( $set_conditions ) || ! count( $set_conditions );

		include ADVADS_ABSPATH . 'admin/views/conditions/conditions-form.php';
	}

	/**
	 * Render connector option
	 *
	 * @param int    $index incremental index of the options.
	 * @param string $value connector value.
	 * @param string $form_name name of the form, falls back to class constant.
	 *
	 * @return string HTML for connector option.
	 * @since 1.7.0.4
	 */
	public static function render_connector_option( $index, $value, $form_name ) {
		$label = ( 'or' === $value ) ? __( 'or', 'advanced-ads' ) : __( 'and', 'advanced-ads' );

		$name = self::get_form_name_with_index( $form_name, $index );

		// create random value to identify the form field.
		$rand = md5( $form_name );

		return sprintf(
			"<input style='display:none' type='checkbox' name='%s[connector]' value='or' id='%s' %s><label for='%s'>%s</label>",
			esc_attr( $name ),
			esc_attr( "advads-conditions-$index-connector-$rand" ),
			checked( 'or', $value, false ),
			esc_attr( "advads-conditions-$index-connector-$rand" ),
			esc_html( $label )
		);
	}

	/**
	 * Render type field
	 *
	 * @param string $type type of the current condition.
	 * @param string $name name of the form, falls back to class constant.
	 */
	public static function render_type_field( $type, $name ) {
		?>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>[type]" value="<?php echo esc_attr( $type ); ?>"/>
		<?php
	}

	/**
	 * Helper function to the name of a form field.
	 * falls back to default
	 *
	 * @param string $form_name form name if submitted.
	 * @param int    $index index of the condition.
	 *
	 * @return string
	 */
	public static function get_form_name_with_index( $form_name = '', $index = 0 ) {
		return ! empty( $form_name ) ? $form_name . '[' . $index . ']' : self::FORM_NAME . '[' . $index . ']';
	}

	/**
	 * Callback to display the metabox for the post type condition
	 *
	 * @param array  $options options of the condition.
	 * @param int    $index index of the condition.
	 * @param string $form_name name of the form, falls back to class constant.
	 */
	public static function metabox_post_type( $options, $index = 0, $form_name = '' ) {
		if ( ! isset( $options['type'] ) || '' === $options['type'] ) {
			return;
		}

		$type_options = self::get_instance()->conditions;

		if ( ! isset( $type_options[ $options['type'] ] ) ) {
			return;
		}

		// get values and select operator based on previous settings.
		$operator = ( isset( $options['operator'] ) && 'is_not' === $options['operator'] ) ? 'is_not' : 'is';
		$values   = ( isset( $options['value'] ) && is_array( $options['value'] ) ) ? $options['value'] : [];

		// form name basis.
		$name = self::get_form_name_with_index( $form_name, $index );
		$rand = md5( $name );

		self::render_type_field( $options['type'], $name );

		// load operator template.
		include ADVADS_ABSPATH . 'admin/views/conditions/condition-operator.php';

		// set defaults.
		$post_types = get_post_types(
			[
				'public'             => true,
				'publicly_queryable' => true,
			],
			'object',
			'or'
		);
		?>
		<div class="advads-conditions-single advads-buttonset">
		<?php
		$type_label_counts = array_count_values( wp_list_pluck( $post_types, 'label' ) );

		foreach ( $post_types as $_type_id => $_type ) {
			if ( in_array( $_type_id, $values, true ) ) {
				$_val = 1;
			} else {
				$_val = 0;
			}

			if ( $type_label_counts[ $_type->label ] < 2 ) {
				$_label = $_type->label;
			} else {
				$_label = sprintf( '%s (%s)', $_type->label, $_type_id );
			}
			$field_id = "advads-conditions-$_type_id-$rand";
			printf(
				"<label class='button' for='%s'>%s</label><input type='checkbox' id='%s' name='%s' %s value='%s'>",
				esc_attr( $field_id ),
				esc_html( $_label ),
				esc_attr( $field_id ),
				esc_attr( "{$name}[value][]" ),
				checked( $_val, 1, false ),
				esc_attr( $_type_id )
			);
		}
		include ADVADS_ABSPATH . 'admin/views/conditions/not-selected.php';
		?>
		</div>
		<?php
	}

	/**
	 * Callback to display the metabox for the author condition
	 *
	 * @param array  $options options of the condition.
	 * @param int    $index index of the condition.
	 * @param string $form_name name of the form, falls back to class constant.
	 */
	public static function metabox_author( $options, $index = 0, $form_name = '' ) {

		if ( ! isset( $options['type'] ) || '' === $options['type'] ) {
			return;
		}

		$type_options = self::get_instance()->conditions;

		if ( ! isset( $type_options[ $options['type'] ] ) ) {
			return;
		}

		// get values and select operator based on previous settings.
		$operator = ( isset( $options['operator'] ) && 'is_not' === $options['operator'] ) ? 'is_not' : 'is';
		$values   = ( isset( $options['value'] ) && is_array( $options['value'] ) ) ? $options['value'] : [];

		// form name basis.
		$name = self::get_form_name_with_index( $form_name, $index );
		$rand = md5( $name );

		self::render_type_field( $options['type'], $name );

		// load operator template.
		include ADVADS_ABSPATH . 'admin/views/conditions/condition-operator.php';

		// set defaults.
		$max_authors = absint( apply_filters( 'advanced-ads-admin-max-terms', 50 ) );
		$args        = [
			'orderby' => 'nicename',
		];
		if ( version_compare( get_bloginfo( 'version' ), '5.9' ) > -1 ) {
			$args['capability'] = [ 'edit_posts' ];
		} else {
			$args['who'] = 'authors';
		}
		$authors = get_users( $args );

		include ADVADS_ABSPATH . 'admin/views/conditions/condition-author.php';
	}

	/**
	 * Callback to display the metabox for the taxonomy archive pages
	 *
	 * @param array  $options options of the condition.
	 * @param int    $index index of the condition.
	 * @param string $form_name name of the form, falls back to class constant.
	 */
	public static function metabox_taxonomy_terms( $options, $index = 0, $form_name = '' ) {

		if ( ! isset( $options['type'] ) || '' === $options['type'] ) {
			return;
		}

		$type_options = self::get_instance()->conditions;

		// don’t use if this is not a taxonomy.
		if ( ! isset( $type_options[ $options['type'] ] ) || ! isset( $type_options[ $options['type'] ]['taxonomy'] ) ) {
			return;
		}

		$taxonomy = get_taxonomy( $type_options[ $options['type'] ]['taxonomy'] );
		if ( false === $taxonomy ) {
			return;
		}

		// get values and select operator based on previous settings.
		$operator = ( isset( $options['operator'] ) && 'is_not' === $options['operator'] ) ? 'is_not' : 'is';
		$values   = ( isset( $options['value'] ) && is_array( $options['value'] ) ) ? $options['value'] : [];

		// limit the number of terms so many terms don’t break the admin page.
		$max_terms = absint( apply_filters( 'advanced-ads-admin-max-terms', 50 ) );

		// form name basis.
		$name = self::get_form_name_with_index( $form_name, $index );

		self::render_type_field( $options['type'], $name );

		// load operator template.
		include ADVADS_ABSPATH . 'admin/views/conditions/condition-operator.php';

		?>
		<div class="advads-conditions-single advads-buttonset">
			<?php
			$terms = self::display_term_list( $taxonomy, $values, $name . '[value][]', $max_terms, $index );
			if ( empty( $terms ) ) {
				include ADVADS_ABSPATH . 'admin/views/conditions/no-option.php';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Callback to display the metabox for the taxonomies
	 *
	 * @param array  $options options of the condition.
	 * @param int    $index index of the condition.
	 * @param string $form_name name of the form, falls back to class constant.
	 */
	public static function metabox_taxonomies( $options, $index = 0, $form_name = '' ) {

		if ( ! isset( $options['type'] ) || '' === $options['type'] ) {
			return;
		}

		$taxonomies = get_taxonomies( [ 'public' => 1 ], 'objects' );

		$name = self::get_form_name_with_index( $form_name, $index );
		$rand = md5( $name );

		// get values and select operator based on previous settings.
		$operator = ( isset( $options['operator'] ) && 'is_not' === $options['operator'] ) ? 'is_not' : 'is';
		$values   = ( isset( $options['value'] ) && is_array( $options['value'] ) ) ? $options['value'] : [];

		self::render_type_field( $options['type'], $name );

		?>
		<div class="advads-conditions-single advads-buttonset">
			<?php
			$tax_label_counts = array_count_values( wp_list_pluck( $taxonomies, 'label' ) );

			foreach ( $taxonomies as $_taxonomy_id => $_taxonomy ) :

				if ( in_array( $_taxonomy_id, $values ) ) {
					$_val = 1;
				} else {
					$_val = 0;
				}

				if ( $tax_label_counts[ $_taxonomy->label ] < 2 ) {
					$_label = $_taxonomy->label;
				} else {
					$_label = sprintf( '%s (%s)', $_taxonomy->label, $_taxonomy_id );
				}

				$field_id = "advads-conditions-$_taxonomy_id-$rand";
				printf(
					"<label class='button' for='%s'>%s</label><input type ='checkbox' id='%s' name='%s' %s value='%s'>",
					esc_attr( $field_id ),
					esc_html( $_label ),
					esc_attr( $field_id ),
					esc_attr( "{$name}[value][]" ),
					checked( $_val, 1, false ),
					esc_attr( $_taxonomy_id )
				);
			endforeach;
			include ADVADS_ABSPATH . 'admin/views/conditions/not-selected.php';
			?>
		</div>
		<?php
	}

	/**
	 * Display terms of a taxonomy for choice
	 *
	 * @param object $taxonomy taxonomy object.
	 * @param array  $checked ids of checked terms.
	 * @param string $inputname name of the input field.
	 * @param int    $max_terms maximum number of terms to show.
 	 * @param int    $index index of the conditions group.
	 *
	 * @return array|int|WP_Error
	 */
	public static function display_term_list( $taxonomy, $checked = [], $inputname = '', $max_terms = 50, $index = 0 ) {
		$terms = get_terms(
			$taxonomy->name,
			[
				'hide_empty' => false,
				'number'     => $max_terms,
			]
		);
		$rand  = md5( $inputname );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :
			// display search field if the term limit is reached.
			if ( count( $terms ) == $max_terms ) :

				// query active terms.
				if ( is_array( $checked ) && count( $checked ) ) {
					$args            = [ 'hide_empty' => false ];
					$args['include'] = $checked;
					$checked_terms   = get_terms( $taxonomy->name, $args );
					// phpcs:disable Squiz.PHP.EmbeddedPhp.ContentBeforeOpen, Squiz.PHP.EmbeddedPhp.ContentAfterOpen, Squiz.PHP.EmbeddedPhp.ContentBeforeEnd, Squiz.PHP.EmbeddedPhp.ContentAfterEnd -- Prevent whitespaces between labels
					?>
					<div class="advads-conditions-terms-buttons dynamic-search"><?php
					foreach ( $checked_terms as $_checked_term ) :
						?><label class="button advads-button advads-ui-state-active"><span class="advads-button-text"><?php
							echo esc_html( $_checked_term->name ); ?><input type="hidden" name="<?php echo esc_attr( $inputname ); ?>"
					value="<?php echo esc_attr( $_checked_term->term_id ); ?>"></span></label><?php
					endforeach;
					?></div><?php
					// phpcs:enable
				} else {
					?>
					<div class="advads-conditions-terms-buttons dynamic-search"></div>
					<?php
				}

				printf(
					"<span class='advads-conditions-terms-show-search button' title='%s'>+</span><br>",
					esc_attr_x( 'add more terms', 'display the terms search field on ad edit page', 'advanced-ads' )
				);
				printf(
					"<input type='text' class='advads-conditions-terms-search' data-tag-name='%s' data-input-name='%s' placeholder='%s'>",
					esc_attr( $taxonomy->name ),
					esc_attr( $inputname ),
					esc_attr__( 'term name or id', 'advanced-ads' )
				);
			else :
				foreach ( $terms as $_term ) {
					$field_id = "advads-conditions-$_term->term_id-$rand";
					printf(
						"<input type='checkbox' id='%s' name='%s' value='%s' %s><label for='%s'>%s</label>",
						esc_attr( $field_id ),
						esc_attr( $inputname ),
						esc_attr( $_term->term_id ),
						checked( in_array( $_term->term_id, $checked ), true, false ),
						esc_attr( $field_id ),
						esc_html( $_term->name )
					);
				}
				include ADVADS_ABSPATH . 'admin/views/conditions/not-selected.php';
			endif;
		endif;

		return $terms;
	}

	/**
	 * Callback to display the metabox for the taxonomy archive pages
	 *
	 * @param array  $options options of the condition.
	 * @param int    $index index of the condition.
	 * @param string $form_name name of the form, falls back to class constant.
	 */
	public static function metabox_post_ids( $options, $index = 0, $form_name = '' ) {

		if ( ! isset( $options['type'] ) || '' === $options['type'] ) {
			return;
		}

		// get values and select operator based on previous settings.
		$operator = ( isset( $options['operator'] ) && 'is_not' === $options['operator'] ) ? 'is_not' : 'is';
		$values   = ( isset( $options['value'] ) && is_array( $options['value'] ) ) ? $options['value'] : [];

		// form name basis.
		$name = self::get_form_name_with_index( $form_name, $index );

		self::render_type_field( $options['type'], $name );

		// load operator template.
		include ADVADS_ABSPATH . 'admin/views/conditions/condition-operator.php';

		?>
		<div class="advads-conditions-single advads-buttonset advads-conditions-postid-buttons">
		<?php
		// Query active post ids.
		// phpcs:disable Squiz.PHP.EmbeddedPhp.ContentBeforeOpen, Squiz.PHP.EmbeddedPhp.ContentAfterOpen, Squiz.PHP.EmbeddedPhp.ContentBeforeEnd, Squiz.PHP.EmbeddedPhp.ContentAfterEnd -- Prevent whitespaces between labels
		if ( [] !== $values ) {
			$args = [
				'post_type'      => 'any',
				'post__in'       => $values,
				'posts_per_page' => - 1,
				'order'          => 'ASC',
				'order_by'       => 'title',

			];
			$the_query = new WP_Query( $args );
			while ( $the_query->have_posts() ) {
				$the_query->next_post();
				?><label class="button advads-button advads-ui-state-active"><span class="advads-button-text"><?php
				echo esc_html(
					sprintf( '%s (%s)', get_the_title( $the_query->post->ID ), $the_query->post->post_type )
				); ?></span><input type="hidden" name="<?php echo esc_attr( $name ); ?>[value][]"
				value="<?php echo esc_attr( $the_query->post->ID ); ?>"></label><?php
			}
		}
		printf(
			"<span class='advads-conditions-postids-show-search button' style='%s'>+</span>",
			empty( $values ) ? 'display:none;' : ''
		);

		printf(
			"<p class='advads-conditions-postids-search-line'><input type='text' class='advads-display-conditions-individual-post' style='%s' placeholder='%s' data-field-name='%s' />%s</p></div>",
			! empty( $values ) ? 'display:none;' : '',
			esc_attr__( 'title or id', 'advanced-ads' ),
			esc_attr( $name ),
			wp_kses(
				wp_nonce_field( 'internal-linking', '_ajax_linking_nonce', false, false ),
				[
					'input' => [
						'type'  => true,
						'id'    => true,
						'name'  => true,
						'class' => true,
						'value' => true,
					],
				]
			)
		);
		// phpcs:enable
	}

	/**
	 * Callback to display the metabox for the general display conditions
	 *
	 * @param array  $options options of the condition.
	 * @param int    $index index of the condition.
	 * @param string $form_name name of the form, falls back to class constant.
	 */
	public static function metabox_general( $options, $index = 0, $form_name = '' ) {

		// general conditions array.
		$conditions = self::get_instance()->general_conditions();
		if ( ! isset( $options['type'] ) || '' === $options['type'] ) {
			return;
		}

		$name   = self::get_form_name_with_index( $form_name, $index );
		$values = isset( $options['value'] ) ? $options['value'] : [];
		?>
		<div class="advads-conditions-single advads-buttonset">
		<?php

		self::render_type_field( $options['type'], $name );

		$rand = md5( $form_name );
		foreach ( $conditions as $_key => $_condition ) {
			// activate by default.
			$value    = ( [] === $values || in_array( $_key, $values, true ) ) ? 1 : 0;
			$field_id = "advads-conditions-$_key-$rand";
			printf(
				"<input type='checkbox' id='%s' name='%s' %s value='%s'>",
				esc_attr( $field_id ),
				esc_attr( "{$name}[value][]" ),
				checked( 1, $value, false ),
				esc_attr( $_key )
			);
			printf(
				"<label for='%s'>%s</label>",
				esc_attr( $field_id ),
				esc_html( $_condition['label'] )
			);

		}
		include ADVADS_ABSPATH . 'admin/views/conditions/not-selected.php';
		echo '</div>';
	}

	/**
	 * Retrieve the array with general conditions
	 *
	 * @return array $conditions
	 */
	public static function general_conditions() {
		return apply_filters(
			'advanced-ads-display-conditions-general',
			[
				'is_front_page' => [
					'label'       => __( 'Home Page', 'advanced-ads' ),
					'description' => __( 'show on Home page', 'advanced-ads' ),
					'type'        => 'radio',
				],
				'is_singular'   => [
					'label'       => __( 'Singular Pages', 'advanced-ads' ),
					'description' => __( 'show on singular pages/posts', 'advanced-ads' ),
					'type'        => 'radio',
				],
				'is_archive'    => [
					'label'       => __( 'Archive Pages', 'advanced-ads' ),
					'description' => __( 'show on any type of archive page (category, tag, author and date)', 'advanced-ads' ),
					'type'        => 'radio',
				],
				'is_search'     => [
					'label'       => __( 'Search Results', 'advanced-ads' ),
					'description' => __( 'show on search result pages', 'advanced-ads' ),
					'type'        => 'radio',
				],
				'is_404'        => [
					'label'       => __( '404 Page', 'advanced-ads' ),
					'description' => __( 'show on 404 error page', 'advanced-ads' ),
					'type'        => 'radio',
				],
				'is_attachment' => [
					'label'       => __( 'Attachment Pages', 'advanced-ads' ),
					'description' => __( 'show on attachment pages', 'advanced-ads' ),
					'type'        => 'radio',
				],
				'is_main_query' => [
					'label'       => __( 'Secondary Queries', 'advanced-ads' ),
					'description' => __( 'allow ads in secondary queries', 'advanced-ads' ),
					'type'        => 'radio',
				],
				'is_feed'       => [
					'label'       => __( 'RSS Feed', 'advanced-ads' ),
					'description' => __( 'allow ads in RSS Feed', 'advanced-ads' ),
					'type'        => 'radio',
				],
				'is_rest_api'   => [
					'label'       => __( 'REST API', 'advanced-ads' ),
					'description' => __( 'allow ads in REST API', 'advanced-ads' ),
					'type'        => 'radio',
				],
			]
		);
	}

	/**
	 * Callback to display the 'content age' condition
	 *
	 * @param array  $options options of the condition.
	 * @param int    $index index of the condition.
	 * @param string $form_name name of the form, falls back to class constant.
	 */
	public static function metabox_content_age( $options, $index = 0, $form_name = '' ) {
		if ( ! isset( $options['type'] ) || '' === $options['type'] ) {
			return;
		}

		$type_options = self::get_instance()->conditions;

		if ( ! isset( $type_options[ $options['type'] ] ) ) {
			return;
		}

		// form name basis.
		$name = self::get_form_name_with_index( $form_name, $index );

		$operator = isset( $options['operator'] ) ? $options['operator'] : 'older_than';
		$value    = ( isset( $options['value'] ) && is_numeric( $options['value'] ) ) ? (float) $options['value'] : 0;

		self::render_type_field( $options['type'], $name );

		?>
		<select name="<?php echo esc_attr( $name ); ?>[operator]">
			<option value="older_than" <?php selected( 'older_than', $operator ); ?>><?php esc_html_e( 'older than', 'advanced-ads' ); ?></option>
			<option value="younger_than" <?php selected( 'younger_than', $operator ); ?>><?php esc_html_e( 'younger than', 'advanced-ads' ); ?></option>
		</select>

		<input type="text" name="<?php echo esc_attr( $name ); ?>[value]" value="<?php echo esc_attr( $value ); ?>"/>

		<?php esc_html_e( 'days', 'advanced-ads' ); ?>

		<p class="description">
			<?php esc_html_e( 'Display ads based on the age of a page or post.', 'advanced-ads' ); ?>
			<a href="https://wpadvancedads.com/manual/display-ads-by-content-age/?utm_source=advanced-ads&utm_medium=link&utm_campaign=condition-content-age" class="advads-manual-link" target="_blank">
				<?php esc_html_e( 'Manual', 'advanced-ads' ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * Check post type display condition in frontend
	 *
	 * @param array           $options options of the condition.
	 * @param Advanced_Ads_Ad $ad Advanced_Ads_Ad.
	 *
	 * @return bool true if can be displayed
	 */
	public static function check_post_type( $options, Advanced_Ads_Ad $ad ) {
		if ( ! isset( $options['value'] ) || ! is_array( $options['value'] ) ) {
			return false;
		}

		if ( isset( $options['operator'] ) && 'is_not' === $options['operator'] ) {
			$operator = 'is_not';
		} else {
			$operator = 'is';
		}

		$ad_options = $ad->options();
		$query      = $ad_options['wp_the_query'];
		$post       = isset( $ad_options['post'] ) ? $ad_options['post'] : null;
		$post_type  = isset( $post['post_type'] ) ? $post['post_type'] : false;

		if ( ! self::can_display_ids( $post_type, $options['value'], $operator ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check author display condition in frontend
	 *
	 * @param array           $options options of the condition.
	 * @param Advanced_Ads_Ad $ad Advanced_Ads_Ad.
	 *
	 * @return bool true if can be displayed
	 */
	public static function check_author( $options, Advanced_Ads_Ad $ad ) {

		if ( ! isset( $options['value'] ) || ! is_array( $options['value'] ) ) {
			return false;
		}

		if ( isset( $options['operator'] ) && 'is_not' === $options['operator'] ) {
			$operator = 'is_not';
		} else {
			$operator = 'is';
		}

		$ad_options  = $ad->options();
		$post        = isset( $ad_options['post'] ) ? $ad_options['post'] : null;
		$post_author = isset( $post['author'] ) ? $post['author'] : false;

		if ( ! self::can_display_ids( $post_author, $options['value'], $operator ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check taxonomies display condition in frontend
	 *
	 * @param array           $options options of the condition.
	 * @param Advanced_Ads_Ad $ad ad.
	 *
	 * @return bool true if can be displayed
	 */
	public static function check_taxonomies( $options, Advanced_Ads_Ad $ad ) {

		if ( ! isset( $options['value'] ) ) {
			return false;
		}

		if ( isset( $options['operator'] ) && 'is_not' === $options['operator'] ) {
			$operator = 'is_not';
		} else {
			$operator = 'is';
		}

		$ad_options = $ad->options();
		$query      = $ad_options['wp_the_query'];
		$post_id    = isset( $ad_options['post']['id'] ) ? $ad_options['post']['id'] : null;

		// get terms of the current taxonomy.
		$type_options = self::get_instance()->conditions;
		if ( ! isset( $options['type'] ) || ! isset( $type_options[ $options['type'] ]['taxonomy'] ) ) {
			return true;
		}
		$taxonomy = $type_options[ $options['type'] ]['taxonomy'];

		$terms = get_the_terms( $post_id, $taxonomy );

		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$term_ids[] = $term->term_id;
			}
		} elseif ( false === $terms && 'is' === $operator ) {
			// don’t show if should show only for a specific tag.
			return false;
		} else {
			return true;
		}

		if ( 'is' === $operator && ( ! isset( $query['is_singular'] ) || ! $query['is_singular'] ) ) {
			return false;
		} elseif ( isset( $query['is_singular'] ) && $query['is_singular'] && ! self::can_display_ids( $options['value'], $term_ids, $operator )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Check taxonomy archive display condition in frontend
	 *
	 * @param array           $options options of the condition.
	 * @param Advanced_Ads_Ad $ad ad.
	 *
	 * @return bool true if can be displayed
	 */
	public static function check_taxonomy_archive( $options, Advanced_Ads_Ad $ad ) {

		if ( ! isset( $options['value'] ) ) {
			return false;
		}

		if ( isset( $options['operator'] ) && 'is_not' === $options['operator'] ) {
			$operator = 'is_not';
		} else {
			$operator = 'is';
		}

		$ad_options = $ad->options();
		$query      = $ad_options['wp_the_query'];

		// return false if operator is "is", but important query vars are not given.
		if ( 'is' === $operator && ( empty( $query['term_id'] ) || empty( $query['is_archive'] ) ) ) {
			return false;
		} elseif ( isset( $query['term_id'] ) && isset( $query['is_archive'] ) && $query['is_archive'] && ! self::can_display_ids( $query['term_id'], $options['value'], $operator )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Check if a specific archive belongs to a taxonomy in general (not a specific term)
	 *
	 * @param array           $options options of the condition.
	 * @param Advanced_Ads_Ad $ad ad.
	 *
	 * @return bool true if can be displayed
	 */
	public static function check_taxonomy( $options, Advanced_Ads_Ad $ad ) {

		if ( ! isset( $options['value'] ) ) {
			return false;
		}

		if ( isset( $options['operator'] ) && 'is_not' === $options['operator'] ) {
			$operator = 'is_not';
		} else {
			$operator = 'is';
		}

		$ad_options = $ad->options();
		$query      = $ad_options['wp_the_query'];

		// return false if operator is "is", but important query vars are not given.
		if ( 'is' === $operator && ( empty( $query['taxonomy'] ) || empty( $query['is_archive'] ) ) ) {
			return false;
		} elseif ( isset( $query['taxonomy'] ) && isset( $query['is_archive'] ) && $query['is_archive'] && ! self::can_display_ids( $query['taxonomy'], $options['value'], $operator )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Check post ids display condition in frontend
	 *
	 * @param array           $options options of the condition.
	 * @param Advanced_Ads_Ad $ad ad.
	 *
	 * @return bool true if can be displayed
	 */
	public static function check_post_ids( $options, Advanced_Ads_Ad $ad ) {

		if ( isset( $options['operator'] ) && 'is_not' === $options['operator'] ) {
			$operator = 'is_not';
		} else {
			$operator = 'is';
		}

		$ad_options = $ad->options();
		$query      = $ad_options['wp_the_query'];
		$post_id    = isset( $ad_options['post']['id'] ) ? $ad_options['post']['id'] : null;

		// fixes page id on BuddyPress pages.
		if ( 0 === $post_id && class_exists( 'BuddyPress' ) && function_exists( 'bp_current_component' ) ) {
			$component = bp_current_component();
			$bp_pages  = get_option( 'bp-pages' );
			if ( isset( $bp_pages[ $component ] ) ) {
				$post_id = $bp_pages[ $component ];
			}
		}

		/**
		 * WooCommerce Store page fix
		 * since WooCommerce changes the post ID of the static page selected to be the product overview page, we need to get the original page id from the WC options
		 */
		if ( function_exists( 'is_shop' ) && is_shop() && isset( $options['value'] ) && is_array( $options['value'] ) ) {
			$post_id = get_option( 'woocommerce_shop_page_id' );

			return self::can_display_ids( $post_id, $options['value'], $operator );
		}

		if ( empty( $ad_options['wp_the_query']['is_singular'] ) ) {
			if ( 'is_not' === $operator ) {
				return true;
			} else {
				return false;
			}
		}

		if ( ! isset( $options['value'] ) || ! is_array( $options['value'] ) || ! $post_id ) {
			return true;
		}

		return self::can_display_ids( $post_id, $options['value'], $operator );
	}

	/**
	 * Check general display conditions in frontend
	 *
	 * @param array           $options options of the condition.
	 * @param Advanced_Ads_Ad $ad ad.
	 *
	 * @return bool true if can be displayed
	 */
	public static function check_general( $options, Advanced_Ads_Ad $ad ) {

		// display by default.
		if ( ! isset( $options['value'] ) || ! is_array( $options['value'] ) || ! count( $options['value'] ) ) {
			return true;
		}

		// check general conditions added by other add-ons.
		$result = apply_filters( 'advanced-ads-display-conditions-check-general', null, $options['value'] );
		if ( null !== ( $result ) ) {
			return $result;
		}

		// skip checks, if general conditions are unchanged.
		if ( self::$default_general_keys === $options['value'] ) {
			return true;
		}

		// get plugin options.
		$ad_options = $ad->options();
		$query      = $ad_options['wp_the_query'];

		// check main query.
		if ( isset( $query['is_main_query'] ) && ! $query['is_main_query'] && ! in_array( 'is_main_query', $options['value'], true ) ) {
			return false;
		}

		if ( isset( $query['is_rest_api'] ) && $query['is_rest_api'] && ! in_array( 'is_rest_api', $options['value'], true ) ) {
			return false;
		}

		// check home page.
		if (
			( ( isset( $query['is_front_page'] ) && $query['is_front_page'] ) || ( isset( $query['is_home'] ) && $query['is_home'] ) )
			&& in_array( 'is_front_page', $options['value'], true )
		) {
			return true;
		} elseif ( isset( $query['is_front_page'] ) && $query['is_front_page'] && ( ! in_array( 'is_front_page', $options['value'], true ) ) ) {
			return false;
		}

		// check common tests.
		foreach ( self::$query_var_keys as $_type ) {
			if ( 'is_main_query' !== $_type && isset( $query[ $_type ] ) && $query[ $_type ] &&
				in_array( $_type, $options['value'], true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check 'content age' condition in frontend.
	 *
	 * @param array           $options options of the condition.
	 * @param Advanced_Ads_Ad $ad ad.
	 *
	 * @return bool true if can be displayed
	 */
	public static function check_content_age( $options, Advanced_Ads_Ad $ad ) {
		$post = get_post();

		if ( ! $post
			&& wp_doing_ajax()
			&& isset( $_REQUEST['action'], $_REQUEST['theId'], $_REQUEST['isSingular'] )
			&& sanitize_key( $_REQUEST['action'] ) === 'advads_ad_select'
			&& ( $_REQUEST['isSingular'] )
		) {
			$post = get_post( (int) $_REQUEST['theId'] );
		}

		$operator = ( isset( $options['operator'] ) && 'younger_than' === $options['operator'] ) ? 'younger_than' : 'older_than';
		$value    = isset( $options['value'] ) ? $options['value'] : '';

		if ( empty( $post->ID ) || empty( $value ) ) {
			return true;
		}

		// get post publish date in unix timestamp.
		$publish_time = get_the_time( 'U', $post->ID );

		// get difference from now.
		$diff_from_now = time() - $publish_time;

		// check against entered age.
		$value_in_seconds = DAY_IN_SECONDS * $value;

		if ( 'younger_than' === $operator ) {
			return $diff_from_now < $value_in_seconds;
		} else {
			return $diff_from_now > $value_in_seconds;
		}
	}

	/**
	 * Helper function to check for in array values
	 *
	 * @param mixed $id scalar (key) or array of keys as needle.
	 * @param array $ids haystack.
	 *
	 * @return boolean void if either argument is empty
	 */
	public static function in_array( $id, $ids ) {
		// empty?
		if ( ! isset( $id ) || [] === $id ) {
			return;
		}

		// invalid?
		if ( ! is_array( $ids ) ) {
			return;
		}

		return is_array( $id ) ? [] !== array_intersect( $id, $ids ) : in_array( $id, $ids );
	}

	/**
	 * Helper to compare ids.
	 *
	 * @param array  $needle ids that should be searched for in haystack.
	 * @param array  $haystack reference ids.
	 * @param string $operator whether it should be included or not.
	 *
	 * @return boolean
	 */
	public static function can_display_ids( $needle, $haystack, $operator = 'is' ) {

		if ( 'is' === $operator && self::in_array( $needle, $haystack ) === false ) {
			return false;
		}

		if ( 'is_not' === $operator && self::in_array( $needle, $haystack ) === true ) {
			return false;
		}

		return true;
	}

	/**
	 * Check display conditions
	 *
	 * @param bool            $can_display whether the current ad can be displayed based on the checks that ran by now.
	 * @param Advanced_Ads_Ad $ad ad object.
	 *
	 * @return bool $can_display true if can be displayed in frontend
	 * @since 1.1.0 moved here from can_display()
	 *
	 * @since 1.7.0 moved here from display-by-query module
	 */
	public function can_display( $can_display, $ad ) {
		if ( ! $can_display ) {
			return false;
		}

		$options = $ad->options();
		if (
			// test if anything is to be limited at all.
			! isset( $options['conditions'] ) || ! is_array( $options['conditions'] )
			// query arguments required.
			|| ! isset( $options['wp_the_query'] )
		) {
			return true;
		}
		// get conditions with rebased index keys.
		$conditions  = array_values( $options['conditions'] );
		$query       = $options['wp_the_query'];
		$post        = isset( $options['post'] ) ? $options['post'] : null;
		$last_result = false;
		$length      = count( $conditions );

		for ( $i = 0; $i < $length; ++$i ) {
			$_condition = current( $conditions );
			$next       = next( $conditions );
			$next_key   = key( $conditions );

			/**
			 * Force next condition’s connector to OR if
			 *  not set to OR already
			 *  this condition and the next are from the same taxonomy
			 *  the conditions don’t have the same condition type
			 *  they are both set to SHOW
			 */
			$tax      = ( isset( $_condition['type'] ) && isset( $this->conditions[ $_condition['type'] ]['taxonomy'] ) ) ? $this->conditions[ $_condition['type'] ]['taxonomy'] : false;
			$next_tax = ( isset( $next['type'] ) && isset( $this->conditions[ $next['type'] ]['taxonomy'] ) ) ? $this->conditions[ $next['type'] ]['taxonomy'] : false;
			if ( $tax && $next_tax && $next_key
				&& $next_tax === $tax
				&& ( ! isset( $next['connector'] ) || 'or' !== $next['connector'] )
				&& 'is' === $_condition['operator'] && 'is' === $next['operator']
				&& $_condition['type'] !== $next['type']
			) {
					$next['connector']                    = 'or';
					$conditions[ $next_key ]['connector'] = 'or';
			}

			// ignore OR if last result was true.
			if ( $last_result && isset( $_condition['connector'] ) && 'or' === $_condition['connector'] ) {
				continue;
			}

			$result      = self::frontend_check( $_condition, $ad );
			$last_result = $result;
			if ( ! $result ) {
				// return false only, if the next condition doesn’t have an OR operator.
				if ( ! isset( $next['connector'] ) || 'or' !== $next['connector'] ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * On demand provide current query arguments to ads.
	 *
	 * Existing arguments must not be overridden.
	 * Some arguments might be cachable.
	 *
	 * @param array $args arguments.
	 *
	 * @return array
	 */
	public function ad_select_args_callback( $args ) {
		global $post, $wp_the_query, $wp_query, $numpages;

		if ( $post instanceof WP_Post ) {
			if ( ! isset( $args['post'] ) ) {
				$args['post'] = [];
			}
			if ( ! isset( $args['post']['id'] ) ) {

				// if currently on a single site, use the main query information just in case a custom query is broken.
				if ( isset( $wp_the_query->post->ID ) && $wp_the_query->is_single() ) {
					$args['post']['id'] = $wp_the_query->post->ID;
				} else {
					$args['post']['id'] = $post->ID;
				}
			}
			if ( ! isset( $args['post']['author'] ) ) {
				// if currently on a single site, use the main query information just in case a custom query is broken.
				if ( isset( $wp_the_query->post->post_author ) && $wp_the_query->is_single() ) {
					$args['post']['author'] = $wp_the_query->post->post_author;
				} else {
					// a user reported that the missing $post_author property issue appeared so let’s check if it exists.
					$args['post']['author'] = isset( $post->post_author ) ? $post->post_author : '';
				}
			}
			if ( ! isset( $args['post']['post_type'] ) ) {
				// if currently on a single site, use the main query information just in case a custom query is broken.
				if ( isset( $wp_the_query->post->post_type ) && $wp_the_query->is_single() ) {
					$args['post']['post_type'] = $wp_the_query->post->post_type;
				} else {
					// a user reported that the missing $post_type property issue appeared so let’s check if it exists.
					$args['post']['post_type'] = isset( $post->post_type ) ? $post->post_type : '';
				}
			}
		}

		// pass query arguments.
		if ( isset( $wp_the_query ) ) {
			if ( ! isset( $args['wp_the_query'] ) ) {
				$args['wp_the_query'] = [];
			}
			$query = $wp_the_query->get_queried_object();
			// term_id exists only for taxonomy archive pages.
			if ( ! isset( $args['wp_the_query']['term_id'] ) && $query ) {
				$args['wp_the_query']['term_id'] = isset( $query->term_id ) ? $query->term_id : '';
			}
			// taxonomy.
			if ( ! isset( $args['wp_the_query']['taxonomy'] ) && $query ) {
				$args['wp_the_query']['taxonomy'] = isset( $query->taxonomy ) ? $query->taxonomy : '';
			}

			// query type/ context.
			if ( ! isset( $args['wp_the_query']['is_main_query'] ) ) {
				$args['wp_the_query']['is_main_query'] = Advanced_Ads::get_instance()->is_main_query();
			}

			// REST API.
			if ( ! isset( $args['wp_the_query']['is_rest_api'] ) ) {
				$args['wp_the_query']['is_rest_api'] = defined( 'REST_REQUEST' ) && REST_REQUEST;
			}

			// `<!-- nextpage -->` tags.
			if ( ! isset( $args['wp_the_query']['page'] ) ) {
				$args['wp_the_query']['page']     = isset( $wp_the_query->query_vars['page'] ) && $wp_the_query->query_vars['page'] ? $wp_the_query->query_vars['page'] : 1;
				$args['wp_the_query']['numpages'] = isset( $numpages ) ? $numpages : 1;
			}

			// query vars.
			foreach ( self::$query_var_keys as $key ) {
				if ( ! isset( $args['wp_the_query'][ $key ] ) ) {
					$args['wp_the_query'][ $key ] = $wp_the_query->$key();
				}
			}
		}

		return $args;
	}

	/**
	 * ;odify post search query to search by post_title or ID
	 *
	 * @param array $query post search query.
	 *
	 * @return array
	 */
	public static function modify_post_search( $query ) {

		// use ID and not search field if ID given.
		if ( 0 !== absint( $query['s'] ) && strlen( $query['s'] ) === strlen( absint( $query['s'] ) ) ) {
			$query['post__in'] = [ absint( $query['s'] ) ];
			unset( $query['s'] );
		}

		$query['suppress_filters'] = false;
		$query['orderby']          = 'post_title';
		$query['post_status']      = [ 'publish', 'pending', 'draft', 'future' ];

		return $query;
	}

	/**
	 * Modify post search SQL to search only in post title
	 *
	 * @param string $sql SQL statement.
	 *
	 * @return string
	 */
	public static function modify_post_search_sql( $sql ) {
		global $wpdb;
		// removes the search in content and excerpt columns.
		$sql = preg_replace( "/OR \({$wpdb->posts}.post_(content|excerpt)( NOT)? LIKE '(.*?)'\)/", '', $sql );

		return $sql;
	}

	/**
	 * Preg_replace callback used in modify_post_search_sql()
	 *
	 * @param array $matches results for post search.
	 *
	 * @return string
	 * @deprecated since version 1.8.16
	 */
	public static function modify_post_search_sql_callback( $matches ) {
		global $wpdb;
		if ( 'content' === $matches[1] && preg_match( '@^([0-9]+)$@', $matches[3], $matches_id ) ) {
			$equals_op = ' NOT' === $matches[2] ? '!=' : '=';

			return "{$wpdb->posts}.ID$equals_op$matches_id[1]";
		} elseif ( ' NOT' === $matches[2] ) {
			return '1=1';
		} else {
			return '1=0';
		}
	}
}
