<?php // phpcs:ignoreFile
/**
 * XML Importer.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Importers;

use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Ads\Ad_Repository;
use AdvancedAds\Constants;
use AdvancedAds\Framework\Utilities\Arr;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Interfaces\Importer as Interface_Importer;
use AdvancedAds\Utilities\WordPress;
use Exception;
use XML_Encoder;

defined( 'ABSPATH' ) || exit;

/**
 * XML Importer.
 * TODO: Refactor logic.
 */
class XML_Importer extends Importer implements Interface_Importer {
	/**
	 * Uploaded XML file path
	 *
	 * @var string
	 */
	private $import_id;

	/**
	 * Status messages
	 *
	 * @var array
	 */
	private $messages = [];

	/**
	 * Imported data mapped with previous data, e.g. ['ads'][ new_ad_id => old_ad_id (or null if does not exist) ]
	 *
	 * @var array
	 */
	public $imported_data = [
		'ads'        => [],
		'groups'     => [],
		'placements' => [],
	];

	/**
	 * Attachments, created for Image Ads and images in ad content
	 *
	 * @var array
	 */
	private $created_attachments = [];

	/**
	 * Post data indexs to set before inserting into the database
	 *
	 * @var array
	 */
	private $post_data_index = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'advanced-ads-cleanup-import-file', [ $this, 'delete_old_import_file' ] );
		add_filter( 'advanced-ads-new-ad-data', [ $this, 'ad_before_inserting' ], 10, 2 );
		remove_action( 'advanced-ads-ad-pre-save', [ \Advanced_Ads_AdSense_Admin::get_instance(), 'save_ad_options' ] );
	}

	/**
	 * Get the unique identifier (ID) of the importer.
	 *
	 * @return string The unique ID of the importer.
	 */
	public function get_id(): string {
		return 'xml';
	}

	/**
	 * Get the title or name of the importer.
	 *
	 * @return string The title of the importer.
	 */
	public function get_title(): string {
		return __( 'XML', 'advanced-ads' );
	}

	/**
	 * Get a description of the importer.
	 *
	 * @return string The description of the importer.
	 */
	public function get_description(): string {
		return '';
	}

	/**
	 * Get the icon to this importer.
	 *
	 * @return string The icon for the importer.
	 */
	public function get_icon(): string {
		return '<span class="dashicons dashicons-insert"></span>';
	}

	/**
	 * Detect the importer in database.
	 *
	 * @return bool True if detected; otherwise, false.
	 */
	public function detect(): bool {
		return false;
	}

	/**
	 * Render form.
	 *
	 * @return void
	 */
	public function render_form(): void {
	}

	/**
	 * Import data.
	 *
	 * @return array|string
	 */
	public function import() {
		switch ( Params::post( 'import_type' ) ) {
			case 'xml_content':
				if ( '' === Params::post( 'xml_textarea', '' ) ) {
					$this->messages[] = [ 'error', __( 'Please enter XML content', 'advanced-ads' ) ];
					break;
				}
				$content = stripslashes( Params::post( 'xml_textarea' ) );
				$this->import_content( $content );
				break;
			case 'xml_file':
				if ( $this->handle_upload() ) {
					$content = file_get_contents( $this->import_id );
					$this->import_content( $content );
					@unlink( $this->import_id );
				}
				break;
			default:
				return [ 'error', __( 'Please select import type', 'advanced-ads' ) ];
		}

		if ( $this->created_attachments ) {
			/* translators: %s number of attachments */
			$this->messages[] = [ 'success', sprintf( _n( '%s attachment uploaded', '%s attachments uploaded', count( $this->created_attachments ), 'advanced-ads' ), count( $this->created_attachments) ) ];
		}

		return $this->messages;
	}

	/**
	 * The main controller for the actual import stage
	 *
	 * @param string $xml_content XML content to import.
	 */
	public function import_content( &$xml_content ) {
		@set_time_limit( 0 );
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );

		$xml_content = trim( $xml_content );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'source XML:' );
			error_log( $xml_content );
		}

		try {
			$decoded = XML_Encoder::get_instance()->decode( $xml_content );
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );
			$this->messages[] = [ 'error', $e->getMessage() ];
			return;
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'decoded XML:' );
			error_log( print_r( $decoded, true ) );
		}

		$this->import_ads( $decoded['ads'] ?? [] );
		$this->import_groups( $decoded['groups'] ?? [], $decoded['ads'] ?? [] );
		$this->import_placements( $decoded['placements'] ?? [] );
		$this->import_options( $decoded );

		do_action_ref_array( 'advanced-ads-import', [ &$decoded, &$this->imported_data, &$this->messages ] );

		wp_cache_flush();
	}

	/**
	 * Set the ad data before inserting into the database
	 *
	 * @param array $post_data Default post data.
	 * @param Ad    $ad        Ad object.
	 *
	 * @return array
	 */
	public function ad_before_inserting( $post_data, $ad ) {
		foreach ( $this->post_data_index as $index ) {
			if ( ! empty( $ad->get_prop( $index ) ) ) {
				$post_data[ $index ] = $ad->get_prop( $index );
			}
		}

		return $post_data;
	}

	/**
	 * Delete old import file via cron
	 *
	 * @param string $path Path to the file.
	 *
	 * @return void
	 */
	public function delete_old_import_file( $path ) {
		if ( file_exists( $path ) ) {
			@unlink( $path );
		}
	}

	/**
	 * Handles the XML upload
	 *
	 * @return bool false if error, true otherwise
	 */
	private function handle_upload() {
		$uploads_dir = wp_upload_dir();
		if ( ! empty( $uploads_dir['error'] ) ) {
			$this->messages[] = [ 'error', $uploads_dir['error'] ];
			return false;
		}

		$import_dir      = $uploads_dir['basedir'] . '/advads-import';
		$this->import_id = $import_dir . '/' . md5( time() . NONCE_SALT );

		if ( ! is_dir( $import_dir ) && ! wp_mkdir_p( $import_dir ) ) {
			/* translators: %s import directory */
			$this->messages[] = [ 'error', sprintf( __( 'Failed to create import directory <em>%s</em>', 'advanced-ads' ), $import_dir ) ];
			return false;
		}

		if ( ! is_writable( $import_dir ) ) {
			/* translators: %s import directory */
			$this->messages[] = [ 'error', sprintf( __( 'Import directory is not writable: <em>%s</em>', 'advanced-ads' ), $import_dir ) ];
			return false;
		}

		if ( ! @file_exists( $import_dir . '/index.php' ) ) {
			@touch( $import_dir . '/index.php' );
		}

		if ( ! isset( $_FILES['import'] ) ) {
			$this->messages[] = [ 'error', __( 'File is empty, uploads are disabled or post_max_size is smaller than upload_max_filesize in php.ini', 'advanced-ads' ) ];
			return false;
		}

		$file = $_FILES['import'];

		// determine if uploaded file exceeds space quota.
		$file = apply_filters( 'wp_handle_upload_prefilter', $file );

		if ( ! empty( $file['error'] ) ) {
			/* translators: %s error in file */
			$this->messages[] = [ 'error', sprintf( __( 'Failed to upload file, error: <em>%s</em>', 'advanced-ads' ), $file['error'] ) ];
			return false;
		}

		if ( ! ( $file['size'] > 0 ) ) {
			$this->messages[] = [ 'error', __( 'File is empty.', 'advanced-ads' ), $file['error'] ];
			return false;
		}

		if ( ! is_uploaded_file( $file['tmp_name'] ) || ! @ move_uploaded_file( $file['tmp_name'], $this->import_id ) || ! is_readable( $this->import_id  ) ) {
			/* translators: %s import id */
			$this->messages[] = [ 'error', sprintf( __( 'The file could not be created: <em>%s</em>. This is probably a permissions problem', 'advanced-ads' ), $this->import_id ) ];
			return false;
		}

		// Set correct file permissions.
		$stat  = stat( dirname( $import_dir ) );
		$perms = $stat['mode'] & 0000666;
		@ chmod( $this->import_id, $perms );

		// cleanup in case of failed import.
		wp_schedule_single_event( time() + 10 * MINUTE_IN_SECONDS, 'advanced-ads-cleanup-import-file', [ $this->import_id ] );

		return true;
	}

	/**
	 * Create new ads and groups based on import information
	 *
	 * @param array $decoded decoded XML.
	 */
	private function import_ads( $decoded ) {
		// Early bail!!
		if ( empty( $decoded ) ) {
			return;
		}

		$count_attachment = 0;
		$count_ads        = 0;

		foreach ( $decoded as $ad ) {
			if ( isset( $ad['meta_input'] ) && is_array( $ad['meta_input'] ) ) {
				foreach ( $ad['meta_input'] as $meta_k => &$meta_v ) {
					if ( Ad_Repository::OPTION_METAKEY !== $meta_k ) {
						$meta_v = WordPress::maybe_unserialize( $meta_v );
					}
				}
			}

			// upload images for Image ad type.
			if (
				isset( $ad['attached_img_url'] )
				&& (
					isset( $ad['meta_input']['advanced_ads_ad_options']['output']['image_id'] )
					|| isset( $ad['meta_input']['advanced_ads_ad_options']['image_id'] )
				)
			) {
				$attached_img_url = $this->replace_placeholders( $ad['attached_img_url'] );
				$attachment_id    = null;

				if ( isset( $this->created_attachments[ $attached_img_url ] ) ) {
					$attachment_id = $this->created_attachments[ $attached_img_url ]['post_id'];
				} else if ( $attachment = $this->upload_image_from_url( $attached_img_url ) ) {
					$link          = ( $link = get_attachment_link( $attachment['post_id'] ) ) ? sprintf( '<a href="%s">%s</a>', esc_url( $link ), __( 'Edit', 'advanced-ads' ) ) : '';
					$attachment_id = $attachment['post_id'];
					++$count_attachment;

					$this->created_attachments[ $attached_img_url ] = $attachment;
				}

				if ( $attachment_id ) {
					$ad['meta_input']['advanced_ads_ad_options']['output']['image_id'] = $attachment_id;
				}
			}

			$ad_obj = wp_advads_create_new_ad( $ad['meta_input']['advanced_ads_ad_options']['type'] );

			if ( ! $ad_obj ) {
				/* translators: %s Ad title */
				$this->messages[] = [ 'error', sprintf( __( 'Failed to import <em>%s</em>', 'advanced-ads' ), esc_html( Arr::get( $ad, 'post_title' ) ) ) ];
				continue;
			}

			$ad_obj->set_title( Arr::get( $ad, 'post_title' ) );
			$ad_obj->set_content( $this->process_ad_content( Arr::get( $ad, 'post_content', '' ) ) );
			$ad_obj->set_status( Arr::get( $ad, 'post_status', 'publish' ) );
			$ad_obj->set_author_id( get_current_user_id() );

			// set on filter 'advanced-ads-new-ad-data'.
			$ad_obj->set_prop_temp( 'post_date', Arr::get( $ad, 'post_date' ) );
			$ad_obj->set_prop_temp( 'post_date_gmt', Arr::get( $ad, 'post_date_gmt' ) );
			$ad_obj->set_prop_temp( 'post_password', Arr::get( $ad, 'post_password', '' ) );
			$ad_obj->set_prop_temp( 'post_name', Arr::get( $ad, 'post_name', '' ) );
			$ad_obj->set_prop_temp( 'post_modified', Arr::get( $ad, 'post_modified', 0 ) );
			$ad_obj->set_prop_temp( 'post_modified_gmt', Arr::get( $ad, 'post_modified_gmt', 0 ) );
			$ad_obj->set_prop_temp( 'guid', Arr::get( $ad, 'guid', '' ) );

			$this->post_data_index = [
				'post_date',
				'post_date_gmt',
				'post_password',
				'post_name',
				'post_modified',
				'post_modified_gmt',
				'guid',
			];

			foreach ( Arr::get( $ad, 'meta_input.advanced_ads_ad_options', [] ) as $key => $value ) {
				if ( 'output' === $key ) {
					foreach ( $value as $prop => $inner_value ) {
						$ad_obj->set_prop( $prop, $inner_value );
					}
				} elseif ( 'tracking' === $key ) {
					foreach ( Arr::get( $ad, 'meta_input.advanced_ads_ad_options.tracking' ) as $inner_key => $inner_value ) {
						$ad_obj->set_prop( "tracking.{$inner_key}", $inner_value );
					}
				} else {
					$ad_obj->set_prop_temp( $key, $value );
				}
			}

			if ( $ad_obj->is_type( 'adsense' ) ) {
				$content = json_decode( str_replace( [ "\n", "\r", ' ' ], '', wp_unslash( $ad['post_content'] ) ), true );
				if ( in_array( $content['unitType'] ?? 'none', [
					'responsive',
					'link',
					'link-responsive',
					'matched-content',
					'in-article',
					'in-feed',
				], true )
				) {
					$ad_obj->set_width( 0 );
					$ad_obj->set_height( 0 );
				}
			}

			$ad_obj->save();

			if ( Arr::get( $ad, 'meta_input.advanced_ads_selling_order' ) ) {
				update_post_meta( $ad_obj->get_id(), 'advanced_ads_selling_order', absint( $ad['meta_input']['advanced_ads_selling_order'] ) );
			}

			if ( Arr::get( $ad, 'meta_input.advanced_ads_selling_order_item' ) ) {
				update_post_meta( $ad_obj->get_id(), 'advanced_ads_selling_order_item', absint( $ad['meta_input']['advanced_ads_selling_order_item'] ) );
			}

			++$count_ads;

			// new ad id => old ad id, if exists.
			$this->imported_data['ads'][ $ad_obj->get_id() ] = isset( $ad['ID'] ) ? absint( $ad['ID'] ) : null;
		}

		if ( $count_ads ) {
			/* translators: %s number of ads */
			$this->messages[] = [ 'success', sprintf( _n( '%s ad imported', '%s ads imported', $count_ads, 'advanced-ads' ), $count_ads ) ];
		}
	}

	/**
	 * Create new empty groups based on import information
	 *
	 * @param array $decoded group related info.
	 * @param array $ads     ads related info.
	 *
	 * @return void
	 */
	private function import_groups( $decoded, $ads ) {
		// Early bail!!
		if ( empty( $decoded ) ) {
			return;
		}

		foreach ( $decoded as $_group ) {
			$group = wp_advads_create_new_group( $_group['type'] ?? 'default' );
			$group->set_name( $_group['name'] ?? '' );
			$group->set_ad_count( $_group['ad_count'] ?? 1 );
			$group->set_options( $_group['options'] ?? [] );

			$ad_weights = [];

			if ( isset( $_group['weight'] ) ) {
				foreach ( $_group['weight'] ?? [] as $old_ad_id => $weight ) {
					$ad_id = array_search( $old_ad_id, $this->imported_data['ads'] );
					if ( $ad_id ) {
						$ad_weights[ $ad_id ] = $weight ?? Constants::GROUP_AD_DEFAULT_WEIGHT;
					}
				}
			} else {
				foreach ( $ads as $ad ) {
					if ( ! isset( $ad['groups'] ) ) {
						continue;
					}
					foreach ( $ad['groups'] as $group_of_current_ad ) {
						if ( $group_of_current_ad['term_id'] !== $_group['term_id'] ) {
							continue;
						}
						$ad_id      = $this->search_item( $ad['ID'], 'ad' );
						if ( $ad_id ) {
							$ad_weights[ $ad_id ] = $group_of_current_ad['weight'] ?? Constants::GROUP_AD_DEFAULT_WEIGHT;
						}
					}
				}
			}

			$group->set_ad_weights( $ad_weights );
			$group->save();

			$this->imported_data['groups'][ $group->get_id() ] = $_group['term_id'] ?? null;
		}

		if ( count( $this->imported_data['groups'] ) ) {
			/* translators: %s number of groups */
			$this->messages[] = [ 'success', sprintf( _n( '%s group imported', '%s groups imported', count( $this->imported_data['groups'] ), 'advanced-ads' ), count( $this->imported_data['groups'] ) ) ];
		}
	}

	/**
	 * Create new placements based on import information
	 *
	 * @param array $decoded decoded XML.
	 */
	private function import_placements( $decoded ) {
		// Early bail!!
		if ( empty( $decoded ) ) {
			return;
		}

		$existing_placements = wp_advads_get_placements();
		$updated_placements  = $existing_placements;

		foreach ( $decoded as &$placement ) {
			$use_existing = ! empty( $placement['use_existing'] );

			if ( $use_existing ) {
				if ( empty( $placement['key'] ) ) {
					continue;
				}

				$placement_key_uniq = sanitize_title( $placement['key'] );
				if ( ! isset( $existing_placements[ $placement_key_uniq ] ) ) {
					continue;
				}

				$existing_placement        = $existing_placements[ $placement_key_uniq ];
				$existing_placement['key'] = $placement_key_uniq;
			} else {
				$placement_key_uniq = $placement['ID'] ?? $placement['key'];
				$placement_type     = Arr::get( $placement, 'meta_input.type', $placement['type'] ?? '' );
				$placement['type']  = wp_advads_has_placement_type( $placement_type ) ? $placement_type : 'default';
				$placement['name']  = $placement['post_title'] ?? $placement['name'];
				$placement['item']  = Arr::get( $placement, 'meta_input.item', $placement['item'] ?? null ) ?? '';

				// make sure the key in placement array is unique.
				if ( isset( $existing_placements[ $placement_key_uniq ] ) ) {
					$count = 1;
					while ( isset( $existing_placements[ $placement_key_uniq . '_' . $count ] ) ) {
						++$count;
					}
					$placement_key_uniq .= '_' . $count;
				}

				// new placement key => old placement key.
				$this->imported_data['placements'][ $placement_key_uniq ] = $placement['ID'] ?? null;
			}

			// try to set "Item" (ad or group).
			if ( ! empty( $placement['item'] ) ) {
				$_item = explode( '_', $placement['item'] );
				if ( ! empty( $_item[1] ) ) {
					switch ( $_item[0] ) {
						case 'ad':
						case Constants::ENTITY_AD:
							$found = $this->search_item( $_item[1], Constants::ENTITY_AD );
							if ( false === $found ) {
								break;
							}

							if ( $use_existing ) {
								// assign new ad to an existing placement
								// - if the placement has no or a single ad assigned, it will be swapped against the new one
								// - if a group is assigned to the placement, the new ad will be added to this group with a weight of 1.
								$placement = $existing_placement;

								if ( ! empty( $placement['item'] ) ) {
									// get the item from the existing placement.
									$_item_existing = explode( '_', $placement['item'] );

									if ( ! empty( $_item_existing[1] ) && Constants::ENTITY_GROUP === $_item_existing[0] ) {
										$advads_ad_weights = get_option( 'advads-ad-weights', [] );

										if ( term_exists( absint( $_item_existing[1] ), Constants::TAXONOMY_GROUP ) ) {
											wp_set_post_terms( $found, $_item_existing[1], Constants::TAXONOMY_GROUP, true );

											/**
											 * By default, a new add added to a group receives the weight of 5
											 * so that users could set the weight of existing ads either higher or lower
											 * depending on whether they want to show the new ad with a higher weight or not.
											 * This is especially useful with Selling Ads to replace an existing ad in a group
											 * with a newly sold one
											 *
											 * Advanced users could use the `advanced-ads-import-default-group-weight` filter
											 * to manipulate the value
											 */
											$advads_ad_weights[ $_item_existing[1] ][ $found ] = apply_filters( 'advanced-ads-import-default-group-weight', 5 );
											update_option( 'advads-ad-weights', $advads_ad_weights );
											// new placement key => old placement key.
											$this->imported_data['placements'][ $placement_key_uniq ] = $placement_key_uniq;
											break;
										}
									}
								}
							}

							$placement['item'] = 'ad_' . $found;
							// new placement key => old placement key.
							$this->imported_data['placements'][ $placement_key_uniq ] = $placement_key_uniq;
							break;
						case Constants::ENTITY_GROUP:
							$found = $this->search_item( $_item[1], Constants::ENTITY_GROUP );
							if ( false === $found ) {
								break;
							}

							$placement['item'] = 'group_' . $found;
							// new placement key => old placement key.
							$this->imported_data['placements'][ $placement_key_uniq ] = $placement_key_uniq;
							break;
					}
				}
			}

			if ( ! isset( $placement['options'] ) ) {
				$placement['options'] = $placement['meta_input']['options'] ?? [];
			}

			$updated_placements[ $placement_key_uniq ] = apply_filters( 'advanced-ads-import-placement', $placement, $this );
		}

		if ( $existing_placements !== $updated_placements ) {
			$count_placements = 0;

			foreach ( $updated_placements as $placement_key => $placement_data ) {
				if ( isset( $existing_placements[ $placement_key ] ) ) {
					continue;
				}

				$new_placement = wp_advads_create_new_placement( $placement_data['type'] );
				$new_placement->set_title( $placement_data['name'] );
				$new_placement->set_item( $placement_data['item'] ?? '' );

				foreach ( $placement_data['options'] as $key => $option ) {
					if ( 'placement_conditions' === $key ) {
						foreach ( $placement_data['options']['placement_conditions'] as $prop => $value ) {
							$new_placement->set_prop( $prop, $value );
						}
					} else {
						$new_placement->set_prop( $key, $option );
					}
				}

				$new_placement->save();
				++$count_placements;

				$this->imported_data['placements'][ $placement_key ] = $new_placement->get_id();
			}

			if ( $count_placements ) {
				/* translators: %s number of placements */
				$this->messages[] = [ 'success', sprintf( _n( '%s placement imported', '%s placements imported', $count_placements, 'advanced-ads' ), $count_placements ) ];
			}
		}
	}

	/**
	 * Search for ad/group id
	 *
	 * @param string $id ad/group Group id.
	 * @param string $type        Group type.
	 * @return int|bool
	 * - int id of the imported ad/group if exists
	 * - or int id of the existing ad/group if exists
	 * - or bool false
	 */
	public function search_item( $id, $type ) {
		$found = false;

		switch ( $type ) {
			case 'ad':
			case Constants::ENTITY_AD:
				// if the ad was imported.
				$found = array_search( $id, $this->imported_data['ads'] );
				if ( ! $found ) {
					// if the ad already exists.
					if ( get_post_type( $id ) === Constants::POST_TYPE_AD ) {
						$found = $id;
					}
				}
				break;
			case Constants::ENTITY_GROUP:
				$found = array_search( $id, $this->imported_data['groups'] );
				if ( ! $found ) {
					if ( term_exists( absint( $id ), Constants::TAXONOMY_GROUP ) ) {
						$found = $id;
					}
				}
				break;
		}

		return (int) $found;
	}

	/**
	 * Create new options based on import information.
	 *
	 * @param array $decoded decoded XML.
	 */
	private function import_options( $decoded ) {
		if ( isset( $decoded['options'] ) && is_array( $decoded['options'] ) ) {
			$count_options = 0;

			foreach ( $decoded['options'] as $option_name => $imported_option ) {
				// Ignore options not belonging to advanced ads.
				if (
					0 !== strpos( $option_name, 'advads-' )
					&& 0 !== strpos( $option_name, 'advads_' )
					&& 0 !== strpos( $option_name, 'advanced-ads' )
					&& 0 !== strpos( $option_name, 'advanced_ads' )
				) {
					continue;
				}

				$existing_option = get_option( $option_name, [] );

				if ( ! is_array( $imported_option ) ) {
					$imported_option = [];
				}
				if ( ! is_array( $existing_option ) ) {
					$existing_option = [];
				}

				$option_to_import = array_merge( $existing_option, $imported_option );

				$count_options++;
				update_option( $option_name, WordPress::maybe_unserialize( $option_to_import ) );
			}

			if ( $count_options ) {
				/* translators: %s number of options */
				$this->messages[] = [ 'success', sprintf( _n( '%s option imported', '%s options imported', $count_options, 'advanced-ads' ), $count_options ) ];
			}
		}
	}

	/**
	 * Replace placeholders
	 *
	 * @param string $content The content.
	 *
	 * @return string with replaced placeholders
	 */
	private function replace_placeholders( $content ) {
		$content = str_replace( '{ADVADS_BASE_URL}', ADVADS_BASE_URL, $content );
		return $content;
	}

	/**
	 * Upload image from URL and create attachment
	 *
	 * @param string $image_url Image url.
	 * @return array with indices: post_id, attachment_url, false on failure
	 */
	private function upload_image_from_url( $image_url ) {
		$file_name   = basename( current( explode( '?', $image_url ) ) );
		$wp_filetype = wp_check_filetype( $file_name, null );
		$parsed_url  = @parse_url( $image_url );
		$image_url   = str_replace( ' ', '%20', $image_url );

		if ( ! $wp_filetype['type'] ) {
			/* translators: %s image url */
			$this->messages[] = [ 'error', sprintf( __( 'Invalid filetype <em>%s</em>', 'advanced-ads' ), $image_url ) ];
			return false;
		}

		if ( ! $parsed_url || ! is_array( $parsed_url ) ) {
			/* translators: %s image url */
			$this->messages[] = [ 'error', sprintf( __( 'Error getting remote image <em>%s</em>', 'advanced-ads' ), $image_url ) ];
			return false;
		}

		$response = wp_safe_remote_get( $image_url, [ 'timeout' => 20 ] );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			/* translators: %s image url */
			$this->messages[] = [ 'error', sprintf( __( 'Error getting remote image <em>%s</em>', 'advanced-ads' ), $image_url ) ];
			return false;
		}

		// Upload the file.
		$upload = wp_upload_bits( $file_name, '', wp_remote_retrieve_body( $response ) );

		if ( $upload['error'] ) {
			/* translators: %s image url */
			$this->messages[] = [ 'error', sprintf( __( 'Error getting remote image <em>%s</em>', 'advanced-ads' ), $image_url ) ];
			return false;
		}

		// Get filesize.
		$filesize = filesize( $upload['file'] );

		if ( 0 == $filesize ) {
			@unlink( $upload['file'] );
			/* translators: %s image url */
			$this->messages[] = [  'error', sprintf( __( 'Zero size file downloaded <em>%s</em>', 'advanced-ads' ), $image_url ) ];
			return false;
		}

		/**
		 * Get allowed image mime types.
		 *
		 * @var string Single mime type.
		 */
		$allowed_mime_types = get_allowed_mime_types() ?? [];
		$mime_types         = array_filter( get_allowed_mime_types(), function( $mime_type ) {
			return preg_match( '/image\//', $mime_type );
		} );
		$fileinfo = @getimagesize( $upload['file'] );

		if ( ! $fileinfo || ! in_array( $fileinfo['mime'], $mime_types, true ) ) {
			@unlink( $upload['file'] );
			/* translators: %s image url */
			$this->messages[] = [ 'error', sprintf( __( 'Error getting remote image <em>%s</em>', 'advanced-ads' ), $image_url ) ];

			return false;
		}

		// create new post.
		$new_post = [
			'post_title' => $file_name,
			'post_mime_type' => $wp_filetype['type'],
			'guid' => $upload['url'],
		];

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$post_id = wp_insert_attachment( $new_post, $upload['file'] );
		wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

		return [
			'post_id'        => $post_id,
			'attachment_url' => wp_get_attachment_url( $post_id ),
		];
	}

	/**
	 * Ad content manipulations
	 *
	 * @param string $content Content.
	 *
	 * @return string $content
	 */
	private function process_ad_content( $content ) {
		$replacement_map = [];

		if ( preg_match_all( '/\<advads_import_img\>(\S+?)\<\/advads_import_img\>/i', $content, $matches ) ) {
			foreach ( $matches[1] as $k => $url ) {
				if ( isset( $this->created_attachments[ $url ] ) ) {
					$replacement_map[ $url ] = $this->created_attachments[ $url ]['attachment_url'];
				} else if ( $attachment = $this->upload_image_from_url( $url ) ) {
					$link = ( $link = get_attachment_link( $attachment['post_id'] ) ) ? sprintf( '<a href="%s">%s</a>', esc_url( $link ), __( 'Edit', 'advanced-ads' ) ) : '';
					/* translators: 1: Attachment ID 2: Attachment link */
					$this->messages[] = [ 'success', sprintf( __( 'New attachment created <em>%1$s</em> %2$s', 'advanced-ads' ), $attachment['post_id'], $link ) ];
					$this->created_attachments[ $url ] = $attachment;
					$replacement_map[ $url ] = $attachment['attachment_url'];
				}
			}
		}

		$content = str_replace( [ '<advads_import_img>', '</advads_import_img>' ], '', $content );

		if ( count( $replacement_map ) ) {
			$content = str_replace( array_keys( $replacement_map ), array_values( $replacement_map ), $content );
		}

		return $this->replace_placeholders( $content );
	}
}
