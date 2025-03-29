<?php
/**
 * Render box with problems and notifications on the Advanced Ads overview page
 *
 * @package AdvancedAds
 *
 * @var int $ignored_count number of ignored notices.
 */

use AdvancedAds\Utilities\Data;

?>
<div class="advads-widget-wrapper pt-4">
	<?php if ( ! Advanced_Ads_Ad_Health_Notices::get_instance()->has_notices() ) : ?>
		<div class="ml-2"><?php esc_html_e( 'There are no notifications.', 'advanced-ads' ); ?></div>
	<?php else : ?>
		<?php Advanced_Ads_Ad_Health_Notices::get_instance()->display_problems(); ?>
		<?php Advanced_Ads_Ad_Health_Notices::get_instance()->display_notices(); ?>
		<?php Advanced_Ads_Ad_Health_Notices::get_instance()->display_pitches(); ?>

		<div class="advads-ad-health-notices-show-hidden" <?php echo ! $ignored_count ? 'style="display: none;"' : ''; ?>>
			<?php
			printf(
				/* translators: %s is the number of hidden notices. */
				esc_html__( '%s hidden notifications', 'advanced-ads' ),
				'<span class="count">' . absint( $ignored_count ) . '</span>'
			);
			?>
		</div>
		<div class="advads-loader" style="display: none;"></div>
	<?php endif; ?>
</div>

<footer>
	<span class="dashicons dashicons-lightbulb"></span>
	<a class="no-underline" href="<?php echo esc_url( Data::support_url( '/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-notices-support' ) ); ?>" target="_blank">
		<?php esc_html_e( 'Save time and get personal support.', 'advanced-ads' ); ?>
		<strong class="underline"><?php esc_html_e( 'Ask your question!', 'advanced-ads' ); ?></strong>
		<span aria-hidden="true" class="dashicons dashicons-external"></span>
	</a>
</footer>
