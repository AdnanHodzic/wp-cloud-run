<?php
// phpcs:ignoreFile
/**
 * Advanced Ads
 *
 * @package   Advanced_Ads_Placements
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright 2014 Thomas Maier, Advanced Ads GmbH
 */

use AdvancedAds\Entities;
use AdvancedAds\Utilities\WordPress;

/**
 * Grouping placements functions
 *
 * @since 1.1.0
 * @package Advanced_Ads_Placements
 * @author  Thomas Maier <support@wpadvancedads.com>
 */
class Advanced_Ads_Placements {

	/**
	 * Gather placeholders which later are replaced by the ads
	 *
	 * @var array $ads_for_placeholders
	 */
	private static $ads_for_placeholders = [];
	/**
	 * Temporarily change content during processing
	 *
	 * @var array $placements
	 */
	private static $replacements = [
		'gcse:search' => 'gcse__search', // Google custom search namespaced tags.
	];

	/**
	 * Return placement page description
	 *
	 * @deprecated 1.47.0
	 *
	 * @return string
	 */
	public static function get_description() {
		_deprecated_function( __METHOD__, '1.47.0', '\AdvancedAds\Entities::get_placement_description()' );
		return Entities::get_placement_description();
	}

	/**
	 * Get placement types
	 *
	 * @return \Advanced_Ads\Placement_Type[] $types array with placement types
	 * @since 1.2.1
	 */
	public static function get_placement_types() {
		$types = [
			'post_top'       => [
				'title'       => __( 'Before Content', 'advanced-ads' ),
				'description' => __( 'Injected before the post content.', 'advanced-ads' ),
				'image'       => ADVADS_BASE_URL . 'admin/assets/img/placements/content-before.png',
				'order'       => 20,
				'options'     => [
					'show_position'    => true,
					'show_lazy_load'   => true,
					'uses_the_content' => true,
					'amp'              => true,
				],
			],
			'post_content'   => [
				'title'       => __( 'Content', 'advanced-ads' ),
				'description' => __( 'Injected into the content. You can choose the paragraph after which the ad content is displayed.', 'advanced-ads' ),
				'image'       => ADVADS_BASE_URL . 'admin/assets/img/placements/content-within.png',
				'order'       => 21,
				'options'     => [
					'show_position'    => true,
					'show_lazy_load'   => true,
					'uses_the_content' => true,
					'amp'              => true,
				],
			],
			'post_bottom'    => [
				'title'       => __( 'After Content', 'advanced-ads' ),
				'description' => __( 'Injected after the post content.', 'advanced-ads' ),
				'image'       => ADVADS_BASE_URL . 'admin/assets/img/placements/content-after.png',
				'order'       => 35,
				'options'     => [
					'show_position'    => true,
					'show_lazy_load'   => true,
					'uses_the_content' => true,
					'amp'              => true,
				],
			],
			'sidebar_widget' => [
				'title'       => __( 'Sidebar Widget', 'advanced-ads' ),
				'description' => __( 'Create a sidebar widget with an ad. Can be placed and used like any other widget.', 'advanced-ads' ),
				'image'       => ADVADS_BASE_URL . 'admin/assets/img/placements/widget.png',
				'order'       => 50,
				'options'     => [
					'show_position'  => true,
					'show_lazy_load' => true,
					'amp'            => true,
				],
			],
			'default'        => [
				'title'       => __( 'Manual Placement', 'advanced-ads' ),
				'description' => __( 'Manual placement to use as function or shortcode.', 'advanced-ads' ),
				'image'       => ADVADS_BASE_URL . 'admin/assets/img/placements/manual.png',
				'order'       => 80,
				'options'     => [
					'show_position'  => true,
					'show_lazy_load' => true,
					'amp'            => true,
				],
			],
			'header'         => [
				'title'       => __( 'Header Code', 'advanced-ads' ),
				'description' => __( 'Injected in Header (before closing &lt;/head&gt; Tag, often not visible).', 'advanced-ads' ),
				'image'       => ADVADS_BASE_URL . 'admin/assets/img/placements/header.png',
				'order'       => 3,
			],
			'footer'         => [
				'title'       => __( 'Footer Code', 'advanced-ads' ),
				'description' => __( 'Injected in Footer (before closing &lt;/body&gt; Tag).', 'advanced-ads' ),
				'image'       => ADVADS_BASE_URL . 'admin/assets/img/placements/footer.png',
				'order'       => 95,
				'options'     => [ 'amp' => true ],
			],
		];

		$types = (array) apply_filters( 'advanced-ads-placement-types', $types );

		foreach ( $types as $type => $definition ) {
			$types[ $type ] = new \Advanced_Ads\Placement_Type( $type, $definition );
		}

		return $types;
	}

