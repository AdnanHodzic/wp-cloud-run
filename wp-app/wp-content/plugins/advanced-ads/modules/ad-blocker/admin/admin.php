<?php // phpcs:ignoreFile
/**
 * Ad blocker admin functionality.
 */
class Advanced_Ads_Ad_Blocker_Admin {
	/**
	 * Singleton instance of the plugin
	 *
	 * @var     Advanced_Ads_Ad_Blocker_Admin
	 */
	protected static $instance;

	/**
	 * Module options
	 *
	 * @var     array (if loaded)
	 */
	protected $options;

	/**
	 * Pattern to search assets using preg_match. The string ends with .css/.js/.png/.gif
	 *
	 * @var     string
	 */
	protected $search_file_pattern = '/(css|js|png|gif)$/';

	/**
	 * Pattern to exclide directories from search. The string does not contain '/vendor/' or '/lib/' or '/admin/' or /node_modules/
	 *
	 * @var     string
	 */
	protected $exclude_dir_pattern = '/(\/vendor\/|\/lib\/|\/admin\/|\/node_modules\/)/';

	/**
	 * Array, containing path information on the currently configured uploads directory
	 *
	 * @var     array
	 */
	protected $upload_dir;

	/**
	 * Error messages for user
	 *
	 * @var     WP_Error
	 */
	protected $error_messages;

	/**
	 * Initialize the module
	 *
	 */
	private function __construct() {
		// add module settings to Advanced Ads settings page
		add_action( 'advanced-ads-settings-init', [ $this, 'settings_init' ], 9, 1 );

		$is_main_site = is_main_site( get_current_blog_id() );
		if ( ! $is_main_site ) {
			return;
		}

		// Get the most recent options values
		$this->options = Advanced_Ads_Ad_Blocker::get_instance()->options();
		$this->upload_dir = $this->options['upload_dir'];

		add_action( 'admin_init', [ $this, 'process_auto_update' ] );

		$this->error_messages = new WP_Error();
	}

	/**
	 * Return an instance of Advanced_Ads_Ad_Blocker
	 *
	 * @return  Advanced_Ads_Ad_Blocker_Admin
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if (null === self::$instance)
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Add settings to settings page.
	 *
	 * @param   string $hook settings page hook
	 */
	public function settings_init( $hook ) {
		add_settings_field(
			'use-adblocker',
			__( 'Ad blocker fix', 'advanced-ads' ),
			[ $this, 'render_settings_use_adblocker' ],
			$hook,
			'advanced_ads_adblocker_setting_section'
		);
	}

	/**
	* Render setting to enable/disable adblocker.
	*/
	public function render_settings_use_adblocker() {
		$is_main_site = is_main_site( get_current_blog_id() );
		$checked      = ! empty( Advanced_Ads::get_instance()->options()['use-adblocker'] );

		include ADVADS_AB_BASE_PATH . 'admin/views/setting-use-adblocker.php';

		// if this is a sub site in a network, don't run the rebuild form code.
		if ( ! $is_main_site ) {
			return;
		}

		// add the rebuild form directly after the settings
		?>
		<div id="advads-adblocker-wrapper" <?php echo( $checked ? '' : 'style="display: none;"' ); ?>>
			<?php
			$button_disabled = true;
			$upload_dir      = $this->upload_dir;
			$options         = $this->options;

			include ADVADS_AB_BASE_PATH . 'admin/views/rebuild_form.php';
			?>
		</div>
		<?php
	}

