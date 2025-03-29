<?php
/**
 * Admin Notices.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Notices.
 */
class Admin_Notices implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'all_admin_notices', [ $this, 'create_first_ad' ] );
		add_action( 'admin_notices', [ $this, 'show_rollback_notice' ] );
	}

	/**
	 * Show instructions to create first ad above the ad list
	 *
	 * @return void
	 */
	public function create_first_ad(): void {
		$screen = get_current_screen();
		if ( ! isset( $screen->id ) || 'edit-advanced_ads' !== $screen->id ) {
			return;
		}

		$counts = WordPress::get_count_ads();

		// Only display if there are no more than 2 ads.
		if ( 3 > $counts ) {
			include ADVADS_ABSPATH . 'views/notices/create-first-ad.php';
		}
	}

	/**
	 * Show notice to rollback to a previous version.
	 *
	 * @return void
	 */
	public function show_rollback_notice(): void {
		// show only on plugins page.
		if ( 'plugins' !== get_current_screen()->id ) {
			return;
		}

		$rollback = filter_input( INPUT_GET, 'rollback', FILTER_VALIDATE_BOOLEAN );
		if ( ! $rollback ) {
			return;
		}

		$rollback_notification = defined( 'ADVADS_VERSION' )
			/* translators: %s: version number */
			? sprintf( esc_html__( 'You have successfully rolled back to Advanced Ads %s', 'advanced-ads' ), ADVADS_VERSION )
			: esc_html__( 'You have successfully rolled back to a previous version of Advanced Ads.', 'advanced-ads' );

		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php echo esc_html( $rollback_notification ); ?>
			</p>
		</div>
		<?php
	}
}
