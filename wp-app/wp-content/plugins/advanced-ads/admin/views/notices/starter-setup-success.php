<?php
/**
 * Success notice for the starter setup.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.x.x
 *
 * @var string $last_post_link URL to the last created post.
 */

?>
<div class="notice notice-success advads-admin-notice message">
	<h2>
		<?php esc_html_e( '2 Test Ads successfully added!', 'advanced-ads' ); ?>
	</h2>
	<p>
		<?php esc_html_e( 'Look below for the list of created ads.', 'advanced-ads' ); ?>
	</p>
	<p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=advanced-ads-placements' ) ); ?>"><?php esc_attr_e( 'Visit list of placements', 'advanced-ads' ); ?></a>
	</p>
	<?php if ( $last_post_link ) : ?>
		<p>
			<a href="<?php echo esc_url( $last_post_link ); ?>" target="_blank">
				<?php esc_html_e( 'See them in action', 'advanced-ads' ); ?>
			</a>
		</p>
	<?php endif; ?>
</div>
