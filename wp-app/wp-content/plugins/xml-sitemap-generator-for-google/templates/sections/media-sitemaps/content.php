<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Image & Video Content Options', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p><?php esc_html_e( 'This settings will be used for generating Image & Video Sitemaps.', 'xml-sitemap-generator-for-google' ); ?></p>

		<table class="wp-list-table widefat fixed striped">
			<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Content', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Include', 'xml-sitemap-generator-for-google' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php Dashboard::render_content_field( 'Pages', 'page_media_sitemap', $settings->page->media_sitemap ?? 1 ); ?>
			<?php Dashboard::render_content_field( 'Posts', 'post_media_sitemap', $settings->post->media_sitemap ?? 1 ); ?>
			</tbody>
		</table>

		<?php if ( ! empty( $args['cpt'] ) ) { ?>
			<h3 class="hndle"><?php
				esc_html_e( 'Custom Post Types', 'xml-sitemap-generator-for-google' );

				sgg_show_pro_badge();
			?></h3>
			<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
				<table class="wp-list-table widefat fixed striped tags">
					<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Content', 'xml-sitemap-generator-for-google' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Include', 'xml-sitemap-generator-for-google' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					foreach ( $args['cpt'] as $cpt ) {
						Dashboard::render_content_field(
							$cpt->label,
							"{$cpt->name}_media_sitemap",
							$settings->cpt[ $cpt->name ]->media_sitemap ?? 0
						);
					}
					?>
					</tbody>

				</table>

				<?php sgg_show_pro_overlay(); ?>
			</div>
		<?php } ?>
	</div>
</div>
