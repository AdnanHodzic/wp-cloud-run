<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section">
	<h3 class="grim-section-title" data-search-id="sitemap_options"><?php esc_html_e( 'Sitemap Options', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20"><?php esc_html_e( 'This Options will be used for generating your Sitemap.', 'xml-sitemap-generator-for-google' ); ?></p>
		<table class="grim-table wp-list-table widefat fixed striped">
			<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Content', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Include', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Priority', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Update Frequency', 'xml-sitemap-generator-for-google' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			Dashboard::render_post_row( 'Home Page', 'home', $settings->home );
			Dashboard::render_post_row( 'Pages', 'page', $settings->page );
			Dashboard::render_post_row( 'Posts', 'post', $settings->post );
			Dashboard::render_post_row( 'Recent Archive', 'archive', $settings->archive );
			Dashboard::render_post_row( 'Older Archives', 'archive_older', $settings->archive_older );
			Dashboard::render_post_row( 'Author Pages', 'authors', $settings->authors );
			Dashboard::render_post_row( 'Media Pages', 'media', $settings->media ?? new stdClass() );

			if ( ! empty( $args['taxonomies'] ) ) {
				foreach ( $args['taxonomies'] as $taxonomy ) {
					Dashboard::render_post_row( $taxonomy->label, $taxonomy->name, $settings->taxonomies[ $taxonomy->name ] );
				}
			}
			?>
			</tbody>
		</table>
		<div class="grim-notice grim-mb-20 grim-mt-20">
			<i class="grim-icon-information"></i>
			<p>
				<?php
				$attachment_pages_url = 'https://make.wordpress.org/core/2023/10/16/changes-to-attachment-pages/';
				echo sprintf(
					/* translators: %s: Note about enabling attachment pages with link */
					esc_html__( 'Media Pages: %s', 'xml-sitemap-generator-for-google' ),
					'<span>' . esc_html__( 'Enable WordPress attachment pages on the frontend for this option to work - ', 'xml-sitemap-generator-for-google' )
					. ' <a href="' . esc_url( $attachment_pages_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Learn more', 'xml-sitemap-generator-for-google' ) . '</a>.</span>'
				);
				?>
			</p>
		</div>
	</div>
</div>

<?php if ( ! empty( $args['cpt'] ) ) { ?>
	<div class="grim-section">
		<h3 class="grim-section-title grim-mb-20" data-search-id="general_custom_post_types"><?php esc_html_e( 'Custom Post Types', 'xml-sitemap-generator-for-google' ); ?></h3>
		<table class="grim-table wp-list-table widefat fixed striped tags">
			<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Content', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Include', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Priority', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Update Frequency', 'xml-sitemap-generator-for-google' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ( $args['cpt'] as $cpt ) {
				Dashboard::render_post_row( $cpt->label, $cpt->name, ! empty( $settings->cpt[ $cpt->name ] ) ? $settings->cpt[ $cpt->name ] : $settings->post );
			}
			?>
			</tbody>
		</table>
	</div>
<?php } ?>