	/**
	 * Update placements if sent
	 *
	 * @since 1.5.2
	 */
	public static function update_placements() {

		// check user permissions.
		if ( ! WordPress::user_can( 'advanced_ads_manage_placements' ) ) {
			return;
		}

		$success = null;

		// add hook of last opened placement settings to URL.
		$hook = ! empty( $_POST['advads-last-edited-placement'] ) ? '#single-placement-' . $_POST['advads-last-edited-placement'] : '';

		if ( isset( $_POST['advads']['placement'] ) && check_admin_referer( 'advads-placement', 'advads_placement' ) ) {
			$success = self::save_new_placement( $_POST['advads']['placement'] );
		}
		// save placement data.
		if ( isset( $_POST['advads']['placements'] ) && check_admin_referer( 'advads-placement', 'advads_placement' ) ) {
			$success = self::save_placements( $_POST['advads']['placements'] );
		}

		$success = apply_filters( 'advanced-ads-update-placements', $success );

		if ( isset( $success ) ) {
			$message = $success ? 'updated' : 'error';
			wp_redirect( esc_url_raw( add_query_arg( [ 'message' => $message ] ) ) . $hook );
		}
	}

	/**
	 * Save a new placement
	 *
	 * @param array $new_placement information about the new placement.
	 *
	 * @return mixed slug if saved; false if not
	 * @since 1.1.0
	 */
	public static function save_new_placement( $new_placement ) {
		// load placements // -TODO use model.
		$placements = Advanced_Ads::get_ad_placements_array();

		// create slug.
		$new_placement['slug'] = sanitize_title( $new_placement['name'] );

		if ( isset( $placements[ $new_placement['slug'] ] ) ) {
			$i = 1;
			// try to save placement until we found an empty slug.
			do {
				$i ++;
				if ( 100 === $i ) { // prevent endless loop, just in case.
					Advanced_Ads::log( 'endless loop when injecting placement' );
					break;
				}
			} while ( isset( $placements[ $new_placement['slug'] . '_' . $i ] ) );

			$new_placement['slug'] .= '_' . $i;
			$new_placement['name'] .= ' ' . $i;
		}

		// check if slug already exists or is empty.
		if ( '' === $new_placement['slug'] || isset( $placements[ $new_placement['slug'] ] ) || ! isset( $new_placement['type'] ) ) {
			return false;
		}

		// make sure only allowed types are being saved.
		$placement_types       = self::get_placement_types();
		$new_placement['type'] = ( isset( $placement_types[ $new_placement['type'] ] ) ) ? $new_placement['type'] : 'default';
		// escape name.
		$new_placement['name'] = esc_attr( $new_placement['name'] );

		// add new place to all placements.
		$placements[ $new_placement['slug'] ] = [
			'type' => $new_placement['type'],
			'name' => $new_placement['name'],
			'item' => $new_placement['item'],
		];

		// add index options.
		if ( isset( $new_placement['options'] ) ) {
			$placements[ $new_placement['slug'] ]['options'] = $new_placement['options'];
			if ( isset( $placements[ $new_placement['slug'] ]['options']['index'] ) ) {
				$placements[ $new_placement['slug'] ]['options']['index'] = absint( $placements[ $new_placement['slug'] ]['options']['index'] );
			}
		}

		// save array.
		Advanced_Ads::get_instance()->get_model()->update_ad_placements_array( $placements );

		return $new_placement['slug'];
	}

