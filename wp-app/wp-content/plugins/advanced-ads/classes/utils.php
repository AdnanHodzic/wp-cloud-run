<?php
/**
 * Class Advanced_Ads_Utils
 */
class Advanced_Ads_Utils {
	/**
	 * Merges multiple arrays, recursively, and returns the merged array.
	 *
	 * This function is similar to PHP's array_merge_recursive() function, but it
	 * handles non-array values differently. When merging values that are not both
	 * arrays, the latter value replaces the former rather than merging with it.
	 *
	 * Example:
	 * $link_options_1 = array( 'fragment' => 'x', 'class' => array( 'a', 'b' ) );
	 * $link_options_2 = array( 'fragment' => 'y', 'class' => array( 'c', 'd' ) );
	 * // This results in array( 'fragment' => 'y', 'class' => array( 'a', 'b', 'c', 'd' ) ).
	 *
	 * @param array $arrays An arrays of arrays to merge.
	 * @param bool  $preserve_integer_keys (optional) If given, integer keys will be preserved and merged instead of appended.
	 * @return array The merged array.
	 * @copyright Copyright 2001 - 2013 Drupal contributors. License: GPL-2.0+. Drupal is a registered trademark of Dries Buytaert.
	 */
	public static function merge_deep_array( array $arrays, $preserve_integer_keys = false ) {
		$result = [];
		foreach ( $arrays as $array ) {
			if ( ! is_array( $array ) ) {
				continue; }

			foreach ( $array as $key => $value ) {
				// Renumber integer keys as array_merge_recursive() does unless
				// $preserve_integer_keys is set to TRUE. Note that PHP automatically
				// converts array keys that are integer strings (e.g., '1') to integers.
				if ( is_integer( $key ) && ! $preserve_integer_keys ) {
					$result[] = $value;
				} elseif ( isset( $result[ $key ] ) && is_array( $result[ $key ] ) && is_array( $value ) ) {
					// recurse when both values are arrays.
					$result[ $key ] = self::merge_deep_array( [ $result[ $key ], $value ], $preserve_integer_keys );
				} else {
					// otherwise, use the latter value, overriding any previous value.
					$result[ $key ] = $value;
				}
			}
		}
		return $result;
	}

	/**
	 * Convert array of html attributes to string.
	 *
	 * @param array $data attributes.
	 * @return string
	 * @since untagged
	 */
	public static function build_html_attributes( $data ) {
		$result = '';
		foreach ( $data as $_html_attr => $_values ) {
			if ( 'style' === $_html_attr ) {
				$_style_values_string = '';
				foreach ( $_values as $_style_attr => $_style_values ) {
					if ( is_array( $_style_values ) ) {
						$_style_values_string .= $_style_attr . ': ' . implode( ' ', array_filter( $_style_values ) ) . '; ';
					} else {
						$_style_values_string .= $_style_attr . ': ' . $_style_values . '; ';
					}
				}
				$result .= " style=\"$_style_values_string\"";
			} else {
				if ( is_array( $_values ) ) {
					$_values_string = esc_attr( implode( ' ', array_filter( $_values ) ) );
				} else {
					$_values_string = esc_attr( $_values );
				}
				if ( $_values_string !== '' ) {
					$result .= " $_html_attr=\"$_values_string\"";
				}
			}
		}
		return $result;
	}

	/**
	 * Get inline asset.
	 *
	 * @param string $content existing content.
	 * @return string $content
	 */
	public static function get_inline_asset( $content ) {
		// WP Fastest Cache Premium: "Render Blocking Js" feature.
		$content = ltrim( $content );
		if ( class_exists( 'WpFastestCache', false )
			&& '<script' === substr( $content, 0, 7 ) ) {
				$content = substr_replace( $content, '<script data-wpfc-render="false"', 0, 7 );
		}

		if ( Advanced_Ads_Checks::active_autoptimize() || Advanced_Ads_Checks::active_wp_rocket() ) {
			return '<!--noptimize-->' . $content . '<!--/noptimize-->';
		}
		return $content;
	}

	/**
	 * Get nested ads of an ad or a group.
	 *
	 * @param string $id Id.
	 * @param string $type Type (placement, ad or group).
	 * @return array of Advanced_Ads_Ad objects.
	 */
	public static function get_nested_ads( $id, $type ) {
		$result = [];

		switch ( $type ) {
			case 'placement':
				$placements = Advanced_Ads::get_ad_placements_array();
				if ( isset( $placements[ $id ]['item'] ) ) {
					$item = explode( '_', $placements[ $id ]['item'] );
					if ( isset( $item[1] ) ) {
						return self::get_nested_ads( $item[1], $item[0] );
					}
				}
			case 'ad':
				$ad       = \Advanced_Ads\Ad_Repository::get( $id );
				$result[] = $ad;
				if ( 'group' === $ad->type && ! empty( $ad->output['group_id'] ) ) {
					$result = array_merge( $result, self::get_nested_ads( $ad->output['group_id'], 'group' ) );
				}
				break;
			case 'group':
				$group = new Advanced_Ads_Group( $id );
				$ads   = $group->get_all_ads();
				foreach ( $ads as $ad ) {
					$result = array_merge( $result, self::get_nested_ads( $ad->ID, 'ad' ) );
				}
				break;
		}
		return $result;
	}

