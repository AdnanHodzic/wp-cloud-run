<?php

namespace GRIM_SG\Vendor;

use GRIM_SG\Settings;

class Controller {
	public static $slug = 'xml-sitemap-generator-for-google';

	public function get_settings() {
		$settings      = new Settings();
		$saved_options = get_option( self::$slug );

		// TODO Remove this after Refactoring
		if ( is_array( $saved_options ) ) {
			$saved_options = (object) $saved_options;
		}

		if ( ! empty( $saved_options ) ) {
			foreach ( $settings as $key => &$option ) {
				if ( isset( $saved_options->{$key} ) ) {
					if ( in_array( $key, array( 'cpt', 'taxonomies' ), true ) ) {
						if ( is_array( $saved_options->{$key} ) ) {
							foreach ( $saved_options->{$key} as $inner_key => $value ) {
								$option[ $inner_key ] = is_array( $value )
									? (object) $value // TODO Remove this after Refactoring
									: $value;
							}
						}
					} else {
						$option = ( is_object( $option ) && is_array( $saved_options->{$key} ) )
							? (object) $saved_options->{$key} // TODO Remove this after Refactoring
							: $saved_options->{$key};
					}
				}
			}
		}

		return $settings;
	}

	/**
	 * Get Custom Post Types
	 * @return string[]|\WP_Post_Type[]
	 */
	public function get_cpt( $output = 'names' ) {
		$args = array(
			'public'   => true,
			'_builtin' => false,
		);

		return get_post_types( $args, $output );
	}

	/**
	 * Get Taxonomy Types
	 * @return string[]|\WP_Taxonomy[]
	 */
	public function get_taxonomy_types( $output = 'names' ) {
		$args = array(
			'public'  => true,
			'show_ui' => true,
		);

		return get_taxonomies( $args, $output );
	}

	/**
	 * Get Allowed Post Types List
	 * @return array
	 */
	public function get_post_types_list( $post_types, $settings ) {
		foreach ( $post_types as $key => $post_type ) {
			if ( isset( $settings->{$post_type}->include ) && ! $settings->{$post_type}->include ) {
				unset( $post_types[ $key ] );
			}
		}

		foreach ( $this->get_cpt() as $cpt ) {
			if ( ! empty( $settings->cpt[ $cpt ] ) && ! empty( $settings->cpt[ $cpt ]->include ) ) {
				$post_types[] = $cpt;
			}
		}

		return $post_types;
	}
}
