<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

use AdvancedAds\Utilities\WordPress;

/**
 * Injects ads in the content based on an XPath expression.
 */
class Advanced_Ads_In_Content_Injector {

	/**
	 * Gather placeholders which later are replaced by the ads
	 *
	 * @var array $ads_for_placeholders
	 */
	private static $ads_for_placeholders = [];

	/**
	 * Inject ads directly into the content
	 *
	 * @param string $placement_id Id of the placement.
	 * @param array  $placement_opts Placement options.
	 * @param string $content Content to inject placement into.
	 * @param array  $options {
	 *     Injection options.
	 *
	 *     @type bool   $allowEmpty                   Whether the tag can be empty to be counted.
	 *     @type bool   $paragraph_select_from_bottom Whether to select ads from buttom.
	 *     @type string $position                     Position. Can be one of 'before', 'after', 'append', 'prepend'
	 *     @type number $alter_nodes                  Whether to alter nodes, for example to prevent injecting ads into `a` tags.
	 *     @type bool   $repeat                       Whether to repeat the position.
	 *     @type number $paragraph_id                 Paragraph Id.
	 *     @type number $itemLimit                    If there are too few items at this level test nesting. Set to '-1` to prevent testing.
	 * }
	 *
	 * @return string $content Content with injected placement.
	 */
	public static function &inject_in_content( $placement_id, $placement_opts, &$content, $options = [] ) {
		if ( ! extension_loaded( 'dom' ) ) {
			return $content;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase

		// parse arguments.
		$tag = isset( $placement_opts['tag'] ) ? $placement_opts['tag'] : 'p';
		$tag = preg_replace( '/[^a-z0-9]/i', '', $tag ); // simplify tag.
		/**
		 * Store the original tag value since $tag is changed on the fly and we might want to know the original selected
		 * options for some checks later.
		 */
		$tag_option = $tag;

		// allow more complex xPath expression.
		$tag = apply_filters( 'advanced-ads-placement-content-injection-xpath', $tag, $placement_opts );

		// get plugin options.
		$plugin_options = Advanced_Ads::get_instance()->options();

		$defaults = [
			'allowEmpty'                   => false,
			'paragraph_select_from_bottom' => isset( $placement_opts['start_from_bottom'] ) && $placement_opts['start_from_bottom'],
			'position'                     => isset( $placement_opts['position'] ) ? $placement_opts['position'] : 'after',
			// only has before and after.
			'before'                       => isset( $placement_opts['position'] ) && 'before' === $placement_opts['position'],
			// Whether to alter nodes, for example to prevent injecting ads into `a` tags.
			'alter_nodes'                  => true,
			'repeat'                       => false,
		];

		$defaults['paragraph_id'] = isset( $placement_opts['index'] ) ? $placement_opts['index'] : 1;
		$defaults['paragraph_id'] = max( 1, (int) $defaults['paragraph_id'] );

		// if there are too few items at this level test nesting.
		$defaults['itemLimit'] = 'p' === $tag_option ? 2 : 1;

		// trigger such a high item limit that all elements will be considered.
		if ( ! empty( $plugin_options['content-injection-level-disabled'] ) ) {
			$defaults['itemLimit'] = 1000;
		}

		// Handle tags that are empty by definition or could be empty ("custom" option).
		if ( in_array( $tag_option, [ 'img', 'iframe', 'custom' ], true ) ) {
			$defaults['allowEmpty'] = true;
		}

		// Merge the options if possible. If there are common keys, we don't merge them to prevent overriding and unexpected behavior.
		$common_keys = array_intersect_key( $options, $placement_opts );
		if ( empty( $common_keys ) ) {
			$options = array_merge( $options, $placement_opts );
		}

		// allow hooks to change some options.
		$options = apply_filters(
			'advanced-ads-placement-content-injection-options',
			wp_parse_args( $options, $defaults ),
			$tag_option
		);

		$wp_charset = get_bloginfo( 'charset' );
		// parse document as DOM (fragment - having only a part of an actual post given).

		$content_to_load = self::get_content_to_load( $content, $wp_charset );
		if ( ! $content_to_load ) {
			return $content;
		}

		$dom = new DOMDocument( '1.0', $wp_charset );
		// may loose some fragments or add autop-like code.
		$libxml_use_internal_errors = libxml_use_internal_errors( true ); // avoid notices and warnings - html is most likely malformed.

		$success = $dom->loadHtml( '<!DOCTYPE html><html><meta http-equiv="Content-Type" content="text/html; charset=' . $wp_charset . '" /><body>' . $content_to_load );
		libxml_use_internal_errors( $libxml_use_internal_errors );
		if ( true !== $success ) {
			// -TODO handle cases were dom-parsing failed (at least inform user)
			return $content;
		}

		/**
		 * Handle advanced tags.
		 */
		switch ( $tag_option ) {
			case 'p':
				// Exclude paragraphs within blockquote tags.
				$tag = 'p[not(parent::blockquote)]';
				break;
			case 'pwithoutimg':
				// Convert option name into correct path, exclude paragraphs within blockquote tags.
				$tag = 'p[not(descendant::img) and not(parent::blockquote)]';
				break;
			case 'img':
				/*
				 * Handle: 1) "img" tags 2) "image" block 3) "gallery" block 4) "gallery shortcode" 5) "wp_caption" shortcode
				 * Handle the gallery created by the block or the shortcode as one image.
				 * Prevent injection of ads next to images in tables.
				*/
				// Default shortcodes, including non-HTML5 versions.
				$shortcodes = "@class and (
						contains(concat(' ', normalize-space(@class), ' '), ' gallery-size') or
						contains(concat(' ', normalize-space(@class), ' '), ' wp-caption ') )";
				$tag        = "*[self::img or self::figure or self::div[$shortcodes]]
					[not(ancestor::table or ancestor::figure or ancestor::div[$shortcodes])]";
				break;
			// Any headline. By default h2, h3, and h4.
			case 'headlines':
				$headlines = apply_filters( 'advanced-ads-headlines-for-ad-injection', [ 'h2', 'h3', 'h4' ] );

				foreach ( $headlines as &$headline ) {
					$headline = 'self::' . $headline;
				}
				$tag = '*[' . implode( ' or ', $headlines ) . ']'; // /html/body/*[self::h2 or self::h3 or self::h4]
				break;
			// Any HTML element that makes sense in the content.
			case 'anyelement':
				$exclude = [
					'html',
					'body',
					'script',
					'style',
					'tr',
					'td',
					// Inline tags.
					'a',
					'abbr',
					'b',
					'bdo',
					'br',
					'button',
					'cite',
					'code',
					'dfn',
					'em',
					'i',
					'img',
					'kbd',
					'label',
					'option',
					'q',
					'samp',
					'select',
					'small',
					'span',
					'strong',
					'sub',
					'sup',
					'textarea',
					'time',
					'tt',
					'var',
				];
				$tag     = '*[not(self::' . implode( ' or self::', $exclude ) . ')]';
				break;
			case 'custom':
				// Get the path for the "custom" tag choice, use p as a fallback to prevent it from showing any ads if users left it empty.
				$tag = ! empty( $placement_opts['xpath'] ) ? stripslashes( $placement_opts['xpath'] ) : 'p';
				break;
		}

		// select positions.
		$xpath = new DOMXPath( $dom );

		if ( -1 !== $options['itemLimit'] ) {
			$items = $xpath->query( '/html/body/' . $tag );

			if ( $items->length < $options['itemLimit'] ) {
				$items = $xpath->query( '/html/body/*/' . $tag );
			}
			// try third level.
			if ( $items->length < $options['itemLimit'] ) {
				$items = $xpath->query( '/html/body/*/*/' . $tag );
			}
			// try all levels as last resort.
			if ( $items->length < $options['itemLimit'] ) {
				$items = $xpath->query( '//' . $tag );
			}
		} else {
			$items = $xpath->query( $tag );
		}

		// allow to select other elements.
		$items = apply_filters( 'advanced-ads-placement-content-injection-items', $items, $xpath, $tag_option );

		// filter empty tags from items.
		$whitespaces = json_decode( '"\t\n\r \u00A0"' );
		$paragraphs  = [];
		foreach ( $items as $item ) {
			if ( $options['allowEmpty'] || ( isset( $item->textContent ) && trim( $item->textContent, $whitespaces ) !== '' ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
				$paragraphs[] = $item;
			}
		}

		$ancestors_to_limit = self::get_ancestors_to_limit( $xpath );
		$paragraphs         = self::filter_by_ancestors_to_limit( $paragraphs, $ancestors_to_limit );

		$options['paragraph_count'] = count( $paragraphs );

		if ( $options['paragraph_count'] >= $options['paragraph_id'] ) {
			$offset     = $options['paragraph_select_from_bottom'] ? $options['paragraph_count'] - $options['paragraph_id'] : $options['paragraph_id'] - 1;
			$offsets    = apply_filters( 'advanced-ads-placement-content-offsets', [ $offset ], $options, $placement_opts, $xpath, $paragraphs, $dom );
			$did_inject = false;

			foreach ( $offsets as $offset ) {

				// inject.
				$node = apply_filters( 'advanced-ads-placement-content-injection-node', $paragraphs[ $offset ], $tag, $options['before'] );

				if ( $options['alter_nodes'] ) {
					// Prevent injection into image caption and gallery.
					$parent = $node;
					for ( $i = 0; $i < 4; $i++ ) {
						$parent = $parent->parentNode;
						if ( ! $parent instanceof DOMElement ) {
							break;
						}
						if ( preg_match( '/\b(wp-caption|gallery-size)\b/', $parent->getAttribute( 'class' ) ) ) {
							$node = $parent;
							break;
						}
					}

					// Make sure that the ad is injected outside the link.
					if ( 'img' === $tag_option && 'a' === $node->parentNode->tagName ) {
						if ( $options['before'] ) {
							$node->parentNode;
						} else {
							// Go one level deeper if inserted after to not insert the ad into the link; probably after the paragraph.
							$node->parentNode->parentNode;
						}
					}
				}

				$ad_content = (string) Advanced_Ads_Select::get_instance()->get_ad_by_method( $placement_id, 'placement', $placement_opts );

				if ( trim( $ad_content, $whitespaces ) === '' ) {
					continue;
				}

				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
				$ad_content = self::filter_ad_content( $ad_content, $node->tagName, $options );

				// Convert HTML to XML!.
				$ad_dom                     = new DOMDocument( '1.0', $wp_charset );
				$libxml_use_internal_errors = libxml_use_internal_errors( true );
				$ad_dom->loadHtml( '<!DOCTYPE html><html><meta http-equiv="Content-Type" content="text/html; charset=' . $wp_charset . '" /><body>' . $ad_content );

				switch ( $options['position'] ) {
					case 'append':
						$ref_node = $node;

						foreach ( $ad_dom->getElementsByTagName( 'body' )->item( 0 )->childNodes as $importedNode ) {
							$importedNode = $dom->importNode( $importedNode, true );
							$ref_node->appendChild( $importedNode );
						}
						break;
					case 'prepend':
						$ref_node = $node;

						foreach ( $ad_dom->getElementsByTagName( 'body' )->item( 0 )->childNodes as $importedNode ) {
							$importedNode = $dom->importNode( $importedNode, true );
							$ref_node->insertBefore( $importedNode, $ref_node->firstChild );
						}
						break;
					case 'before':
						$ref_node = $node;

						foreach ( $ad_dom->getElementsByTagName( 'body' )->item( 0 )->childNodes as $importedNode ) {
							$importedNode = $dom->importNode( $importedNode, true );
							$ref_node->parentNode->insertBefore( $importedNode, $ref_node );
						}
						break;
					case 'after':
					default:
						// append before next node or as last child to body.
						$ref_node = $node->nextSibling;
						if ( isset( $ref_node ) ) {
							foreach ( $ad_dom->getElementsByTagName( 'body' )->item( 0 )->childNodes as $importedNode ) {
								$importedNode = $dom->importNode( $importedNode, true );
								$ref_node->parentNode->insertBefore( $importedNode, $ref_node );
							}
						} else {
							// Append to body; -TODO using here that we only select direct children of the body tag.
							foreach ( $ad_dom->getElementsByTagName( 'body' )->item( 0 )->childNodes as $importedNode ) {
								$importedNode = $dom->importNode( $importedNode, true );
								$node->parentNode->appendChild( $importedNode );
							}
						}
				}
				libxml_use_internal_errors( $libxml_use_internal_errors );
				$did_inject = true;
			}

			if ( ! $did_inject ) {
				return $content;
			}

			$content_orig = $content;
			// convert to text-representation.
			$content = $dom->saveHTML();
			$content = self::prepare_output( $content, $content_orig );

			/**
			 * Show a warning to ad admins in the Ad Health bar in the frontend, when
			 *
			 * * the level limitation was not disabled
			 * * could not inject one ad (as by use of `elseif` here)
			 * * but there are enough elements on the site, but just in sub-containers
			 */
		} elseif (
			WordPress::user_can( 'advanced_ads_manage_options' ) &&
			-1 !== $options['itemLimit'] &&
			empty( $plugin_options['content-injection-level-disabled'] )
		) {

			// Check if there are more elements without limitation.
			$all_items = $xpath->query( '//' . $tag );

			$paragraphs = [];
			foreach ( $all_items as $item ) {
				if ( $options['allowEmpty'] || ( isset( $item->textContent ) && trim( $item->textContent, $whitespaces ) !== '' ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
					$paragraphs[] = $item;
				}
			}

			$paragraphs = self::filter_by_ancestors_to_limit( $paragraphs, $ancestors_to_limit );
			if ( $options['paragraph_id'] <= count( $paragraphs ) ) {
				// Add a warning to ad health.
				add_filter( 'advanced-ads-ad-health-nodes', [ 'Advanced_Ads_In_Content_Injector', 'add_ad_health_node' ] );
			}
		}

		// phpcs:enable
		return $content;
	}

	/**
	 * Get content to load.
	 *
	 * @param string $content Original content.
	 * @param string $wp_charset blog charset.
	 *
	 * @return string $content Content to load.
	 */
	private static function get_content_to_load( $content, $wp_charset ) {
		// Prevent removing closing tags in scripts.
		$content_to_load = preg_replace( '/<script.*?<\/script>/si', '<!--\0-->', $content );

		// check which priority the wpautop filter has; might have been disabled on purpose.
		$wpautop_priority = has_filter( 'the_content', 'wpautop' );
		if ( $wpautop_priority && Advanced_Ads_Plugin::get_instance()->get_content_injection_priority() < $wpautop_priority ) {
			$content_to_load = wpautop( $content_to_load );
		}

		return $content_to_load;
	}

	/**
	 * Filter ad content.
	 *
	 * @param string $ad_content Ad content.
	 * @param string $tag_name tar before/after the content.
	 * @param array  $options Injection options.
	 *
	 * @return string ad content.
	 */
	private static function filter_ad_content( $ad_content, $tag_name, $options ) {
		// Replace `</` with `<\/` in ad content when placed within `document.write()` to prevent code from breaking.
		$ad_content = preg_replace( '#(document.write.+)</(.*)#', '$1<\/$2', $ad_content );

		// Inject placeholder.
		$id                           = count( self::$ads_for_placeholders );
		self::$ads_for_placeholders[] = [
			'id'   => $id,
			'tag'  => $tag_name,
			'position' => $options['position'],
			'ad'   => $ad_content,
		];

		return '%advads_placeholder_' . $id . '%';
	}

	/**
	 * Prepare output.
	 *
	 * @param string $content Modified content.
	 * @param string $content_orig Original content.
	 *
	 * @return string $content Content to output.
	 */
	private static function prepare_output( $content, $content_orig ) {
		$content                    = self::inject_ads( $content, $content_orig, self::$ads_for_placeholders );
		self::$ads_for_placeholders = [];

		return $content;
	}

	/**
	 * Search for ad placeholders in the `$content` to determine positions at which to inject ads.
	 * Given the positions, inject ads into `$content_orig.
	 *
	 * @param string $content Post content with injected ad placeholders.
	 * @param string $content_orig Unmodified post content.
	 * @param array  $ads_for_placeholders Array of ads.
	 *  Each ad contains placeholder id, before or after which tag to inject the ad, the ad content.
	 *
	 * @return string $content
	 */
	private static function inject_ads( $content, $content_orig, $ads_for_placeholders ) {
		$self_closing_tags = [
			'area',
			'base',
			'basefont',
			'bgsound',
			'br',
			'col',
			'embed',
			'frame',
			'hr',
			'img',
			'input',
			'keygen',
			'link',
			'meta',
			'param',
			'source',
			'track',
			'wbr',
		];

		// It is not possible to append/prepend in self closing tags.
		foreach ( $ads_for_placeholders as &$ad_content ) {
			if ( ( 'prepend' === $ad_content['position'] || 'append' === $ad_content['position'] )
				 && in_array( $ad_content['tag'], $self_closing_tags, true ) ) {
				$ad_content['position'] = 'after';
			}
		}
		unset( $ad_content );
		usort( $ads_for_placeholders, [ 'Advanced_Ads_In_Content_Injector', 'sort_ads_for_placehoders' ] );

		// Add tags before/after which ad placehoders were injected.
		$alts = [];
		foreach ( $ads_for_placeholders as $ad_content ) {
			$tag = $ad_content['tag'];

			switch ( $ad_content['position'] ) {
				case 'before':
				case 'prepend':
					$alts[] = "<{$tag}[^>]*>";
					break;
				case 'after':
					if ( in_array( $tag, $self_closing_tags, true ) ) {
						$alts[] = "<{$tag}[^>]*>";
					} else {
						$alts[] = "</{$tag}>";
					}
					break;
				case 'append':
					$alts[] = "</{$tag}>";
					break;
			}
		}

		$alts       = array_unique( $alts );
		$tag_regexp = implode( '|', $alts );
		// Add ad placeholder.
		$alts[]                     = '%advads_placeholder_(?:\d+)%';
		$tag_and_placeholder_regexp = implode( '|', $alts );

		preg_match_all( "#{$tag_and_placeholder_regexp}#i", $content, $tag_matches );
		$count = 0;

		// For each tag located before/after an ad placeholder, find its offset among the same tags.
		foreach ( $tag_matches[0] as $r ) {
			if ( preg_match( '/%advads_placeholder_(\d+)%/', $r, $result ) ) {
				$id       = $result[1];
				$found_ad = false;
				foreach ( $ads_for_placeholders as $n => $ad ) {
					if ( (int) $ad['id'] === (int) $id ) {
						$found_ad = $ad;
						break;
					}
				}
				if ( ! $found_ad ) {
					continue;
				}

				switch ( $found_ad['position'] ) {
					case 'before':
					case 'append':
						$ads_for_placeholders[ $n ]['offset'] = $count;
						break;
					case 'after':
					case 'prepend':
						$ads_for_placeholders[ $n ]['offset'] = $count - 1;
						break;
				}
			} else {
				++$count;
			}
		}

		// Find tags before/after which we need to inject ads.
		preg_match_all( "#{$tag_regexp}#i", $content_orig, $orig_tag_matches, PREG_OFFSET_CAPTURE );
		$new_content = '';
		$pos         = 0;

		foreach ( $orig_tag_matches[0] as $n => $r ) {
			$to_inject = [];
			// Check if we need to inject an ad at this offset.
			foreach ( $ads_for_placeholders as $ad ) {
				if ( isset( $ad['offset'] ) && $ad['offset'] === $n ) {
					$to_inject[] = $ad;
				}
			}

			foreach ( $to_inject as $item ) {
				switch ( $item['position'] ) {
					case 'before':
					case 'append':
						$found_pos = $r[1];
						break;
					case 'after':
					case 'prepend':
						$found_pos = $r[1] + strlen( $r[0] );
						break;
				}

				$new_content .= substr( $content_orig, $pos, $found_pos - $pos );
				$pos          = $found_pos;
				$new_content .= $item['ad'];
			}
		}
		$new_content .= substr( $content_orig, $pos );

		return $new_content;
	}


	/**
	 * Callback function for usort() to sort ads for placeholders.
	 *
	 * @param array $first The first array to compare.
	 * @param array $second The second array to compare.
	 *
	 * @return int 0 if both objects equal. -1 if second array should come first, 1 otherwise.
	 */
	public static function sort_ads_for_placehoders( $first, $second ) {
		if ( $first['position'] === $second['position'] ) {
			return 0;
		}

		$num = [
			'before'  => 1,
			'prepend' => 2,
			'append'  => 3,
			'after'   => 4,
		];

		return $num[ $first['position'] ] > $num[ $second['position'] ] ? 1 : - 1;
	}

	/**
	 * Add a warning to 'Ad health'.
	 *
	 * @param array $nodes .
	 *
	 * @return array $nodes.
	 */
	public static function add_ad_health_node( $nodes ) {
		$nodes[] = [
			'type' => 1,
			'data' => [
				'parent' => 'advanced_ads_ad_health',
				'id'     => 'advanced_ads_ad_health_the_content_not_enough_elements',
				'title'  => sprintf(
				/* translators: %s stands for the name of the "Disable level limitation" option and automatically translated as well */
					__( 'Set <em>%s</em> to show more ads', 'advanced-ads' ),
					__( 'Disable level limitation', 'advanced-ads' )
				),
				'href'   => admin_url( '/admin.php?page=advanced-ads-settings#top#general' ),
				'meta'   => [
					'class'  => 'advanced_ads_ad_health_warning',
					'target' => '_blank',
				],
			],
		];

		return $nodes;
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
}