	/**
	 * Maybe translate a capability to a set of roles.
	 *
	 * @param string/array $roles_or_caps A set of roles or capabilities.
	 * @return array $roles A list of roles.
	 */
	public static function maybe_translate_cap_to_role( $roles_or_caps ) {
		global $wp_roles;

		$roles_or_caps = (array) $roles_or_caps;
		$roles         = [];

		foreach ( $roles_or_caps as $cap ) {
			if ( $wp_roles->is_role( $cap ) ) {
				$roles[] = $cap;
				continue;
			}

			foreach ( $wp_roles->roles as $id => $role ) {
				if ( isset( $role['capabilities'][ $cap ] ) ) {
					$roles[] = $id;
				}
			}
		}

		return array_unique( $roles );
	}

	/**
	 * Check if the page is loaded in an iframe.
	 *
	 * @return bool
	 */
	public static function is_iframe() {
		if ( is_customize_preview() ) {
			return true;
		}

		if ( self::is_elementor_preview_or_edit() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the Elementor preview mode is used.
	 *
	 * @deprecated
	 *
	 * @return bool
	 */
	private static function is_elementor_preview_or_edit() {
		if ( class_exists( '\Elementor\Plugin' ) ) {
			try {
				$refl_plugin = new ReflectionClass( '\Elementor\Plugin' );

				if ( $refl_plugin->hasMethod( 'instance' ) ) {
					$refl_instance_method = $refl_plugin->getMethod( 'instance' );

					if ( $refl_instance_method->isPublic() && $refl_instance_method->isStatic() ) {

						if ( class_exists( '\Elementor\Preview' ) && $refl_plugin->hasProperty( 'preview' ) ) {
							$preview_property = new ReflectionProperty( '\Elementor\Plugin', 'preview' );

							if ( $preview_property->isPublic() && ! $preview_property->isStatic() ) {
								if ( method_exists( '\Elementor\Preview', 'is_preview_mode' )
									&& \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
									return true;
								}
							}
						}

						if ( class_exists( '\Elementor\Editor' ) && $refl_plugin->hasProperty( 'editor' ) ) {
							$editor_property = new ReflectionProperty( '\Elementor\Plugin', 'editor' );

							if ( $editor_property->isPublic() && ! $editor_property->isStatic() ) {
								if ( method_exists( '\Elementor\Editor', 'is_edit_mode' )
									&& \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
									return true;
								}
							}
						}
					}
				}
			} catch ( Exception $e ) {
				// not much we can do here.
			}
		}
		return false;
	}

	/**
	 * Get DateTimeZone object for the WP installation
	 *
	 * @return DateTimeZone DateTimeZone object.
	 */
	public static function get_wp_timezone() {
		static $date_time_zone;
		if ( ! is_null( $date_time_zone ) ) {
			return $date_time_zone;
		}

		// wp_timezone() is available since WordPress 5.3.0.
		if ( function_exists( 'wp_timezone' ) ) {
			$date_time_zone = wp_timezone();

			return $date_time_zone;
		}

		$time_zone = get_option( 'timezone_string' );
		// no timezone string but gmt offset.
		if ( empty( $time_zone ) ) {
			$time_zone = get_option( 'gmt_offset' );
			// gmt + x but not prefixed with a "+".
			if ( preg_match( '/^\d/', $time_zone ) ) {
				$time_zone = '+' . $time_zone;
			}
		}

		$date_time_zone = new DateTimeZone( $time_zone );

		return $date_time_zone;
	}

	/**
	 * Get literal expression of timezone.
	 *
	 * @return string Human readable timezone name.
	 */
	public static function get_timezone_name() {
		$time_zone = self::get_wp_timezone()->getName();
		if ( $time_zone === 'UTC' ) {
			return 'UTC+0';
		}

		if ( strpos( $time_zone, '+' ) === 0 || strpos( $time_zone, '-' ) === 0 ) {
			return 'UTC' . $time_zone;
		}

		// translators: time zone name.
		return sprintf( __( 'time of %s', 'advanced-ads' ), $time_zone );
	}
}