	/**
	 * Save placements
	 *
	 * @param array $placement_items placements.
	 *
	 * @return mixed true if saved; error message if not
	 * @since 1.1.0
	 */
	public static function save_placements( $placement_items ) {

		// load placements // -TODO use model.
		$placements = Advanced_Ads::get_ad_placements_array();

		foreach ( $placement_items as $_placement_slug => $_placement ) {
			// remove the placement.
			if ( isset( $_placement['delete'] ) ) {
				unset( $placements[ $_placement_slug ] );
				continue;
			}
			// save item.
			if ( isset( $_placement['item'] ) ) {
				$placements[ $_placement_slug ]['item'] = $_placement['item'];
			}
			// save item options.
			if ( isset( $_placement['options'] ) ) {
				$placements[ $_placement_slug ]['options'] = $_placement['options'];
				if ( isset( $placements[ $_placement_slug ]['options']['index'] ) ) {
					$placements[ $_placement_slug ]['options']['index'] = absint( $placements[ $_placement_slug ]['options']['index'] );
				}
			} else {
				$placements[ $_placement_slug ]['options'] = [];
			}
		}

		// save array.
		Advanced_Ads::get_instance()->get_model()->update_ad_placements_array( $placements );

		return true;
	}

	/**
	 * Get items for item select field.
	 * Used for new placement form.
	 *
	 * @return array $select items for select field
	 */
	public static function items_for_select() {
		$select = [];
		$model  = Advanced_Ads::get_instance()->get_model();

		// load all ad groups.
		$groups = $model->get_ad_groups();
		foreach ( $groups as $_group ) {
			$select['groups'][ 'group_' . $_group->term_id ] = $_group->name;
		}

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

		return $select;
	}

	/**
	 * Get html tags for content injection
	 *
	 * @return array $tags array with tags that can be used for content injection
	 * @since 1.3.5
	 */
	public static function tags_for_content_injection() {
		$headline_tags          = apply_filters( 'advanced-ads-headlines-for-ad-injection', [ 'h2', 'h3', 'h4' ] );
		$headline_tags_imploded = '&lt;' . implode( '&gt;, &lt;', $headline_tags ) . '&gt;';

		$tags = apply_filters(
			'advanced-ads-tags-for-injection',
			[
				// translators: %s is an html tag.
				'p'           => sprintf( __( 'paragraph (%s)', 'advanced-ads' ), '&lt;p&gt;' ),
				// translators: %s is an html tag.
				'pwithoutimg' => sprintf( __( 'paragraph without image (%s)', 'advanced-ads' ), '&lt;p&gt;' ),
				// translators: %s is an html tag.
				'h2'          => sprintf( __( 'headline 2 (%s)', 'advanced-ads' ), '&lt;h2&gt;' ),
				// translators: %s is an html tag.
				'h3'          => sprintf( __( 'headline 3 (%s)', 'advanced-ads' ), '&lt;h3&gt;' ),
				// translators: %s is an html tag.
				'h4'          => sprintf( __( 'headline 4 (%s)', 'advanced-ads' ), '&lt;h4&gt;' ),
				// translators: %s is an html tag.
				'headlines'   => sprintf( __( 'any headline (%s)', 'advanced-ads' ), $headline_tags_imploded ),
				// translators: %s is an html tag.
				'img'         => sprintf( __( 'image (%s)', 'advanced-ads' ), '&lt;img&gt;' ),
				// translators: %s is an html tag.
				'table'       => sprintf( __( 'table (%s)', 'advanced-ads' ), '&lt;table&gt;' ),
				// translators: %s is an html tag.
				'li'          => sprintf( __( 'list item (%s)', 'advanced-ads' ), '&lt;li&gt;' ),
				// translators: %s is an html tag.
				'blockquote'  => sprintf( __( 'quote (%s)', 'advanced-ads' ), '&lt;blockquote&gt;' ),
				// translators: %s is an html tag.
				'iframe'      => sprintf( __( 'iframe (%s)', 'advanced-ads' ), '&lt;iframe&gt;' ),
				// translators: %s is an html tag.
				'div'         => sprintf( __( 'container (%s)', 'advanced-ads' ), '&lt;div&gt;' ),
				// any HTML tag.
				'anyelement'  => __( 'any element', 'advanced-ads' ),
				// custom
				'custom'      => _x( 'custom', 'for the "custom" content placement option', 'advanced-ads' ),
			]
		);

		return $tags;
	}