	/**
	 * Render the ad-blocker rebuild assets form
	 *
	 */
	public function add_asset_rebuild_form() {
		global $wp_filesystem;
		$success = false;
		$message = '';

		$fs_connect = Advanced_Ads_Filesystem::get_instance()->fs_connect( $this->upload_dir['basedir'] );

		if ( $fs_connect === false || is_wp_error( $fs_connect ) ) {
			$message = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'advanced-ads' );

			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$message = esc_html( $wp_filesystem->errors->get_error_message() );
			}
			if ( is_wp_error( $fs_connect ) && $fs_connect->get_error_code() ) {
				$message = esc_html( $fs_connect->get_error_message() );
			}
		} else {
			$output = $this->process_form();
			if ( is_wp_error( $output ) ) {
				$message = $output->get_error_message();
			} else {
				$success = true;
				$message = __( 'The asset folder was rebuilt successfully', 'advanced-ads' );
			}
		}

		$upload_dir      = $this->upload_dir;
		$button_disabled = false;
		$options         = Advanced_Ads_Ad_Blocker::get_instance()->options( true );

		include ADVADS_AB_BASE_PATH . 'admin/views/rebuild_form.php';
	}

	/**
	 * Perform processing of the rebuild_form, sent by user
	 *
	 * @return true|WP_Error true on success, WP_Error in case of error
	 **/
	private function process_form() {
		// at this point we do not need ftp/ssh credentials anymore
		$form_post_fields = array_intersect_key( $_POST, [ 'advads_ab_assign_new_folder' => false ] );

		$this->create_dummy_plugin( $form_post_fields );

		if ( $error_messages = $this->error_messages->get_error_messages() ) {
			foreach ( $error_messages as $error_message ) {
				Advanced_Ads::log( __METHOD__ . ': ' . $error_message );
			}

			return $this->error_messages;
		}

		return true;
	}

	/**
	 * Creates dummy plugin and return new options, that need to be stored in database.
	 *
	 * @param   array $form_post_fields options, POST data sent by user.
	 * @return  array $new_options - options, that need to be stored in database.
	 */
	public function create_dummy_plugin( $form_post_fields = [] ) {
		global $wp_filesystem;

		$need_assign_new_name = isset( $form_post_fields['advads_ab_assign_new_folder'] );

		if ( ! $this->upload_dir ) {
			$message = __( 'There is no writable upload folder', 'advanced-ads' );
			$this->error_messages->add( 'create_dummy_1', $message);
			return false;
		}

		$new_options       = [
			'lookup_table' => isset( $this->options['lookup_table'] ) ? $this->options['lookup_table'] : [],
		];
		$new_options_error = $new_options;
		// $new_options_error does not have the 'module_can_work' key - ad-blocker script will be inactive and the asset folder will be rebuilt next time
		$new_options['module_can_work'] = true;

		$existing_files = @scandir( $this->upload_dir['basedir'] );
		if ( $existing_files ) {
			$existing_files = array_diff(  $existing_files, [ '..', '.' ] );
		} else {
			$existing_files = [];
		}

		if ( ! empty( $this->options['folder_name'] ) ) {
			$new_options['folder_name'] = $new_options_error['folder_name'] = $this->options['folder_name'];

			$old_folder_normalized = Advanced_Ads_Filesystem::get_instance()->normalize_path( trailingslashit( $this->upload_dir['basedir'] ) ) . $this->options['folder_name'];

			if ( $wp_filesystem->exists( $old_folder_normalized ) ) {

				if ( $need_assign_new_name ) {
					$existing_files[] = (string) $new_options['folder_name'];
					$new_folder_name = $this->generate_unique_name( $existing_files );
					$new_folder_normalized = Advanced_Ads_Filesystem::get_instance()->normalize_path( trailingslashit( $this->upload_dir['basedir'] ) ) . $new_folder_name;

					if ( ! $wp_filesystem->move( $old_folder_normalized, $new_folder_normalized ) ) {
						/* translators: %s old folder name */
						$message = sprintf( __( 'Unable to rename "%s" directory', 'advanced-ads' ), $old_folder_normalized );
						$this->error_messages->add( 'create_dummy_2', $message);
						return false;
					}
					$new_options['folder_name'] = $new_options_error['folder_name'] = $new_folder_name;

				}

				$is_rebuild_needed = count( $this->get_assets() );

				// we have an error while the method is being executed
				Advanced_Ads_Ad_Blocker::get_instance()->update_options( $new_options_error );

				if ( $is_rebuild_needed ) {
					$lookup_table = $this->copy_assets( $new_options['folder_name'], $need_assign_new_name );
					if ( ! $lookup_table ) {
						/* translators: %s folder name */
						$message = sprintf( __( 'Unable to copy assets to the "%s" directory', 'advanced-ads' ), $new_options['folder_name'] );
						$this->error_messages->add( 'create_dummy_3', $message);
						return false;
					}
					$new_options['lookup_table'] = $lookup_table;
				}

			} else {
				// we have an error while the method is being executed
				Advanced_Ads_Ad_Blocker::get_instance()->update_options( $new_options_error );
				// old folder does not exist, let's create it
				$lookup_table = $this->copy_assets( $new_options['folder_name'] );
				if ( ! $lookup_table ) {
					/* translators: %s folder name */
					$message = sprintf( __( 'Unable to copy assets to the "%s" directory', 'advanced-ads' ), $new_options['folder_name'] );
					$this->error_messages->add( 'create_dummy_4', $message);
					return false;
				}
				$new_options['lookup_table'] = $lookup_table;
			}
		} else {
			// It seems this is the first time this plugin was ran, let's create everything we need in order to
			// have this plugin function normally.
			$new_folder_name = $this->generate_unique_name( $existing_files );
			// Create a unique folder name
			$new_options['folder_name'] = $new_options_error['folder_name'] = $new_folder_name;
			// we have an error while the  method is being executed
			Advanced_Ads_Ad_Blocker::get_instance()->update_options( $new_options_error );
			// Copy the assets
			$lookup_table = $this->copy_assets( $new_options['folder_name'] );
			if ( ! $lookup_table ) {
				$message = sprintf( __( 'Unable to copy assets to the "%s" directory', 'advanced-ads' ), $new_options['folder_name'] );
				$this->error_messages->add( 'create_dummy_5', $message);
				return false;
			}
			$new_options['lookup_table'] = $lookup_table;
		}
		// successful result, save options and rewrite previous error options
		Advanced_Ads_Ad_Blocker::get_instance()->update_options( $new_options );
		Advanced_Ads_Ad_Health_Notices::get_instance()->remove( 'assets_expired' );
	}

	/**
	 * Copy all assets (JS/CSS) to the magic directory.
	 *
	 * @param  string $folder_name Destination folder.
	 * @param  bool   $need_assign_new_name True if we need to assign new random names to assets.
	 * @return bool/array Bool false on failure, array lookup table on success.
	 */
	public function copy_assets( $folder_name, $need_assign_new_name = false ) {
		global $wp_filesystem;

		// Are we completely rebuilding the assets folder?
		$asset_path = trailingslashit( $this->upload_dir['basedir'] ) . $folder_name ;
		$asset_path_normalized = Advanced_Ads_Filesystem::get_instance()->normalize_path( trailingslashit( $this->upload_dir['basedir'] ) ) . $folder_name;

		// already saved associations (original name => replaced name)
		$rand_asset_names = [];

		if ( $need_assign_new_name ) {
			// Check if there is a previous asset folder
			if ( $wp_filesystem->exists( $asset_path_normalized ) ) {
				// Remove the old directory and its contents
				if ( ! $wp_filesystem->rmdir( trailingslashit( $asset_path_normalized ), true ) ) {
					/* translators: %s directory path */
					$message = sprintf( __( 'We do not have direct write access to the "%s" directory', 'advanced-ads' ), $asset_path_normalized );
					$this->error_messages->add( 'copy_assets_1', $message);
					return false;
				}
			}
		} elseif ( isset( $this->options['lookup_table'] ) ) {
			foreach ( $this->options['lookup_table'] as $orig_path => $replaced_info ) {
				$replaced_path = is_array( $replaced_info ) ? $replaced_info['path']  : $replaced_info;

				$orig_path_components = preg_split('/\//', $orig_path, -1, PREG_SPLIT_NO_EMPTY);
				$replaced_path_components = preg_split('/\//', $replaced_path, -1, PREG_SPLIT_NO_EMPTY);

				// (css, style.css) => (1, 2.css)
				foreach ( $orig_path_components as $k=> $orig_path_part ) {
					$rand_asset_names[ $orig_path_part] = (string) $replaced_path_components[$k];
				}
			}
		}


		// Lookup_table contains associations between the original path of the asset and it path within our magic folder.
		// I.e: [advanced-ads-layer/admin/assets/css/admin.css] => array( path => /12/34/56/78/1347107783.css, mtime => 99 ).
		$assets = $this->get_assets();
		if ( $need_assign_new_name ) {
			$lookup_table = [];
		} else {
			$lookup_table = isset( $this->options['lookup_table'] ) ? $this->options['lookup_table'] : [];
		}

		/* Do not rename assets and folders. If, for example, some library uses in file.css something like this:
		'background: url(/img/image.png)', you should add 'img') to this array */
		$not_rename_assets = [ 'public', 'assets', 'js', 'css', 'fancybox', 'advanced.js', 'jquery.fancybox-1.3.4.css'  ];

		// Loop through all the found assets
		foreach ( $assets as $file => $filemtime ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			$first_cleanup = str_replace( WP_PLUGIN_DIR , '', $file );
			$first_cleanup_dir = dirname( $first_cleanup );
			$first_cleanup_filename = basename( $first_cleanup );
			$first_cleanup_file_extension = pathinfo( $first_cleanup, PATHINFO_EXTENSION );
			$path_components = preg_split('/\//', $first_cleanup_dir, -1, PREG_SPLIT_NO_EMPTY);
			$path_components_new = [];

			// Interate over directories.
			foreach ( $path_components as $k => $dir ) {
				if ( in_array( $dir, $not_rename_assets ) ) {
					$path_components_new[ $k ] = $dir;
				} elseif ( array_key_exists( $dir, $rand_asset_names ) ) {
					$path_components_new[ $k ] = $rand_asset_names[ $dir ];
				} else {
					$new_rand_folder_name = $this->generate_unique_name( array_values( $rand_asset_names ) );
					$path_components_new[ $k ] = $new_rand_folder_name;
					$rand_asset_names[ $dir ] = (string) $new_rand_folder_name;
				}
			}

			$new_dir_full = trailingslashit( $asset_path ) . trailingslashit( implode( '/', $path_components_new ) );
			$new_dir_full_normalized = trailingslashit( $asset_path_normalized ) . trailingslashit( implode( '/', $path_components_new ) );
			$new_dir = trailingslashit( implode( '/', $path_components_new ) );




			if ( ! in_array( $first_cleanup_filename, $not_rename_assets ) && ( $first_cleanup_file_extension == 'js' || $first_cleanup_file_extension == 'css' ) ) {
				if ( array_key_exists( $first_cleanup_filename, $rand_asset_names ) ) {
					$new_abs_file = $new_dir_full_normalized . $rand_asset_names[$first_cleanup_filename];
					$new_rel_file = $new_dir . $rand_asset_names[$first_cleanup_filename];
				} else {
					$new_filename = $this->generate_unique_name( array_values( $rand_asset_names ), $first_cleanup_file_extension );
					$rand_asset_names[$first_cleanup_filename] = (string) $new_filename;
					$new_abs_file = $new_dir_full_normalized . $new_filename;
					$new_rel_file = $new_dir . $new_filename;
				}
			} else {
				$new_abs_file = $new_dir_full_normalized . $first_cleanup_filename;
				$new_rel_file = $new_dir . $first_cleanup_filename;
			}


			if ( ! file_exists( $new_dir_full_normalized ) ) {
				// Create the path if it doesn't exist (prevents the copy() function from failing)
				if ( ! Advanced_Ads_Filesystem::get_instance()->mkdir_p( $new_dir_full_normalized ) ) {
					$message = sprintf( __( 'We do not have direct write access to the "%s" directory', 'advanced-ads' ), $this->upload_dir['basedir'] );
					$this->error_messages->add( 'copy_assets_4', $message);
					return false;
				}
			}


			$file_normalized = Advanced_Ads_Filesystem::get_instance()->normalize_path( trailingslashit( dirname( $file ) ) ) . basename( $file );

			// Copy the file to our new magic directory,
			if ( ! $wp_filesystem->copy( $file_normalized, $new_abs_file, true, FS_CHMOD_FILE ) ) {
				/* translators: %s directory path */
				$message = sprintf( __( 'Unable to copy files to %s', 'advanced-ads' ), $asset_path_normalized );
				$this->error_messages->add( 'copy_assets_5', $message);
				return false;
			}

			$lookup_table[ $first_cleanup ] = [
				'path'  => $new_rel_file,
				'mtime' => $filemtime,
			];
		}

		return $lookup_table;
	}

	/**
	 * This function recursively searches for assets
	 *
	 * @param  string $dir The directory to search in.
	 * @return Array with pairs: abs_filename => mtime.
	 */
	public function recursive_search_assets( $dir ) {
		$assets = [];

		$tree = glob( rtrim( $dir, '/' ) . '/*' );
		if ( is_array( $tree ) ) {
			foreach ( $tree as $file ) {
				if ( is_dir( $file ) && ! preg_match( $this->exclude_dir_pattern, $file ) ) {
					$assets = array_merge( $assets, $this->recursive_search_assets( $file ) );
				} elseif ( is_file( $file )  && preg_match( $this->search_file_pattern, $file ) ) {
					$assets[ $file ] = @filemtime( $file );
				}
			}
		}

		return $assets;
	}

	/**
	 * Returns new or modified assets and their mtimes.
	 *
	 * @return array
	 */
	public function get_assets() {
		$new_files_info = $this->recursive_search_assets( trailingslashit( WP_PLUGIN_DIR ) . 'advanced-ads*' );

		if ( ! isset( $this->options['lookup_table'] ) || ! isset( $this->upload_dir['basedir'] ) || ! isset( $this->options['folder_name'] ) ) {
			return $new_files_info;
		}

		$asset_path = trailingslashit( trailingslashit( $this->upload_dir['basedir'] ) . $this->options['folder_name'] ) ;
		$new_files = [];

		foreach ( $new_files_info as $abs_file => $mtime ) {
			$rel_file = str_replace( WP_PLUGIN_DIR , '', $abs_file );

			if ( ! isset( $this->options['lookup_table'][ $rel_file ]['mtime'] ) ||
				$this->options['lookup_table'][ $rel_file ]['mtime'] !== $mtime ||
				! file_exists( $asset_path . $this->options['lookup_table'][$rel_file]['path'] )
			) {
				$new_files[ $abs_file ] = $mtime;
			}
		}

		return $new_files;
	}

	/**
	 * Automatically updates assets
	 *
	 */
	public function process_auto_update() {
		$advads_options = Advanced_Ads::get_instance()->options();
		if ( ! isset( $advads_options['use-adblocker'] )
			|| ! $this->upload_dir
		) { return; }

		//if  module is working without errors and there are new assets
		if ( ! empty( $this->options['module_can_work'] ) && count( $this->get_assets() ) ) {
			$fs_connect = Advanced_Ads_Filesystem::get_instance()->fs_connect( $this->upload_dir['basedir'] );

			if ( false === $fs_connect || is_wp_error( $fs_connect ) ) {
				// we can not update assets automatically. The user should visit the setting page and update assets manually
				// disable module and show notice
				unset( $this->options['module_can_work'] );
				Advanced_Ads_Ad_Blocker::get_instance()->update_options( $this->options );
				return;
			}

			$this->create_dummy_plugin();

			// write errors to the log
			if ( $error_messages = $this->error_messages->get_error_messages() ) {
				foreach ( $error_messages as $error_message ) {
					Advanced_Ads::log( __METHOD__ . ': ' . $error_message );
				}
			}
		}

	}

	/**
	 * Generate unique name
	 *
	 * @param    array    $haystack array to check, that the returned string does not exist in this array
	 * @param    string   $extension Extension to append to the name.
	 * @return   string   unique name
	 */
	function generate_unique_name( $haystack = false, $extension = '' ) {
		$extension = $extension ? '.' . $extension : '';
		if ( $haystack ) {
			$i = 0;

			do {
				$rand = (string) mt_rand( 1, 999 );
				if ( ++$i < 100 ) {
					$needle = (string) $rand . $extension;
				} else {
					$needle = (string) $rand . '_' . $i . $extension;
				}

			} while( in_array( $needle, $haystack ) );

			return $needle;
		}

		$needle = (string) mt_rand( 1, 999 ) . $extension;
		return $needle;
	}

	/**
	 * Clear assets (on uninstall)
	 */
	function clear_assets() {
		$advads_options = Advanced_Ads::get_instance()->options();

		if ( ! empty( $this->options['folder_name'] )
			&& ! empty( $this->options['module_can_work'] )
			&& $this->upload_dir
			&& class_exists( 'WP_Filesystem_Direct', false )
		) {
			$wp_filesystem = new WP_Filesystem_Direct( new StdClass() );
			$path = trailingslashit( $this->upload_dir['basedir'] ) . trailingslashit( $this->options['folder_name'] );
			$wp_filesystem->rmdir( $path, true );
		}
	}

}
