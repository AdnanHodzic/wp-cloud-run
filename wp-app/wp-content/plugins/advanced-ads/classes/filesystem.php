<?php // phpcs:ignoreFile

/**
 * @since 1.7.17
 */
class Advanced_Ads_Filesystem {
	/**
	 * Singleton instance of the class
	 *
	 * @var Advanced_Ads_Filesystem
	 */
	protected static $instance;

	/**
	 * Return an instance of Advanced_Ads_Filesystem
	 *
	 * @return  Advanced_Ads_Filesystem
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Connect to the filesystem.
	 *
	 * @param array $directories                  A list of directories. If any of these do
	 *                                            not exist, a WP_Error object will be returned.
	 * @return bool|WP_Error True if able to connect, false or a WP_Error otherwise.
	 */
	public function fs_connect( $directories = [] ) {
		global $wp_filesystem;
		$directories = ( is_array( $directories ) && count( $directories ) ) ? $directories : [ WP_CONTENT_DIR ];

		// This will output a credentials form in event of failure, We don't want that, so just hide with a buffer.
		ob_start();
		$credentials = request_filesystem_credentials( '', '', false, $directories[0] );
		ob_end_clean();

		if ( false === $credentials ) {
			return false;
		}

		if ( ! WP_Filesystem( $credentials ) ) {
			$error = true;
			if ( is_object( $wp_filesystem ) && $wp_filesystem->errors->get_error_code() ) {
				$error = $wp_filesystem->errors;
			}
			// Failed to connect, Error and request again.
			ob_start();
			request_filesystem_credentials( '', '', $error, $directories[0] );
			ob_end_clean();
			return false;
		}

		if ( ! is_object( $wp_filesystem) ) {
			return new WP_Error( 'fs_unavailable', __( 'Could not access filesystem.', 'advanced-ads' ) );
		}

		if ( is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
			return new WP_Error( 'fs_error', __( 'Filesystem error.', 'advanced-ads' ), $wp_filesystem->errors);
		}

		foreach ( (array) $directories as $dir ) {
			switch ( $dir ) {
				case ABSPATH:
					if ( ! $wp_filesystem->abspath() )
						return new WP_Error( 'fs_no_root_dir', __( 'Unable to locate WordPress root directory.', 'advanced-ads' ) );
					break;
				case WP_CONTENT_DIR:
					if ( ! $wp_filesystem->wp_content_dir() )
						return new WP_Error( 'fs_no_content_dir', __( 'Unable to locate WordPress content directory (wp-content).', 'advanced-ads' ) );
					break;
				default:
					if ( ! $wp_filesystem->find_folder( $dir ) )
						/* translators: %s directory */
						return new WP_Error( 'fs_no_folder', sprintf( __( 'Unable to locate needed folder (%s).', 'advanced-ads' ) , esc_html( basename( $dir ) ) ) );
					break;
			}
		}

		return true;
	}

	/**
	 * Replace the 'direct' absolute path with the Filesystem API path. Useful only when the 'direct' method is not used.
	 * Works only with folders.
	 * Check https://codex.wordpress.org/Filesystem_API for info
	 *
	 * @param    string  existing path
	 * @return   string  normalized path
	 */
	public function normalize_path( $path ) {
		global $wp_filesystem;
		return $wp_filesystem->find_folder( $path );
	}

	/**
	 * Recursive directory creation based on full path.
	 *
	 * @param string $target Full path to attempt to create.
	 * @return bool Whether the path was created. True if path already exists.
	 */
	public function mkdir_p( $target ) {
		global $wp_filesystem;

		if ( $wp_filesystem instanceof WP_Filesystem_Direct ) {
			return wp_mkdir_p( $target );
		}

		$target = rtrim($target, '/');
		if ( empty($target) ) {
			$target = '/';
		}

		if ( $wp_filesystem->exists( $target ) ) {
			return $wp_filesystem->is_dir( $target );
		}

		$target_parent = dirname( $target );
		while ( '.' != $target_parent && ! $wp_filesystem->is_dir( $target_parent ) ) {
			$target_parent = dirname( $target_parent );
		}

		$folder_parts = explode( '/', substr( $target, strlen( $target_parent ) + 1 ) );
		for ( $i = 1, $c = count( $folder_parts ); $i <= $c; $i++ ) {
			$dir = $target_parent . '/' . implode( '/', array_slice( $folder_parts, 0, $i ) );
			if ( $wp_filesystem->exists( $dir ) ) { continue; }

			if ( ! $wp_filesystem->mkdir( $dir ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Print the filesystem credentials modal when needed.
	 */
	public function print_request_filesystem_credentials_modal() {
		$filesystem_method = get_filesystem_method();
		ob_start();
		$filesystem_credentials_are_stored = request_filesystem_credentials( self_admin_url() );
		ob_end_clean();
		$request_filesystem_credentials = ( $filesystem_method != 'direct' && ! $filesystem_credentials_are_stored );
		if ( ! $request_filesystem_credentials ) {
			return;
		}
		?>
		<div id="advanced-ads-rfc-dialog" class="notification-dialog-wrap request-filesystem-credentials-dialog">
			<div class="notification-dialog-background"></div>
			<div class="notification-dialog" role="dialog" aria-labelledby="request-filesystem-credentials-title" tabindex="0">
				<div class="request-filesystem-credentials-dialog-content">
					<?php request_filesystem_credentials( site_url() ); ?>
				</div>
			</div>
		</div>
		<?php
	}
}
