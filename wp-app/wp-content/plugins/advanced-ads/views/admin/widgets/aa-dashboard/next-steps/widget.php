<?php
/**
 * Render next steps widget
 *
 * @package AdvancedAds
 */

use AdvancedAds\Constants;
use AdvancedAds\Utilities\Conditional;

$recent_ads = wp_advads_get_all_ads();
?>
<div class="advads-widget-wrapper">
	<?php if ( count( $recent_ads ) === 0 ) : ?>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . Constants::POST_TYPE_AD ) ); ?>">
				<?php esc_html_e( 'Create your first ad', 'advanced-ads' ); ?>
			</a>
		</p>

		<p>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ) ); ?>">
				<?php esc_html_e( 'Connect to AdSense', 'advanced-ads' ); ?>
			</a>
		</p>
	<?php endif; ?>

	<?php
	if ( Conditional::user_can_subscribe( 'nl_first_steps' ) || Conditional::user_can_subscribe( 'nl_adsense' ) ) :
		include ADVADS_ABSPATH . 'views/admin/widgets/aa-dashboard/next-steps/newsletter.php';
		?>
	<?php elseif ( count( $recent_ads ) > 3 && Advanced_Ads_Admin_Notices::get_instance()->can_display( 'review' ) ) : ?>
		<div class="advads-admin-notice" data-notice="review">
			<p><?php esc_html_e( 'Do you find Advanced Ads useful and would like to keep us motivated? Please help us with a review.', 'advanced-ads' ); ?></p>
			<p>
				<span class="dashicons dashicons-external"></span>&nbsp;<strong><a href="https://wordpress.org/support/plugin/advanced-ads/reviews/?rate=5#new-post" target="_blank">
					<?php esc_html_e( 'Sure, I’ll rate the plugin', 'advanced-ads' ); ?></a></strong>
				&nbsp;&nbsp;<span class="dashicons dashicons-smiley"></span>&nbsp;<a href="javascript:void(0)" class="advads-notice-dismiss">
					<?php esc_html_e( 'I already did', 'advanced-ads' ); ?></a>
			</p>
		</div>
	<?php elseif ( count( $recent_ads ) > 0 ) : ?>
		<p><a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Constants::POST_TYPE_AD ) ); ?>">
			<?php esc_html_e( 'Manage your ads', 'advanced-ads' ); ?></a></p>
	<?php endif; ?>
</div>

<?php do_action( 'advanced-ads-dashbaord-widget_next-steps' ); ?>

<?php
// Footer.
$all_access = Advanced_Ads_Admin_Licenses::get_instance()->get_probably_all_access();
if ( ! $all_access ) :
	?>
	<footer>
		<a class="no-underline" href="https://wpadvancedads.com/add-ons/all-access/?utm_source=advanced-ads&utm_medium=link&utm_campaign=pitch-bundle" target="_blank">
			<?php esc_html_e( 'Get the All Access pass', 'advanced-ads' ); ?>
			<span aria-hidden="true" class="dashicons dashicons-external"></span>
		</a>
	</footer>
<?php endif; ?>
