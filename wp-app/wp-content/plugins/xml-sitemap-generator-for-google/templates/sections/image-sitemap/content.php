<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section">
	<h3 class="grim-section-title" data-search-id="image_content_option"><?php esc_html_e( 'Content Options', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20"><?php esc_html_e( 'This settings will be used for generating Image Sitemaps.', 'xml-sitemap-generator-for-google' ); ?></p>

		<table class="grim-table wp-list-table widefat fixed striped">
			<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Content', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Include', 'xml-sitemap-generator-for-google' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php Dashboard::render_content_field( 'Pages', 'page_image_sitemap', $settings->page->image_sitemap ?? 1 ); ?>
			<?php Dashboard::render_content_field( 'Posts', 'post_image_sitemap', $settings->post->image_sitemap ?? 1 ); ?>
			</tbody>
		</table>

	</div>
</div>

<?php if ( ! empty( $args['cpt'] ) ) { ?>
	<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>">
		<?php sgg_show_pro_badge(); ?>
		<h3 class="grim-section-title grim-mb-20" data-search-id="image_custom_post_types">
			<?php esc_html_e( 'Custom Post Types', 'xml-sitemap-generator-for-google' ); ?>
		</h3>
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<table class="grim-table wp-list-table widefat fixed striped tags">
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
						"{$cpt->name}_image_sitemap",
						$settings->cpt[ $cpt->name ]->image_sitemap ?? 0
					);
				}
				?>
				</tbody>
			</table>
		</div>
		<?php sgg_show_pro_overlay(); ?>
	</div>
	<?php
}