	/**
	 * Return content of a placement
	 *
	 * @param string $id slug of the display.
	 * @param array  $args optional arguments (passed to child).
	 *
	 * @return string
	 */
	public static function output( $id = '', $args = [] ) {
		// get placement data for the slug.
		if ( '' == $id ) {
			return;
		}

		$placements = Advanced_Ads::get_ad_placements_array();
		$placement  = ( isset( $placements[ $id ] ) && is_array( $placements[ $id ] ) ) ? $placements[ $id ] : [];

		if ( isset( $args['change-placement'] ) ) {
			// some options was provided by the user.
			$placement = Advanced_Ads_Utils::merge_deep_array( [ $placement, $args['change-placement'] ] );
		}

		if ( isset( $placement['item'] ) && '' !== $placement['item'] ) {
			$_item = explode( '_', $placement['item'] );

			if ( ! isset( $_item[1] ) || empty( $_item[1] ) ) {
				return;
			}

			// inject options.
			if ( isset( $placement['options'] ) && is_array( $placement['options'] ) ) {
				foreach ( $placement['options'] as $_k => $_v ) {
					if ( ! isset( $args[ $_k ] ) ) {
						$args[ $_k ] = $_v;
					}
				}
			}

			// inject placement type.
			if ( isset( $placement['type'] ) ) {
				$args['placement_type'] = $placement['type'];
			}

			// options.
			$prefix = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();

			// return either ad or group content.
			switch ( $_item[0] ) {
				case 'ad':
				case Advanced_Ads_Select::AD:
					// create class from placement id (not if header injection).
					if ( ! isset( $placement['type'] ) || 'header' !== $placement['type'] ) {
						if ( ! isset( $args['output'] ) ) {
							$args['output'] = [];
						}
						if ( ! isset( $args['output']['class'] ) ) {
							$args['output']['class'] = [];
						}
						$class = $prefix . $id;
						if ( ! in_array( $class, $args['output']['class'] ) ) {
							$args['output']['class'][] = $class;
						}
					}

					// fix method id.
					$_item[0] = Advanced_Ads_Select::AD;

					/**
					 * Deliver the translated version of an ad if set up with WPML.
					 * If an ad is not translated, show the ad in the original language when this is the selected option in the WPML settings.
					 *
					 * @source https://wpml.org/wpml-hook/wpml_object_id/
					 * @source https://wpml.org/forums/topic/backend-custom-post-types-page-overview-with-translation-options/
					 *
					 */
					if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
						global $sitepress;
						$_item[1] = apply_filters( 'wpml_object_id', $_item[1], 'advanced_ads', $sitepress->is_display_as_translated_post_type( 'advanced_ads' ) );
					}
					break;

				case Advanced_Ads_Select::PLACEMENT:
					// avoid loops (programmatical error).
					return;

				case Advanced_Ads_Select::GROUP:
					$class = $prefix . $id;
					if ( ( isset( $placement['type'] ) && $placement['type'] !== 'header' )
						 && ( ! isset( $args['output']['class'] )
							  || ! is_array( $args['output']['class'] )
							  || ! in_array( $class, $args['output']['class'] ) ) ) {
						$args['output']['class'][] = $class;
					}
				default:
			}

			// create placement id for various features.
			$args['output']['placement_id'] = $id;

			// add the placement to the global output array.
			$advads = Advanced_Ads::get_instance();
			$name   = isset( $placement['name'] ) ? $placement['name'] : $id;

			$result = Advanced_Ads_Select::get_instance()->get_ad_by_method( (int) $_item[1], $_item[0], $args );

			if ( $result && ( ! isset( $args['global_output'] ) || $args['global_output'] ) ) {
				$advads->current_ads[] = [
					'type'  => 'placement',
					'id'    => $id,
					'title' => $name,
				];
			}

			return $result;
		}

