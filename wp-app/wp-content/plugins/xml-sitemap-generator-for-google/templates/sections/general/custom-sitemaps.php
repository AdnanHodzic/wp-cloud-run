<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;
use GRIM_SG\PTSettings;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section">
	<h3 class="grim-section-title sitemap-index-depended" data-search-id="custom_sitemaps"><?php esc_html_e( 'Custom Sitemaps', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20 sitemap-index-depended line-height-2">
			<?php esc_html_e( 'Here you can add Custom XML Sitemaps URLs (ex: Static Sitemaps, XML Sitmaps created by other services, etc.) to your ', 'xml-sitemap-generator-for-google' ); ?>
			<b><?php esc_html_e( 'Sitemap Index', 'xml-sitemap-generator-for-google' ); ?></b>.
			<br>
			<strong><?php esc_html_e( 'Custom XML Sitemap URL:', 'xml-sitemap-generator-for-google' ); ?></strong>
			<?php esc_html_e( 'Enter XML Sitemap URL to your Sitemap Index. For example: https://example.com/static-sitemap.xml', 'xml-sitemap-generator-for-google' ); ?>
			<br>
			<strong><?php esc_html_e( 'Last Modified:', 'xml-sitemap-generator-for-google' ); ?></strong>
			<i><?php esc_html_e( '(Optional)', 'xml-sitemap-generator-for-google' ); ?></i>
			<?php esc_html_e( 'You can select the Last Modified datetime for the Custom Sitemap or leave it empty (default) to always show the Current Datetime on Sitemap.', 'xml-sitemap-generator-for-google' ); ?>
		</p>
		<table class="grim-table grim-mb-20 wp-list-table widefat striped additional_urls sitemap-index-depended" cellpadding="3" cellspacing="3">
			<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Custom XML Sitemap URL', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Last Modified', 'xml-sitemap-generator-for-google' ); ?></th>
			</tr>
			</thead>
			<tbody id="custom_sitemaps">
			<?php if ( count( $settings->custom_sitemaps ) === 0 ) { ?>
				<tr class="no_urls">
					<td colspan="5" align="center"><?php esc_html_e( 'No Custom Sitemaps added!', 'xml-sitemap-generator-for-google' ); ?></td>
				</tr>
				<?php
			} else {
				foreach ( $settings->custom_sitemaps as $custom_sitemap ) {
					?>
					<tr>
						<td><input class="grim-input" type="text" name="custom_sitemap_urls[]" value="<?php echo esc_attr( $custom_sitemap['url'] ); ?>"></td>
						<td><input class="grim-input" type="datetime-local" name="custom_sitemap_lastmods[]" value="<?php echo esc_attr( $custom_sitemap['lastmod'] ?? '' ); ?>"></td>
						<td><a href="#" class="remove_url"><i class="grim-icon-trash"></i></a></td>
					</tr>
					<?php
				}
			}
			?>
			</tbody>
		</table>
		<div class="general-settings-actions sitemap-index-depended">
			<a href="#" id="add_sitemap_url" class="grim-button white">
				<span>
					<i class="grim-icon-plus"></i>
					<?php esc_html_e( 'Add Sitemap URL', 'xml-sitemap-generator-for-google' ); ?>
				</span>
			</a>
		</div>
	</div>
</div>
