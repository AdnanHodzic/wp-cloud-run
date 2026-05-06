<?php
/**
 * Free vs Pro comparison table.
 */
if ( sgg_pro_enabled() ) {
	return;
}

$segments = sgg_get_upgrade_segments( 'comparison-table' );
$main_cta = sgg_get_segment_cta( 'default', 'comparison-table-main' );
?>
<div class="grim-section grim-upgrade-comparison">
	<div class="grim-upgrade-comparison-header">
		<h3 class="grim-section-title"><?php esc_html_e( 'Free vs Pro', 'xml-sitemap-generator-for-google' ); ?></h3>
		<p class="grim-section-desc">
			<?php esc_html_e( 'Unlock advanced sitemap workflows for larger sites, client projects, and multilingual SEO.', 'xml-sitemap-generator-for-google' ); ?>
		</p>
	</div>

	<table class="grim-table wp-list-table widefat fixed striped grim-upgrade-comparison-table">
		<thead>
		<tr>
			<th scope="col" colspan="2"><?php esc_html_e( 'Feature', 'xml-sitemap-generator-for-google' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Free', 'xml-sitemap-generator-for-google' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Pro', 'xml-sitemap-generator-for-google' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td colspan="2"><?php esc_html_e( 'HTML Sitemap output and controls', 'xml-sitemap-generator-for-google' ); ?></td>
			<td><?php esc_html_e( 'Locked', 'xml-sitemap-generator-for-google' ); ?></td>
			<td><?php esc_html_e( 'Included', 'xml-sitemap-generator-for-google' ); ?></td>
		</tr>
		<tr>
			<td colspan="2"><?php esc_html_e( 'Google News advanced content filtering', 'xml-sitemap-generator-for-google' ); ?></td>
			<td><?php esc_html_e( 'Basic', 'xml-sitemap-generator-for-google' ); ?></td>
			<td><?php esc_html_e( 'Advanced', 'xml-sitemap-generator-for-google' ); ?></td>
		</tr>
		<tr>
			<td colspan="2"><?php esc_html_e( 'Image and Video sitemap premium providers', 'xml-sitemap-generator-for-google' ); ?></td>
			<td><?php esc_html_e( 'Limited', 'xml-sitemap-generator-for-google' ); ?></td>
			<td><?php esc_html_e( 'Extended', 'xml-sitemap-generator-for-google' ); ?></td>
		</tr>
		<tr>
			<td colspan="2"><?php esc_html_e( 'WooCommerce gallery image support', 'xml-sitemap-generator-for-google' ); ?></td>
			<td><?php esc_html_e( 'Locked', 'xml-sitemap-generator-for-google' ); ?></td>
			<td><?php esc_html_e( 'Included', 'xml-sitemap-generator-for-google' ); ?></td>
		</tr>
		<tr>
			<td colspan="2"><?php esc_html_e( 'Post-level priority, frequency, and exclusion controls', 'xml-sitemap-generator-for-google' ); ?></td>
			<td><?php esc_html_e( 'Locked', 'xml-sitemap-generator-for-google' ); ?></td>
			<td><?php esc_html_e( 'Included', 'xml-sitemap-generator-for-google' ); ?></td>
		</tr>
		<tr>
			<td colspan="2"><?php esc_html_e( 'Smart caching and performance options', 'xml-sitemap-generator-for-google' ); ?></td>
			<td><?php esc_html_e( 'Standard', 'xml-sitemap-generator-for-google' ); ?></td>
			<td><?php esc_html_e( 'Enhanced', 'xml-sitemap-generator-for-google' ); ?></td>
		</tr>
		</tbody>
	</table>

	<div class="grim-upgrade-comparison-actions">
		<a href="<?php echo esc_url( $main_cta['url'] ); ?>" class="grim-button secondary" target="_blank" rel="noopener noreferrer">
			<span><?php esc_html_e( 'Compare Plans & Upgrade', 'xml-sitemap-generator-for-google' ); ?></span>
		</a>
		<div class="grim-upgrade-comparison-segments">
			<?php foreach ( $segments as $segment ) : ?>
				<a href="<?php echo esc_url( $segment['url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $segment['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</div>