		return;
	}

	/**
	 * Inject ads directly into the content
	 *
	 * @param string $placement_id Id of the placement.
	 * @param array  $placement_opts Placement options.
	 * @param string $content Content to inject placement into.
	 *
	 * @return string $content Content with injected placement.
	 * @since 1.2.1
	 */
	public static function &inject_in_content( $placement_id, $placement_opts, &$content ) {
		return Advanced_Ads_In_Content_Injector::inject_in_content( $placement_id, $placement_opts, $content );
	}

	/**
	 * Check if the placement can be displayed
	 *
	 * @param int $id placement id.
	 *
	 * @return bool true if placement can be displayed
	 * @since 1.6.9
	 */
	public static function can_display( $id = 0 ) {
		if ( ! isset( $id ) || 0 === $id ) {
			return true;
		}

		return apply_filters( 'advanced-ads-can-display-placement', true, $id );
	}

	/**
	 * Get the placements that includes the ad or group.
	 *
	 * @param string $type 'ad' or 'group'.
	 * @param int    $id Id.
	 *
	 * @return array
	 */
	public static function get_placements_by( $type, $id ) {
		$result = [];

		$placements = Advanced_Ads::get_ad_placements_array();
		foreach ( $placements as $_id => $_placement ) {
			if ( isset( $_placement['item'] ) && $_placement['item'] === $type . '_' . $id ) {
				$result[ $_id ] = $_placement;
			}
		}

		return $result;
	}

	/**
	 * Get the markup for the group/ad selection for each placement.
	 *
	 * @param string $slug      Slug for current placement. This is passed to the view.
	 * @param array  $placement The current placement.
	 *
	 * @return string
	 */
	public static function get_items_for_placement_markup( $slug, $placement ) {
		$placement['item'] = $placement['item'] ?? '';
		// Get the currently selected item.
		$placement_item_array = explode( '_', $placement['item'] );
		$placement_item_type  = $placement_item_array[0];
		$placement_item_id    = (int) ( $placement_item_array[1] ?? 0 );

		$items = self::get_items_for_placement( $placement['type'], $placement['item'] );

		// check for missing items
		if ( $placement_item_type && ! array_key_exists( $placement['item'], $items[ $placement_item_type . 's' ]['items'] ) ) {
			$method = $placement_item_type === 'group' ? 'get_ad_groups' : 'get_ads';
			$item   = \Advanced_Ads::get_instance()->get_model()->{$method}( [ 'include' => $placement_item_id ] )[0] ?? null;

			$items[ $placement_item_type . 's' ]['items'][ $placement['item'] ] = [
				'selected' => true,
				'disabled' => true,
			];

			if ( $item instanceof WP_Post ) {
				$items[ $placement_item_type . 's' ]['items'][ $placement['item'] ]['name'] = $item->post_title;
			} elseif ( $item instanceof Advanced_Ads_Group ) {
				$items[ $placement_item_type . 's' ]['items'][ $placement['item'] ]['name'] = $item->name;
			} else {
				unset( $items[ $placement_item_type . 's' ]['items'][ $placement['item'] ] );
			}

			if ( isset( $items[ $placement_item_type . 's' ]['items'][ $placement['item'] ] ) ) {
				$items = array_map( static function( $items_group ) {
					$keys = array_column( $items_group['items'], 'name' );
					array_multisort( $keys, SORT_ASC, SORT_NATURAL, $items_group['items'] );

					return $items_group;
				}, $items );
			}
		}

		$items = array_filter( $items, static function( $items_group ) {
			return ! empty( $items_group['items'] );
		} );

		ob_start();

		include ADVADS_ABSPATH . 'admin/views/placements-item.php';

		return ob_get_clean();
	}

	/**
	 * Get the available items for the selected placement.
	 *
	 * @param string $type The current placement type.
	 * @param string $item The ad/group id.
	 *
	 * @return array[]
	 */
	public static function get_items_for_placement( string $type, string $item = 'ad_0' ) : iterable {
		$placement_type = self::get_placement_types()[ $type ];
		$items          = [
			'groups' => [
				'label' => __( 'Ad Groups', 'advanced-ads' ),
				'items' => $placement_type->get_allowed_groups(),
			],
			'ads'    => [
				'label' => __( 'Ads', 'advanced-ads' ),
				'items' => $placement_type->get_allowed_ads(),
			],
		];

		return array_map( static function( $items_group ) use ( $item ) {
			array_walk( $items_group['items'], static function( &$value, $key ) use ( $item ) {
				$value = [
					'name'     => $value,
					'selected' => $key === $item,
					'disabled' => false,
				];
			} );

			return $items_group;
		}, $items );
	}

	/**
	 * Get paths of ancestors that should not contain ads.
	 *
	 * @param object $xpath DOMXPath object.
	 *
	 * @return array Paths of ancestors.
	 */
	private static function get_ancestors_to_limit( $xpath ) {
		$query = self::get_ancestors_to_limit_query();
		if ( ! $query ) {
			return [];
		}

		$node_list          = $xpath->query( $query );
		$ancestors_to_limit = [];

		foreach ( $node_list as $a ) {
			$ancestors_to_limit[] = $a->getNodePath();
		}

		return $ancestors_to_limit;
	}


	/**
	 * Remove paragraphs that has ancestors that should not contain ads.
	 *
	 * @param array $paragraphs An array of `DOMNode` objects to insert ads before or after.
	 * @param array $ancestors_to_limit Paths of ancestor that should not contain ads.
	 *
	 * @return array $new_paragraphs An array of `DOMNode` objects to insert ads before or after.
	 */
	private static function filter_by_ancestors_to_limit( $paragraphs, $ancestors_to_limit ) {
		$new_paragraphs = [];

		foreach ( $paragraphs as $k => $paragraph ) {
			foreach ( $ancestors_to_limit as $a ) {
				if ( 0 === stripos( $paragraph->getNodePath(), $a ) ) {
					continue 2;
				}
			}

			$new_paragraphs[] = $paragraph;
		}

		return $new_paragraphs;
	}

	/**
	 * Get query to select ancestors that should not contain ads.
	 *
	 * @return string/false DOMXPath query or false.
	 */
	private static function get_ancestors_to_limit_query() {
		/**
		 * TODO:
		 * - support `%` (rand) at the start
		 * - support plain text that node should contain instead of CSS selectors
		 * - support `prev` and `next` as `type`
		 */

		/**
		 * Filter the nodes that limit injection.
		 *
		 * @param array An array of arrays, each of which contains:
		 *
		 * @type string $type Accept: `ancestor` - limit injection inside the ancestor.
		 * @type string $node A "class selector" which targets one class (.) or "id selector" which targets one id (#),
		 *                        optionally with `%` at the end.
		 */
		$items = apply_filters(
			'advanced-ads-content-injection-nodes-without-ads',
			[
				[
					// a class anyone can use to prevent automatic ad injection into a specific element.
					'node' => '.advads-stop-injection',
					'type' => 'ancestor',
				],
				[
					// Product Slider for Beaver Builder by WooPack.
					'node' => '.woopack-product-carousel',
					'type' => 'ancestor',
				],
				[
					// WP Author Box Lite.
					'node' => '#wpautbox-%',
					'type' => 'ancestor',
				],
				[
					// GeoDirectory Post Slider.
					'node' => '.geodir-post-slider',
					'type' => 'ancestor',
				],
			]
		);

		$query = [];
		foreach ( $items as $p ) {
			$sel = $p['node'];

			$sel_type = substr( $sel, 0, 1 );
			$sel      = substr( $sel, 1 );

			$rand_pos = strpos( $sel, '%' );
			$sel      = str_replace( '%', '', $sel );
			$sel      = sanitize_html_class( $sel );

			if ( '.' === $sel_type ) {
				if ( false !== $rand_pos ) {
					$query[] = "@class and contains(concat(' ', normalize-space(@class), ' '), ' $sel')";
				} else {
					$query[] = "@class and contains(concat(' ', normalize-space(@class), ' '), ' $sel ')";
				}
			}
			if ( '#' === $sel_type ) {
				if ( false !== $rand_pos ) {
					$query[] = "@id and starts-with(@id, '$sel')";
				} else {
					$query[] = "@id and @id = '$sel'";
				}
			}
		}

		if ( ! $query ) {
			return false;
		}

		return '//*[' . implode( ' or ', $query ) . ']';
	}

	/**
	 * Sort placements
	 *
	 * @param array  $placements Existing placements.
	 * @param string $orderby The field to order by. Accept `name` or `type`.
	 * @return array $placements Sorted placements.
	 */
	public static function sort( $placements, $orderby = 'name' ) {
		if ( ! is_array( $placements ) ) {
			return [];
		}
		if ( 'name' === $orderby ) {
			ksort( $placements, SORT_NATURAL );
			return $placements;
		}
		uasort( $placements, [ 'Advanced_Ads_Placements', 'sort_by_type_callback' ] );
		return $placements;

	}

	/**
	 * Callback to sort placements by type.
	 *
	 * @param array $f First placement.
	 * @param array $s Second placement.
	 * @return int 0 If placements are equal, -1 if the first should come first, 1 otherwise.
	 */
	private static function sort_by_type_callback( $f, $s ) {
		// A placement with the "Words Between Ads" option set to non-zero gets injected after others
		// because it reads existing ads.
		if ( ! empty( $f['options']['words_between_repeats'] ) xor ! empty( $s['options']['words_between_repeats'] ) ) {
			return ! empty( $f['options']['words_between_repeats'] ) ? 1 : -1;
		}

		$types = self::get_placement_types();

		$f_o = ( isset( $f['type'] ) && isset( $types[ $f['type'] ]['order'] ) ) ? $types[ $f['type'] ]['order'] : 100;
		$s_o = ( isset( $s['type'] ) && isset( $types[ $s['type'] ]['order'] ) ) ? $types[ $s['type'] ]['order'] : 100;

		if ( $f_o === $s_o ) {
			// Sort by index.
			if ( 'post_content' === $f['type'] && isset( $f['options']['index'] ) && isset( $s['options']['index'] )
				&& $f['options']['index'] !== $s['options']['index'] ) {
				return ( $f['options']['index'] < $s['options']['index'] ) ? -1 : 1;
			}

			// Sort by name.
			if ( isset( $f['name'] ) && isset( $s['name'] ) ) {
				return 0 > strnatcmp( $f['name'], $s['name'] ) ? -1 : 1;
			}
			return 0;
		}

		// Sort by order.
		return ( $f_o < $s_o ) ? -1 : 1;

	}


}
