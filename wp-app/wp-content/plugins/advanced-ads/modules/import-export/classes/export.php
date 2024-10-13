<?php

use AdvancedAds\Entities;
use AdvancedAds\Utilities\WordPress;

/**
 * Export functionality.
 */
class Advanced_Ads_Export {
    /**
     * @var Advanced_Ads_Export
     */
    private static $instance;

    /**
     * Status messages
     */
    private $messages = [];

	private function __construct() {

		$page_hook = 'admin_page_advanced-ads-import-export';
		// execute before headers are sent
		add_action( 'load-' . $page_hook, [ $this, 'download_export_file' ] );
	}

	/**
	 * Return an instance of this class.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Handle form submissions
	 */
	public function download_export_file() {
		$action = WordPress::current_action();

		if ( $action === 'export' ) {
			if ( ! WordPress::user_can( 'advanced_ads_manage_options') ) {
				return;
			}

			check_admin_referer( 'advads-export' );

			if ( isset( $_POST['content'] ) ) {
				$this->process( $_POST['content'] );
			}
		}
	}

	/**
	 * Generate XML file.
	 *
	 * @param array $content Types of content to be exported.
	 */
	private function process( array $content ) {
		global $wpdb;

		@set_time_limit( 0 );
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );

		$export = [];
		$advads_ad_groups = get_option( 'advads-ad-groups', [] );

		if ( in_array( 'ads', $content ) ) {
			$advads_ad_weights =  get_option( 'advads-ad-weights', [] );

			$ads = [];
			$export_fields = implode( ', ', [
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
				'guid'
			] );

			$posts = $wpdb->get_results( $wpdb->prepare( "SELECT $export_fields FROM {$wpdb->posts} where post_type = '%s' and post_status not in ('trash', 'auto-draft')", Entities::POST_TYPE_AD ), ARRAY_A );

			$mime_types = array_filter( get_allowed_mime_types(), function( $mime_type ) {
				return preg_match( '/image\//', $mime_type );
			} );
			$search     = '/' . preg_quote( home_url(), '/' ) . '(\S+?)\.(' . implode( '|', array_keys( $mime_types ) ) . ')/i';
			foreach ( $posts as $k => $post ) {
				if ( ! empty( $post['post_content'] ) ) {
					// wrap images in <advads_import_img></advads_import_img> tags
					$post['post_content']  = preg_replace( $search, '<advads_import_img>\\0</advads_import_img>', $post['post_content']  );
				}

			    $ads[$k] = $post;

			    if ( in_array( 'groups', $content ) ) {
				    $terms = wp_get_object_terms( $post['ID'], 'advanced_ads_groups' );

					foreach ( (array) $terms as $term ) {
						$group_info = [
							'term_id' => $term->term_id,
							'slug' => $term->slug,
							'name' => $term->name,
						];

						if ( isset( $advads_ad_groups[ $term->term_id ] ) ) {
							$group_info += $advads_ad_groups[ $term->term_id ];
						}

						if ( isset( $advads_ad_weights[ $term->term_id ][ $post['ID'] ] ) ) {
							$group_info['weight'] = $advads_ad_weights[ $term->term_id ][ $post['ID'] ];
						}

						$ads[ $k ]['groups'][] = $group_info;
					}
			    }

			    $postmeta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE post_id = %d", absint( $post['ID'] ) ) );

				foreach ( $postmeta as $meta ) {
					if ( $meta->meta_key === '_edit_lock' ) {
						continue;
					}
					if ( $meta->meta_key === Advanced_Ads_Ad::$options_meta_field ) {
						$ad_options = maybe_unserialize( $meta->meta_value );
						if ( isset( $ad_options['output']['image_id'] ) ) {
							$image_id = absint( $ad_options['output']['image_id'] );
							if ( $atached_img = wp_get_attachment_url( $image_id) ) {
								$ads[ $k ]['attached_img_url'] = $atached_img;
							}
						}
						$ads[ $k ]['meta_input'][ $meta->meta_key ] = $ad_options;
					} else {
						$ads[ $k ]['meta_input'] [$meta->meta_key ] = $meta->meta_value;
			        }
			    }
			}

			if ( $ads ) {
				$export['ads'] = $ads;
			}
		}

	    if ( in_array( 'groups', $content ) ) {
			$terms = Advanced_Ads::get_instance()->get_model()->get_ad_groups();
			foreach ( $terms as $term ) {
				$group_info = [
					'term_id' => $term->term_id,
					'slug' => $term->slug,
					'name' => $term->name,
				];

				if ( isset( $advads_ad_groups[ $term->term_id ] ) ) {
					$group_info += $advads_ad_groups[ $term->term_id ];
				}

				$export['groups'][] = $group_info;
			}
	    }

		if ( in_array( 'placements', $content ) ) {
			$placements = Advanced_Ads::get_instance()->get_model()->get_ad_placements_array();

			// prevent nodes starting with number
			foreach ( $placements as $key => &$placement ) {
				$placement['key'] = $key;
			}

			$export['placements'] = array_values( $placements );
		}

		if ( in_array( 'options', $content, true ) ) {
			/**
			 * Filters the list of options to be exported.
			 *
			 * @param $options An array of options
			 */
			$export['options'] = array_filter( apply_filters( 'advanced-ads-export-options', [
				ADVADS_SLUG                           => get_option( ADVADS_SLUG ),
				GADSENSE_OPT_NAME                     => get_option( GADSENSE_OPT_NAME ),
				Advanced_Ads_Privacy::OPTION_KEY      => get_option( Advanced_Ads_Privacy::OPTION_KEY ),
				Advanced_Ads_Ads_Txt_Strategy::OPTION => get_option( Advanced_Ads_Ads_Txt_Strategy::OPTION ),
			] ) );
		}

		do_action_ref_array( 'advanced-ads-export', [ $content, &$export ] );

		if ( $export ) {
			if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
				error_log( print_r( 'Array to decode', true ) );
				error_log( print_r( $export, true) );
			}

			// add the root domain and the current date to the filename.
			$filename = sprintf(
				'%s-advanced-ads-export-%s.xml',
				sanitize_title( preg_replace(
					'#^(?:[^:]+:)?//(?:www\.)?([^/]+)#',
					'$1',
					get_bloginfo( 'url' )
				) ),
				gmdate( 'Y-m-d' )
			);

			try {
				$encoded = Advanced_Ads_XmlEncoder::get_instance()->encode( $export, [ 'encoding' => get_option( 'blog_charset' ) ] );

				header( 'Content-Description: File Transfer' );
				header( 'Content-Disposition: attachment; filename=' . $filename );
				header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );
				echo $encoded;

				if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
					error_log( print_r( $encoded, true ) );
					$decoded = Advanced_Ads_XmlEncoder::get_instance()->decode( $encoded );
					error_log( 'result ' . var_export( $export === $decoded , true ) );
				}

				exit();

			} catch ( Exception $e ) {
				$this->messages[] = [ 'error', $e->getMessage() ];
			}
		}
	}

	public function get_messages(){
		return $this->messages;
	}
}
