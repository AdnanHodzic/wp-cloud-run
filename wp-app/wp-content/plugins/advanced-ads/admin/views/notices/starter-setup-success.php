<div class="notice notice-success advads-admin-notice message">
	<h2><?php esc_attr_e( '2 Test Ads successfully added!', 'advanced-ads' ); ?></h2>
	<p><?php esc_attr_e( 'Look below for the list of created ads.', 'advanced-ads' ); ?></p>
	<p>
		<a href="<?php esc_attr_e( admin_url( 'admin.php?page=advanced-ads-placements' ) ); ?>"><?php esc_attr_e( 'Visit list of placements', 'advanced-ads' ); ?></a>
	</p>
	<?php if ( $last_post_link ) : ?>
		<p>
			<a href="<?php echo esc_url( $last_post_link ); ?>" target="_blank"><?php esc_attr_e( 'See them in action', 'advanced-ads' ); ?></a>
		</p>
	<?php endif; ?>
</div>