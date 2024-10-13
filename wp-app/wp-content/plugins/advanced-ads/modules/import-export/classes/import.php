<?php // phpcs:ignoreFile

use AdvancedAds\Entities;
use AdvancedAds\Utilities\WordPress;

/**
 * Class Advanced_Ads_Import
 */
class Advanced_Ads_Import {
	/**
	 * Class instance.
	 *
	 * @var Advanced_Ads_Export
	 */
	private static $instance;

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
	 * Created groups during this import session ['slug' => 'id']
	 *
	 * @var array
	 */
	private $created_groups = [];

	/**
	 * Attachments, created for Image Ads and images in ad content
	 *
	 * @var array
	 */
	private $created_attachments = [];

	private function __construct() {}

	/**
	 * Return an instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Manages stages of the XML import process
	 */
	public function dispatch() {
		if ( ! isset( $_POST['_wpnonce'] ) ||
			! wp_verify_nonce( $_POST['_wpnonce'], 'advads-import' ) ||
			! WordPress::user_can( 'advanced_ads_manage_options' )
		) {
			return;
		}

		if ( ! isset( $_POST['import_type'] ) ) {
			return;
		}

		switch ( $_POST['import_type'] ) {
			case 'xml_content':
				if ( empty( $_POST['xml_textarea'] ) ) {
					$this->messages[] = [ 'error', __( 'Please enter XML content', 'advanced-ads' ) ];
					return;
				}
				$content = stripslashes( $_POST['xml_textarea'] );
				$this->import( $content );
				break;
			case 'xml_file':
				if ( $this->handle_upload() ) {
					$content = file_get_contents( $this->import_id );
					$this->import( $content );
					@unlink( $this->import_id );
				}
				break;
		}
	}

	/**
	 * The main controller for the actual import stage
	 *
	 * @param string $xml_content XML content to import.
	 */
	public function import( &$xml_content ) {
		@set_time_limit( 0 );
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );

		$xml_content = trim( $xml_content );

