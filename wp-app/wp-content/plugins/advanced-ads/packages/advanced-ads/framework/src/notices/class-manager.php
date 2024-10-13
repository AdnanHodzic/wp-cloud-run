<?php
/**
 * Notification center
 *
 * @package AdvancedAds\Framework\Notices
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Notices;

use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Manager class
 */
class Manager extends Storage {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		parent::hooks();

		add_action( 'admin_footer', [ $this, 'print_javascript' ] );

		add_action( 'wp_ajax_advads_framework_notice_dismissible', [ $this, 'notice_dismissible' ] );
	}

	/**
	 * Display the notices.
	 *
	 * @return void
	 */
	public function display(): void {
		// Never display notices for network admin.
		if ( function_exists( 'is_network_admin' ) && is_network_admin() ) {
			return;
		}

		foreach ( $this->get_notices() as $notice ) {
			if ( $notice->can_display() ) {
				echo $notice; // phpcs:ignore
			}
		}
	}

	/**
	 * Print JS for dismissible.
	 *
	 * @return void
	 */
	public function print_javascript(): void {
		?>
		<script>
			;(function($) {
				$( '.is-dismissible' ).on( 'click', '.notice-dismiss', function() {
					var notice = $( this ).parent()

					$.ajax({
						url: ajaxurl,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'advads_framework_notice_dismissible',
							security: notice.data( 'security' ),
							noticeId: notice.attr( 'id' )
						}
					});
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Dismiss persistent notice.
	 *
	 * @return void
	 */
	public function notice_dismissible(): void {
		$notice_id = Params::post( 'noticeId' );
		check_ajax_referer( $notice_id, 'security' );

		$notice = $this->remove( $notice_id );
	}
}
