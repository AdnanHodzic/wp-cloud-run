<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Sitemap Options', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p><?php esc_html_e( 'This Options will be used for generating your Sitemap.', 'xml-sitemap-generator-for-google' ); ?></p>
		<table class="wp-list-table widefat fixed striped">
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

			if ( ! empty( $args['taxonomies'] ) ) {
				foreach ( $args['taxonomies'] as $taxonomy ) {
					Dashboard::render_post_row( $taxonomy->label, $taxonomy->name, $settings->taxonomies[ $taxonomy->name ] );
				}
			}
			?>
			</tbody>
		</table>

		<?php if ( ! empty( $args['cpt'] ) ) { ?>
			<h3 class="hndle"><?php esc_html_e( 'Custom Post Types', 'xml-sitemap-generator-for-google' ); ?></h3>
			<table class="wp-list-table widefat fixed striped tags">
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
		<?php } ?>
	</div>
</div>
