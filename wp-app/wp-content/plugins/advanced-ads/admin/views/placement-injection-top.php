<?php
/**
 * Render placements after publishing an ad.
 *
 * @package AdvancedAds
 *
 * @var Ad    $ad         Ad instance.
 * @var array $placements Placements array.
 */

// show quick injection options.
// check if the ad code contains the AdSense verification and Auto ads code.
$is_page_level_ad_in_code_field = $ad->is_type( 'plain' ) && strpos( $ad->get_content(), 'enable_page_level_ads' ) || preg_match( '/script[^>]+data-ad-client=/', $ad->get_content() ); // phpcs:ignore
?>
<div id="advads-ad-injection-box" class="advads-ad-metabox postbox">
<span class="advads-loader" style="display: none;"></span>
	<div id="advads-ad-injection-message-placement-created" class="hidden">
	<p><?php esc_html_e( 'Congratulations! Your ad is now visible in the frontend.', 'advanced-ads' ); ?></p>
	<?php if ( empty( $ad->get_display_conditions() ) && ! empty( $latest_post ) ) : ?>
		<a class="button button-primary" target="_blank" href="<?php echo esc_url( get_permalink( $latest_post['ID'] ) ); ?>"><?php esc_html_e( 'Take a look at your ad', 'advanced-ads' ); ?></a>
	<?php endif; ?>
	<p class="hide-server-placement">
		<?php
		printf(
			wp_kses(
				/* translators: %s is a URL. */
				__( 'Ad not showing up? Take a look <a href="%s" target="_blank">here</a>', 'advanced-ads' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
					],
				]
			),
			'https://wpadvancedads.com/manual/ads-not-showing-up/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-ad-not-visible'
		);
		?>
	</p>
	<p class="hide-server-placement">
		<?php
		printf(
			/* translators: %1$s is the opening link tag, %2$s is closing link tag. */
			esc_html__( 'Adjust the placement options? Take a look  %1$shere.%2$s', 'advanced-ads' ),
			'<a href="' . esc_url( admin_url( 'admin.php?page=advanced-ads-placements#single-placement-' ) ) . '" target="_blank">',
			'</a>'
		);
		?>
	</p>
	</div>
	<div id="advads-ad-injection-box-placements">
		<h2><?php esc_html_e( 'Where do you want to display the ad?', 'advanced-ads' ); ?></h2>
						<?php
						// show different placements if this is the AdSense Auto ads code.
						if ( $is_page_level_ad_in_code_field ) :
							if ( Advanced_Ads_AdSense_Data::get_instance()->is_page_level_enabled() ) :
								?>
				<p>
								<?php
								sprintf(
									wp_kses(
										/* translators: %s is a URL. */
										__( 'The AdSense verification and Auto ads code is already activated in the <a href="%s">AdSense settings</a>.', 'advanced-ads' ),
										[
											'a' => [
												'href' => [],
											],
										]
									),
									admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' )
								);
								?>
								</p><p>
								<?php
								esc_attr_e( 'No need to add the code manually here, unless you want to include it into certain pages only.', 'advanced-ads' );
			endif;
							?>
			<p><?php esc_attr_e( 'Click on the button below to add the Auto ads code to the header of your site.', 'advanced-ads' ); ?></p>
			<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="header" style="background-image: url(
							<?php
								// phpcs:ignore
								echo ADVADS_BASE_URL . 'admin/assets/img/placements/header.png';
							?>
							)">
							<?php
								/**
								 * Translators: this is a label in a button when a user uses an AdSense Auto ads code in a plain code field
								 * the button has barely space for the original English text, so keep it short
								 */
								esc_attr_e( 'inject Auto ads', 'advanced-ads' );
							?>
								</button></div>
			<div class="clear"></div>

						<?php else : ?>
			<p><?php esc_html_e( 'New placement', 'advanced-ads' ); ?></p>
			<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="post_top" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/content-before.png'; ?>)"><?php esc_html_e( 'Before Content', 'advanced-ads' ); ?></button></div>
			<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="post_content" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/content-within.png'; ?>)"><?php esc_html_e( 'Content', 'advanced-ads' ); ?></button></div>
			<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="post_bottom" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/content-after.png'; ?>)"><?php esc_html_e( 'After Content', 'advanced-ads' ); ?></button></div>
			<a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/widget.png'; ?>)"><?php esc_html_e( 'Manage Sidebar', 'advanced-ads' ); ?></button></div></a>
			<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="default" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/manual.png'; ?>)"><?php esc_html_e( 'PHP or Shortcode', 'advanced-ads' ); ?></button></div>
			<a href="https://wpadvancedads.com/place-ads-in-website-header/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-placements" target="_blank"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/ads-in-header.png'; ?>)"><?php esc_html_e( 'Header (Manual)', 'advanced-ads' ); ?></button></div></a>
							<?php
							if ( ! defined( 'AAP_VERSION' ) ) :
								?>
			<a href="https://wpadvancedads.com/manual/custom-position-placement/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-placements" target="_blank"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary advads-pro-link" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/custom-position.png'; ?>)"><?php esc_html_e( 'Custom Position', 'advanced-ads' ); ?></button></div></a><a href="https://wpadvancedads.com/add-ons/advanced-ads-pro/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-created-injection-pro" target="_blank"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary advads-pro-link" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/content-random.png'; ?>)"><?php esc_html_e( 'Show Pro Places', 'advanced-ads' ); ?></button></div></a>
								<?php
			else :
				?>
				<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="custom_position" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/custom-position.png'; ?>)"><?php esc_html_e( 'Custom Position', 'advanced-ads' ); ?></button></div>
				<?php
			endif;
			if ( class_exists( 'Advanced_Ads_In_Feed', false ) ) :
				?>
				<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="adsense_in_feed" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/adsense-in-feed.png'; ?>)"><?php esc_html_e( 'AdSense In-feed', 'advanced-ads' ); ?></button></div>
				<?php
			endif;

			if ( ! defined( 'AASADS_VERSION' ) ) :
				?>
			<a href="https://wpadvancedads.com/add-ons/sticky-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-created-injection-sticky" target="_blank"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary advads-pro-link" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/sticky-sidebar-left.png'; ?>)"><?php esc_html_e( 'Show Sticky Places', 'advanced-ads' ); ?></button></div></a>
				<?php
			endif;

			if ( ! defined( 'AAPLDS_VERSION' ) ) :
				?>
			<a href="https://wpadvancedads.com/add-ons/popup-and-layer-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-created-injection-layer" target="_blank"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary advads-pro-link" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/layer.png'; ?>)"><?php esc_html_e( 'Show PopUp', 'advanced-ads' ); ?></button></div></a>
				<?php
			else :
				?>
				<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="layer" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/layer.png'; ?>)"><?php esc_html_e( 'PopUp & Layer', 'advanced-ads' ); ?></button></div>
				<?php
			endif;

			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=advanced-ads-placements' ) ); ?>">
				<div class="advads-ad-injection-box-button-wrap">
					<button type="button" class="advads-ad-injection-button button-primary" style="background-image: url(<?php echo esc_url( ADVADS_BASE_URL ) . 'admin/assets/img/placements/more.png'; ?>)">
							<?php esc_html_e( 'see all…', 'advanced-ads' ); ?>
					</button>
				</div>
			</a>
							<?php
							ob_start();
							foreach ( $placements as $placement_id => $placement ) :
								// Early bail!!
								if ( empty( $placement->get_type() ) || empty( $placement->get_title() ) ) {
									continue;
								}
								?>
				<div class="advads-ad-injection-box-button-wrap">
								<?php
								printf(
									'<button type="button" class="advads-ad-injection-button button-primary" data-placement-id="%1$s" data-placement-type="%2$s" %3$s title="%4$s">%5$s</button>',
									esc_attr( $placement_id ),
									esc_attr( $placement->get_type() ),
									'style="background-image: url(' . $placement->get_type_object()->get_image() . ');"', // phpcs:ignore
									esc_html( $placement->get_title() ),
									esc_html( $placement->get_type_object()->get_title() )
								);
								echo esc_html( $placement->get_title() );
								?>
							</div>
								<?php
							endforeach;

							$existing_p_output = ob_get_clean();
							if ( $existing_p_output ) :
								?>
								<div class="clear"></div>
								<p><?php esc_html_e( 'Existing placement', 'advanced-ads' ); ?></p>
								<?php
							// phpcs:ignore
							echo $existing_p_output;
						endif;
							?>

			<div class="clear"></div>
			<p>
							<?php
							printf(
							/* translators: %s is some HTML. */
							__( 'Or use the shortcode %s to insert the ad into the content manually.', 'advanced-ads' ), // phpcs:ignore
								'<input id="advads-ad-injection-shortcode" onclick="this.select();" value="[the_ad id=\'' . absint( $post->ID ) . '\']"/>'
							);
							?>
							<?php
							printf(
								/* translators: %s is a URL. */
								__( 'Learn more about your choices to display an ad in the <a href="%s" target="_blank">manual</a>.', 'advanced-ads' ), // phpcs:ignore
								'https://wpadvancedads.com/manual/display-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-created'
							);
							?>
								</p>
		<?php endif; ?>
	</div>
</div>
	<?php
