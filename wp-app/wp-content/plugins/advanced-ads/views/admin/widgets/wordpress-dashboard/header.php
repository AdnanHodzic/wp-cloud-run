<?php
/**
 * Render header in widget
 *
 * @package AdvancedAds
 */

use AdvancedAds\Constants;
use AdvancedAds\Utilities\WordPress;

$ads_count = WordPress::get_count_ads();
?>
<div class="advads-widget-wrapper">
	<p class="advads-widget-header m-0">
		<?php
			printf(
				/* translators: %1$d is the number of ads. */
				esc_html__( '%1$d Ads', 'advanced-ads' ),
				absint( $ads_count )
			);
			?>
		|
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Constants::POST_TYPE_AD ) ); ?>">
			<?php esc_html_e( 'Manage Ads', 'advanced-ads' ); ?>
		</a>
		|
		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . Constants::POST_TYPE_AD ) ); ?>">
			<?php esc_html_e( 'Create Ad', 'advanced-ads' ); ?>
		</a>
	</p>
</div>
