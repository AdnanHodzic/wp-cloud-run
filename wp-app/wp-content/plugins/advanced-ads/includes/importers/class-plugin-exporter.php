<?php
/**
 * Plugin exporter.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Importers;

use WP_Error;
use Exception;
use XML_Encoder;
use AdvancedAds\Options;
use Advanced_Ads_Privacy;
use AdvancedAds\Constants;
use Advanced_Ads_Ads_Txt_Strategy;
use AdvancedAds\Ads\Ad_Repository;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin exporter.
 *
 * phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_print_r
 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export
 */
class Plugin_Exporter {
	/**
	 * Hold data to make export file
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Types of content to be exported.
	 *
	 * @var array
	 */
	public $options = false;

	/**
	 * Download export file
	 *
	 * @return array|string|WP_Error
	 */
	public function download_file() {
		if ( ! Conditional::user_can( 'advanced_ads_manage_options' ) ) {
			return new WP_Error( 'no_permission', __( 'User dont have premission to export.', 'advanced-ads' ) );
		}

		$this->options = Params::post( 'content', false, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( empty( $this->options ) ) {
			return new WP_Error( 'no_option_selected', __( 'No content option selected to export.', 'advanced-ads' ) );
		}

		$this->process();

		if ( ! empty( $this->data ) ) {
			if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
				error_log( print_r( 'Array to decode', true ) );
				error_log( print_r( $this->data, true ) );
			}

			$filename = $this->get_filename();

			try {
				$encoded = XML_Encoder::get_instance()->encode(
					$this->data,
					[ 'encoding' => get_option( 'blog_charset' ) ]
				);

				header( 'Content-Description: File Transfer' );
				header( 'Content-Disposition: attachment; filename=' . $filename );
				header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );
				echo $encoded; // phpcs:ignore

				if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
					error_log( print_r( $encoded, true ) );
					$decoded = XML_Encoder::get_instance()->decode( $encoded );
					error_log( 'result ' . var_export( $this->data === $decoded, true ) );
				}

				exit();

			} catch ( Exception $e ) {
				return new WP_Error( 'error', $e->getMessage() );
			}
		}

		return false;
	}

	/**
	 * Generate XML file
	 *
	 * @return void
	 */
	private function process(): void {
		@set_time_limit( 0 );
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) ); // phpcs:ignore WordPress.PHP.IniSet.memory_limit_Disallowed

		foreach ( $this->options as $option ) {
			$method = "process_{$option}";
			if ( method_exists( $this, $method ) ) {
				$this->$method();
			}
		}

		do_action_ref_array( 'advanced-ads-export', [ $this->options, &$this->data ] );
	}

	/**
	 * Process ads
	 *
	 * @return void
	 */
	private function process_ads(): void {
		$ads        = [];
		$mime_types = $this->get_mime_types();
		$search     = '/' . preg_quote( home_url(), '/' ) . '(\S+?)\.(' . implode( '|', array_keys( $mime_types ) ) . ')/i';

		$posts = $this->get_posts( Constants::POST_TYPE_AD );
		foreach ( $posts as $index => $post ) {
			if ( ! empty( $post['post_content'] ) ) {
				// Wrap images in <advads_import_img></advads_import_img> tags.
				$post['post_content'] = preg_replace( $search, '<advads_import_img>\\0</advads_import_img>', $post['post_content'] );
			}

			if ( in_array( 'groups', $this->options, true ) ) {
				$terms = wp_get_object_terms( $post['ID'], Constants::TAXONOMY_GROUP );

				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$post['groups'] = [];
					foreach ( $terms as $term ) {
						$post['groups'][] = $this->get_group( $term->term_id );
					}
				}
			}

			$this->get_post_meta( $post );

			$ads[] = $post;
		}

		if ( $ads ) {
			$this->data['ads'] = $ads;
		}
	}

	/**
	 * Process placements
	 *
	 * @return void
	 */
	private function process_placements(): void {
		$placements = [];
		$posts      = $this->get_posts( Constants::POST_TYPE_PLACEMENT );
		foreach ( $posts as $index => $post ) {
			$this->get_post_meta( $post );

			$placements[] = $post;
		}

		if ( $placements ) {
			$this->data['placements'] = $placements;
		}
	}

	/**
	 * Process groups
	 *
	 * @return void
	 */
	private function process_groups(): void {
		$this->data['groups'] = [];
		foreach ( wp_advads_get_groups_dropdown() as $term_id => $name ) {
			$this->data['groups'][] = $this->get_group( $term_id );
		}
	}

	/**
	 * Process options
	 *
	 * @return void
	 */
	private function process_options(): void {
		/**
		 * Filters the list of options to be exported.
		 *
		 * @param $options An array of options
		 */
		$this->data['options'] = array_filter(
			apply_filters(
				'advanced-ads-export-options',
				[
					ADVADS_SLUG                           => get_option( ADVADS_SLUG ),
					GADSENSE_OPT_NAME                     => get_option( GADSENSE_OPT_NAME ),
					Advanced_Ads_Privacy::OPTION_KEY      => get_option( Advanced_Ads_Privacy::OPTION_KEY ),
					Advanced_Ads_Ads_Txt_Strategy::OPTION => get_option( Advanced_Ads_Ads_Txt_Strategy::OPTION ),
					Constants::OPTION_ADBLOCKER_SETTINGS  => Options::instance()->get( 'adblocker', [] ),
				]
			)
		);
	}

	/**
	 * Get the filename
	 *
	 * @return string
	 */
	private function get_filename(): string {
		return sprintf(
			'%s-advanced-ads-export-%s.xml',
			sanitize_title(
				preg_replace(
					'#^(?:[^:]+:)?//(?:www\.)?([^/]+)#',
					'$1',
					get_bloginfo( 'url' )
				)
			),
			gmdate( 'Y-m-d' )
		);
	}

	/**
	 * Get group info
	 *
	 * @param int $group_id Group id.
	 *
	 * @return array
	 */
	private function get_group( $group_id ): array {
		static $advads_groups;
		if ( null === $advads_groups ) {
			$advads_groups = [];
		}

		if ( ! isset( $advads_groups[ $group_id ] ) ) {
			$group = wp_advads_get_group( $group_id );

			$advads_groups[ $group_id ] = [
				'term_id'  => $group->get_id(),
				'slug'     => $group->get_slug(),
				'name'     => $group->get_name(),
				'type'     => $group->get_type(),
				'ad_count' => $group->get_ad_count(),
				'options'  => $group->get_options(),
				'weight'   => $group->get_ad_weights(),
			];
		}

		return $advads_groups[ $group_id ];
	}

	/**
	 * Get posts for export
	 *
	 * @param string $post_type Post type to fetch.
	 *
	 * @return array
	 */
	private function get_posts( $post_type ): array {
		global $wpdb;

		$export_fields = implode(
			', ',
			[
				'ID',
				'post_date',
				'post_date_gmt',
				'post_content',
				'post_title',
				'post_password',
				'post_name',
				'post_status',
				'post_modified',
				'post_modified_gmt',
				'guid',
			]
		);

		// phpcs:disable
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT $export_fields FROM {$wpdb->posts} where post_type = '%s' and post_status not in ('trash', 'auto-draft')",
				$post_type
			),
			ARRAY_A
		);
		// phpcs:enable
	}

	/**
	 * Get mime types
	 *
	 * @return array
	 */
	private function get_mime_types(): array {
		static $mime_types;

		if ( null === $mime_types ) {
			$mime_types = array_filter(
				get_allowed_mime_types(),
				function ( $mime_type ) {
					return preg_match( '/image\//', $mime_type );
				}
			);
		}

		return $mime_types;
	}

	/**
	 * Get ads meta
	 *
	 * @param array $post Post object array.
	 *
	 * @return void
	 */
	private function get_post_meta( &$post ) {
		global $wpdb;

		$postmeta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE post_id = %d", absint( $post['ID'] ) ) ); // phpcs:ignore
		foreach ( $postmeta as $meta ) {
			if ( '_edit_lock' === $meta->meta_key ) {
				continue;
			}

			if ( Ad_Repository::OPTION_METAKEY === $meta->meta_key ) {
				$image_id   = false;
				$ad_options = maybe_unserialize( $meta->meta_value );
				if ( isset( $ad_options['output']['image_id'] ) ) {
					$image_id = absint( $ad_options['output']['image_id'] );
				}
				if ( isset( $ad_options['image_id'] ) ) {
					$image_id = absint( $ad_options['image_id'] );
				}

				if ( $image_id ) {
					$atached_img = wp_get_attachment_url( $image_id );
					if ( $atached_img ) {
						$post['attached_img_url'] = $atached_img;
					}
				}

				$post['meta_input'][ $meta->meta_key ] = maybe_unserialize( $ad_options );
			} else {
				$post['meta_input'][ $meta->meta_key ] = maybe_unserialize( $meta->meta_value );
			}
		}
	}
}
