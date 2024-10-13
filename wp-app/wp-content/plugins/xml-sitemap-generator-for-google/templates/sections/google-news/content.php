<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Content Options', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p><?php esc_html_e( 'This settings will be used for generating your Google News.', 'xml-sitemap-generator-for-google' ); ?></p>

		<table class="wp-list-table widefat fixed striped">
			<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Content', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Include', 'xml-sitemap-generator-for-google' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			Dashboard::render_content_field(
				'Posts',
				'post_google_news',
				$settings->post->google_news ?? 1,
				'google-news-depended'
			);
			Dashboard::render_content_field(
				'Pages',
				'page_google_news',
				$settings->page->google_news ?? 0,
				'google-news-depended'
			);
			?>
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
							"{$cpt->name}_google_news",
							$settings->cpt[ $cpt->name ]->google_news ?? 0,
							'google-news-depended'
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
