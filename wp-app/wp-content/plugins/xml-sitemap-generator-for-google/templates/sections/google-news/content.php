<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section">
	<h3 class="grim-section-title google-news-depended" data-search-id="google_content_post"><?php esc_html_e( 'Content Options', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20 google-news-depended"><?php esc_html_e( 'This settings will be used for generating your Google News.', 'xml-sitemap-generator-for-google' ); ?></p>

		<table class="grim-table wp-list-table widefat fixed striped google-news-depended">
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
	</div>
</div>

<?php if ( ! empty( $args['cpt'] ) ) { ?>
	<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>">
		<?php sgg_show_pro_badge(); ?>
		<h3 class="grim-section-title grim-mb-20 google-news-depended" data-search-id="google_custom_post_types"><?php esc_html_e( 'Custom Post Types', 'xml-sitemap-generator-for-google' ); ?></h3>
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<table class="grim-table wp-list-table widefat fixed striped tags google-news-depended">
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
		</div>
		<?php sgg_show_pro_overlay(); ?>
	</div>
<?php } ?>