		if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
			error_log( 'source XML:' );
			error_log( $xml_content );
		}

		try {
			$decoded = Advanced_Ads_XmlEncoder::get_instance()->decode( $xml_content );
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );
			$this->messages[] = [ 'error', $e->getMessage() ];
			return;
		}

		if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
			error_log( 'decoded XML:' );
			error_log( print_r( $decoded, true ) );
		}

		$this->import_ads_and_groups( $decoded );
		$this->import_empty_groups( $decoded );
		$this->import_placements( $decoded );
		$this->import_options( $decoded );

		do_action_ref_array( 'advanced-ads-import', [ &$decoded, &$this->imported_data, &$this->messages ] );

		wp_cache_flush();
	}

	/**
	 * Create new ads and groups based on import information
	 *
	 * @param array $decoded decoded XML.
	 */
	private function import_ads_and_groups( &$decoded ) {
		if ( isset( $decoded['ads'] ) && is_array( $decoded['ads'] ) ) {
			foreach ( $decoded['ads'] as $k => $ad ) {
				$ad_title = $ad['post_title'] ?? '';
				$ad_date  = $ad['post_date'] ?? '';

				if ( isset( $ad['meta_input'] ) && is_array( $ad['meta_input'] ) ) {
					foreach ( $ad['meta_input'] as $meta_k => &$meta_v ) {
						if ( Advanced_Ads_Ad::$options_meta_field !== $meta_k ) {
							$meta_v = WordPress::maybe_unserialize( $meta_v );
						}
					}
				}

				// upload images for Image ad ad type.
				if ( isset( $ad['attached_img_url'] ) && isset( $ad['meta_input']['advanced_ads_ad_options']['output']['image_id'] ) ) {
					$attached_img_url = $this->replace_placeholders( $ad['attached_img_url'] );
					$attachment_id    = null;

					if ( isset( $this->created_attachments[ $attached_img_url ] ) ) {
						$attachment_id = $this->created_attachments[ $attached_img_url ]['post_id'];
					} else if ( $attachment = $this->upload_image_from_url( $attached_img_url ) ) {
						$link = ( $link = get_attachment_link( $attachment['post_id'] ) ) ? sprintf( '<a href="%s">%s</a>', esc_url( $link ), __( 'Edit', 'advanced-ads' ) ) : '';
						/* translators: 1: Attachment ID 2: Attachment link */
						$this->messages[] = [ 'update', sprintf( __( 'New attachment created <em>%1$s</em> %2$s', 'advanced-ads' ), $attachment['post_id'], $link ) ];
						$attachment_id    = $attachment['post_id'];

						$this->created_attachments[ $attached_img_url ] = $attachment;
					}

					if ( $attachment_id ) {
						$ad['meta_input']['advanced_ads_ad_options']['output']['image_id'] = $attachment_id;
					}
				}

				$insert_ad = [
					'post_title'        => $ad_title,
					'post_date'         => $ad_date,
					'post_date_gmt'     => isset( $ad['post_date_gmt'] ) ? $ad['post_date_gmt'] : '',
					'post_content'      => isset( $ad['post_content'] ) ? $this->process_ad_content( $ad['post_content'] ) : '',
					'post_password'     => isset( $ad['post_password'] ) ? $ad['post_password'] : '',
					'post_name'         => isset( $ad['post_name'] ) ? $ad['post_name'] : '',
					'post_status'       => isset( $ad['post_status'] ) ? $ad['post_status'] : 'publish',
					'post_modified'     => isset( $ad['post_modified'] ) ? $ad['post_modified'] : '',
					'post_modified_gmt' => isset( $ad['post_modified_gmt'] ) ? $ad['post_modified_gmt'] : '',
					'guid'              => $ad['guid'] ?? '',
					'post_author'       => get_current_user_id(),
					'post_type'         => Entities::POST_TYPE_AD,
					'comment_status'    => 'closed',
					'ping_status'       => 'closed',
					'meta_input'        => isset( $ad['meta_input'] ) ? $ad['meta_input'] : '',
				];

				$post_id = wp_insert_post( $insert_ad, true );

				if ( is_wp_error( $post_id ) ) {
					/* translators: %s Ad title */
					$this->messages[] = [ 'error', sprintf( __( 'Failed to import <em>%s</em>', 'advanced-ads' ), esc_html( $ad['post_title'] ) ) ];
					if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
						$this->messages[] = [ 'error', ' > ' . $post_id->get_error_message() ];
					}

					continue;
				} else {
					$link = get_edit_post_link( $post_id );
					$link = $link ? sprintf( '<a href="%s">%s</a>', esc_url( $link ), __( 'Edit', 'advanced-ads' ) ) : '';
					/* translators: 1: Post ID 2: Post link */
					$this->messages[] = [ 'update', sprintf( __( 'New ad created: <em>%1$s</em> %2$s', 'advanced-ads' ), $post_id, $link ) ];
				}

				// new ad id => old ad id, if exists.
				$this->imported_data['ads'][ $post_id ] = isset( $ad['ID'] ) ? absint( $ad['ID'] ) : null;

				// import ad groups.
				if ( ! empty( $ad['groups'] ) && is_array( $ad['groups'] ) ) {
					$groups_to_set     = [];
					$advads_ad_groups  = get_option( 'advads-ad-groups', [] );
					$advads_ad_weights = get_option( 'advads-ad-weights', [] );

					foreach ( $ad['groups'] as $_group ) {
						if ( ! $group_id = $this->create_group_term( $_group ) ) {
							continue;
						}

						$ad_group_id = null;
						if ( isset( $ad['meta_input']['advanced_ads_ad_options']['output']['group_id'] ) ) {
							$ad_group_id = $ad['meta_input']['advanced_ads_ad_options']['output']['group_id'];
						}

						// do not save the ad group, if this is the group assigned as ad content.
						if ( $ad_group_id !== $group_id ) {
							$groups_to_set[] = (int) $group_id;
						}

						if ( ! isset( $advads_ad_groups[ $group_id ] ) ) {
							$advads_ad_groups[ $group_id ] = [
								'type'     => $_group['type'] ?? 'default',
								'ad_count' => $_group['ad_count'] ?? 1,
								'options'  => $_group['options'] ?? [],
							];

							update_option( 'advads-ad-groups', $advads_ad_groups );
						}

						$advads_ad_weights[ $group_id ][ $post_id ] = absint( $_group['weight'] ?? Advanced_Ads_Group::MAX_AD_GROUP_DEFAULT_WEIGHT );
					}

					update_option( 'advads-ad-weights', $advads_ad_weights );

					/* translators: 1: Group IDs 2: Post ID */
					$this->messages[] = [ 'update', sprintf( __( 'Assigned terms: <em>%1$s</em>, to post: <em>%2$s</em>', 'advanced-ads' ), implode( ',',$groups_to_set ), $post_id ) ];

					$tt_ids = wp_set_post_terms( $post_id, $groups_to_set, Entities::TAXONOMY_AD_GROUP  );
				}
			}
		}
	}

	/**
	 * Create new empty groups based on import information
	 *
	 * @param array $decoded decoded XML.
	 */
	private function import_empty_groups( &$decoded ) {
		if ( isset( $decoded['groups'] ) && is_array( $decoded['groups'] ) ) {
			$advads_ad_groups = get_option( 'advads-ad-groups', [] );

			foreach ( $decoded['groups'] as $_group ) {
				if ( $group_id = $this->create_group_term( $_group ) ) {
					if ( ! isset( $advads_ad_groups[ $group_id ] ) ) {
						$advads_ad_groups[ $group_id ] = [
							'type' => isset( $_group['type']) ? $_group['type'] : 'default',
							'ad_count' => isset( $_group['ad_count'] ) ? $_group['ad_count'] : 1,
							'options' => isset( $_group['options'] ) ? $_group['options'] : []
						];
					}
				}
			}
			update_option( 'advads-ad-groups', $advads_ad_groups );
		}
	}

	/**
	 * Create new group term if it haven't already been created
	 *
	 * @param array $_group decoded XML.
	 * @return int group_id, false on failure
	 */
	private function create_group_term( $_group ) {
		if ( empty( $_group['slug'] ) || empty( $_group['name'] ) ) {
			return false;
		}

		$slug = $original_slug = $_group['slug'];

		if ( isset( $this->created_groups[ $original_slug ] ) ) {
			return $this->created_groups[ $slug ];
		}

		if ( term_exists( $slug, Entities::TAXONOMY_AD_GROUP ) ) {
			$count = 1;
			while ( term_exists( $slug . '_' . $count, Entities::TAXONOMY_AD_GROUP ) ) {
				++$count;
			}
			$slug = $slug . '_' . $count;
		}

		$t = wp_insert_term( $_group['name'], Entities::TAXONOMY_AD_GROUP, [ 'slug' => $slug] );

		if ( ! is_wp_error( $t ) ) {
			$this->created_groups[ $original_slug ] = $t['term_id'];
			$group_id                               = $t['term_id'];
			/* translators: 1: Group ID 2: Group name */
			$this->messages[] = [ 'update', sprintf( __( 'New group created, id: <em>%1$s</em>, name: <em>%2$s</em>', 'advanced-ads' ), $group_id, esc_html( $_group['name'] ) ) ];
		} else {
			/* translators: 1: Group taxonomy name 2: Group name */
			$this->messages[] = [ 'error', sprintf( __( 'Failed to import taxonomy: <em>%1$s</em>, term: <em>%2$s</em>', 'advanced-ads' ), esc_html( Entities::TAXONOMY_AD_GROUP ), esc_html( $_group['name'] ) ) ];
			if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
				$this->messages[] = [ 'error', ' > ' . $t->get_error_message() ];
			}

			return false;
		}

		// new group id => old group id, if exists.
		$this->imported_data['groups'][ $group_id ] = isset( $_group['term_id'] ) ? absint( $_group['term_id'] ) : null;

		return $group_id;
	}

	/**
	 * Create new placements based on import information
	 *
	 * @param array $decoded decoded XML.
	 */
	private function import_placements( &$decoded ) {
		if ( isset( $decoded['placements'] ) && is_array( $decoded['placements'] ) ) {

			$existing_placements = $updated_placements = Advanced_Ads::get_instance()->get_model()->get_ad_placements_array();
			$placement_types     = Advanced_Ads_Placements::get_placement_types();

			foreach ( $decoded['placements'] as &$placement ) {
				$use_existing = ! empty( $placement['use_existing'] );

				// use existing placement.
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

				// create new placement.
				} else {
					if ( empty( $placement['key'] ) || empty( $placement['name'] )  || empty( $placement['type'] ) ) {
						continue;
					}

					$placement_key_uniq = sanitize_title( $placement['key'] );
					if ( '' === $placement_key_uniq ) {
						continue;
					}

					$placement['type'] = ( isset( $placement_types[ $placement['type'] ] ) ) ? $placement['type'] : 'default';
					$placement['name'] = esc_attr( $placement['name'] );

					// make sure the key in placement array is unique.
					if ( isset( $existing_placements[ $placement_key_uniq ] ) ) {
						$count = 1;
						while ( isset( $existing_placements[ $placement_key_uniq . '_' . $count ] ) ) {
							$count++;
						}
						$placement_key_uniq .= '_' . $count;
					}

					/* translators: %s is a placement name */
					$this->messages[] = [ 'update', sprintf( __( 'Placement <em>%s</em> created', 'advanced-ads' ), esc_html( $placement['name'] ) ) ];

					// new placement key => old placement key.
					$this->imported_data['placements'][ $placement_key_uniq ] = $placement['key'];
				}

				// try to set "Item" (ad or group).
				if ( ! empty( $placement['item'] ) ) {
					$_item = explode( '_', $placement['item'] );
					if ( ! empty( $_item[1] ) ) {
						switch ( $_item[0] ) {
							case 'ad':
							case Advanced_Ads_Select::AD:

								$found = $this->search_item( $_item[1], Advanced_Ads_Select::AD );
								if ( false === $found ) {
									break;
								}

								if ( $use_existing ) {
									// assign new ad to an existing placement
									// - if the placement has no or a single ad assigned, it will be swapped against the new one
									// - if a group is assigned to the placement, the new ad will be added to this group with a weight of 1
									$placement = $existing_placement;

									if ( ! empty( $placement['item'] ) ) {
										// get the item from the existing placement.
										$_item_existing = explode( '_', $placement['item'] );

										if ( ! empty( $_item_existing[1] ) && $_item_existing[0] === Advanced_Ads_Select::GROUP ) {
											$advads_ad_weights = get_option( 'advads-ad-weights', [] );

											if ( term_exists( absint( $_item_existing[1] ), Entities::TAXONOMY_AD_GROUP ) ) {
												wp_set_post_terms( $found, $_item_existing[1], Entities::TAXONOMY_AD_GROUP, true );

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
							case Advanced_Ads_Select::GROUP:
								$found = $this->search_item( $_item[1], Advanced_Ads_Select::GROUP );
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

				$updated_placements[ $placement_key_uniq ] = apply_filters( 'advanced-ads-import-placement', $placement, $this );
			}

			if ( $existing_placements !== $updated_placements ) {
				Advanced_Ads::get_instance()->get_model()->update_ad_placements_array( $updated_placements );
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
			case Advanced_Ads_Select::AD:
				// if the ad was was imported.
				if ( ! $found = array_search( $id, $this->imported_data['ads'] ) ) {
					// if the ad already exists.
					if ( get_post_type( $id ) === Entities::POST_TYPE_AD ) {
						$found = $id;
					}
				}
				break;
			case Advanced_Ads_Select::GROUP:
				if ( ! $found = array_search( $id, $this->imported_data['groups'] ) ) {
					if ( term_exists( absint( $id ), Entities::TAXONOMY_AD_GROUP ) ) {
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
	private function import_options( &$decoded ) {
		if ( isset( $decoded['options'] ) && is_array( $decoded['options'] ) ) {
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

				/* translators: %s: Option name. */
				$this->messages[] = [ 'update', sprintf( __( 'Option was updated: <em>%s</em>', 'advanced-ads' ), $option_name ) ];
				update_option( $option_name, WordPress::maybe_unserialize( $option_to_import ) );
			}
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
			return;
		}

		$import_dir = $uploads_dir['basedir'] . '/advads-import';
		$this->import_id = $import_dir . '/' . md5( time() . NONCE_SALT );

		if ( ! is_dir( $import_dir) && ! wp_mkdir_p( $import_dir ) ) {
			/* translators: %s import directory */
			$this->messages[] = [ 'error',  sprintf( __( 'Failed to create import directory <em>%s</em>', 'advanced-ads' ), $import_dir ) ];
			return;
		}

		if ( ! is_writable( $import_dir ) ) {
			/* translators: %s import directory */
			$this->messages[] = [ 'error',  sprintf( __( 'Import directory is not writable: <em>%s</em>', 'advanced-ads' ), $import_dir ) ];
			return;
		}

		if ( ! @file_exists( $import_dir . '/index.php') ) {
			@touch( $import_dir . '/index.php' );
		}

		if ( ! isset( $_FILES['import'] ) ) {
			$this->messages[] = [ 'error', __( 'File is empty, uploads are disabled or post_max_size is smaller than upload_max_filesize in php.ini', 'advanced-ads' ) ];
			return;
		}

		$file = $_FILES['import'];

		// determine if uploaded file exceeds space quota.
		$file = apply_filters( 'wp_handle_upload_prefilter', $file );

		if ( ! empty( $file['error'] ) ) {
			/* translators: %s error in file */
			$this->messages[] = [ 'error', sprintf( __( 'Failed to upload file, error: <em>%s</em>', 'advanced-ads' ), $file['error'] ) ];
			return;
		}

		if ( ! ( $file['size'] > 0 ) ) {
			$this->messages[] = [ 'error', __( 'File is empty.', 'advanced-ads' ), $file['error'] ];
			return;
		}

		if ( ! is_uploaded_file( $file['tmp_name'] ) || ! @ move_uploaded_file( $file['tmp_name'], $this->import_id ) || ! is_readable( $this->import_id  ) ) {
			/* translators: %s import id */
			$this->messages[] = [ 'error', sprintf( __( 'The file could not be created: <em>%s</em>. This is probably a permissions problem', 'advanced-ads' ), $this->import_id ) ];
			return;
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
					$this->messages[] = [ 'update', sprintf( __( 'New attachment created <em>%1$s</em> %2$s', 'advanced-ads' ), $attachment['post_id'], $link ) ];
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
		$mime_types = array_filter( get_allowed_mime_types(), function( $mime_type ) {
			return preg_match( '/image\//', $mime_type );
		} );
		$fileinfo   = @getimagesize( $upload['file'] );

		if ( ! $fileinfo || ! in_array( $fileinfo['mime'], $mime_types, true ) ) {
			@unlink( $upload['file'] );
			/* translators: %s image url */
			$this->messages[] = [ 'error', sprintf( __( 'Error getting remote image <em>%s</em>', 'advanced-ads' ), $image_url ) ];

			return false;
		}

		// create new post
		$new_post = [
			'post_title' => $file_name,
			'post_mime_type' => $wp_filetype['type'],
			'guid' => $upload['url'],
		];

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		$post_id = wp_insert_attachment( $new_post, $upload['file'] );
		wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

		return [ 'post_id' => $post_id, 'attachment_url' => wp_get_attachment_url( $post_id ) ];
	}

	public function get_messages() {
		return $this->messages;
	}
}
