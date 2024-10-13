<?php
/**
 * The class is responsible to detect ads.txt.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Modules\OneClick\AdsTxt;

use AdvancedAds\Modules\OneClick\Admin\Admin;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Detector.
 */
class Detector implements Integration_Interface {

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'current_screen', [ $this, 'conditional_loading' ] );
	}

	/**
	 * Detect ads.txt physical file
	 *
	 * @return void
	 */
	public function conditional_loading(): void {
		if ( ! Admin::is_pubguru_page() ) {
			return;
		}

		if ( $this->detect_files() ) {
			add_action( 'all_admin_notices', [ $this, 'show_notice' ] );
		}
	}

	/**
	 * Detect file exists
	 *
	 * @param string $file file name.
	 *
	 * @return bool
	 */
	public function detect_files( $file = 'ads.txt' ): bool {
		$wp_filesystem = $this->get_filesystem();
		if ( null === $wp_filesystem ) {
			return false;
		}

		return $wp_filesystem->exists( ABSPATH . '/' . $file );
	}

	/**
	 * Backup file
	 *
	 * @return bool
	 */
	public function backup_file(): bool {
		$source      = ABSPATH . '/ads.txt';
		$destination = $source . '.bak';
		return $this->move_file( $source, $destination );
	}

	/**
	 * Revert file
	 *
	 * @return bool
	 */
	public function revert_file(): bool {
		$source      = ABSPATH . '/ads.txt.bak';
		$destination = ABSPATH . 'ads.txt';
		return $this->move_file( $source, $destination );
	}

	/**
	 * Show notice that physical file exists
	 *
	 * @return void
	 */
	public function show_notice(): void {
		?>
		<div class="notice notice-error flex items-center p-4">
			<div>
				<strong><?php esc_html_e( 'File alert!', 'advanced-ads' ); ?></strong> <?php esc_html_e( 'Physical ads.txt found. In order to use PubGuru service you need to delete it or back it up.', 'advanced-ads' ); ?>
			</div>

			<button class="!ml-auto button button-primary js-btn-backup-adstxt" data-text="<?php esc_attr_e( 'Backup the File', 'advanced-ads' ); ?>" data-loading="<?php esc_attr_e( 'Backing Up', 'advanced-ads' ); ?>" data-done="<?php esc_attr_e( 'Backed Up', 'advanced-ads' ); ?>" data-security="<?php echo wp_create_nonce( 'pubguru_backup_adstxt' ); // phpcs:ignore ?>">
				<?php esc_html_e( 'Backup the File', 'advanced-ads' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Instantiates the WordPress filesystem for use
	 *
	 * @return object
	 */
	private function get_filesystem() {
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem;
	}

	/**
	 * Handle file operations (backup or revert)
	 *
	 * @param string $source      Source file path.
	 * @param string $destination Destination file path.
	 *
	 * @return bool
	 */
	private function move_file( string $source, string $destination ): bool {
		$wp_filesystem = $this->get_filesystem();

		if ( null === $wp_filesystem || ! $wp_filesystem->is_writable( $source ) ) {
			return false;
		}

		return $wp_filesystem->move( $source, $destination );
	}
}
