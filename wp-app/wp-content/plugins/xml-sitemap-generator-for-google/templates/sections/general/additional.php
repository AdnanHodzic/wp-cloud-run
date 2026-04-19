<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;
use GRIM_SG\PTSettings;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section">
	<h3 class="grim-section-title" data-search-id="additional_urls"><?php esc_html_e( 'Additional URLs', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20 line-height-2">
			<?php esc_html_e( 'External URLs which should be included in your Sitemap:', 'xml-sitemap-generator-for-google' ); ?>
			<br>
			<strong><?php esc_html_e( 'URL to External Page:', 'xml-sitemap-generator-for-google' ); ?></strong>
			<?php esc_html_e( 'Enter the URL to the External Page. For example: https://example.com/blog or www.example.com/account.', 'xml-sitemap-generator-for-google' ); ?>
			<br>
			<strong><?php esc_html_e( 'Last Modified:', 'xml-sitemap-generator-for-google' ); ?></strong>
			<i><?php esc_html_e( '(Optional)', 'xml-sitemap-generator-for-google' ); ?></i>
			<?php esc_html_e( 'You can select the Last Modified datetime for the URL or leave it empty (default) to always show the Current Datetime on Sitemap.', 'xml-sitemap-generator-for-google' ); ?>
		</p>
		<table class="grim-table grim-mb-20 wp-list-table widefat striped additional_urls grim-additional-urls" cellpadding="3" cellspacing="3">
			<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'URL to External Page', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Priority', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Update Frequency', 'xml-sitemap-generator-for-google' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Last Modified', 'xml-sitemap-generator-for-google' ); ?></th>
			</tr>
			</thead>
			<tbody id="additional_urls">
			<?php if ( count( $settings->additional_pages ) === 0 ) { ?>
				<tr class="no_urls">
					<td colspan="5" align="center"><?php esc_html_e( 'No URLs added yet!', 'xml-sitemap-generator-for-google' ); ?></td>
				</tr>
				<?php
			} else {
				foreach ( $settings->additional_pages as $additional_page ) {
					?>
					<tr>
						<td><input type="text" name="additional_urls[]" class="grim-input" value="<?php echo esc_attr( $additional_page['url'] ); ?>"></td>
						<td><?php Dashboard::render_priority_field( 'additional_priorities[]', $additional_page['priority'] ); ?></td>
						<td><?php Dashboard::render_frequency_field( 'additional_frequencies[]', $additional_page['frequency'] ); ?></td>
						<td><input type="datetime-local" name="additional_lastmods[]" class="grim-input" value="<?php echo esc_attr( $additional_page['lastmod'] ?? '' ); ?>"></td>
						<td><a href="#" class="remove_url"><i class="grim-icon-trash"></i></a></td>
					</tr>
					<?php
				}
			}
			?>
			</tbody>
		</table>
		<div class="general-settings-actions">
			<a href="#" id="add_new_url" class="grim-button white">
				<span>
					<i class="grim-icon-plus"></i>
					<?php esc_html_e( 'Add New URL', 'xml-sitemap-generator-for-google' ); ?>
				</span>
			</a>
			<a href="#" id="add_bulk_urls" class="grim-button white">
				<span>
					<i class="grim-icon-plus"></i>
					<?php esc_html_e( 'Add Bulk URLs', 'xml-sitemap-generator-for-google' ); ?>
				</span>
			</a>
			<a href="#" class="grim-button grim-mt-10 secondary grim-additional-urls-toggle">
				<span>Show More</span>
			</a>
		</div>

		<div class="add-bulk-urls-section hidden">
			<label for="bulk_urls"><?php esc_html_e( 'Enter URLs separated by line:', 'xml-sitemap-generator-for-google' ); ?></label>
			<textarea id="bulk_urls" name="bulk_urls" rows="5" cols="70"></textarea>
			<div class="general-settings-actions">
				<a href="#" id="cancel_add_bulk_urls" class="grim-button white">
					<span><?php esc_html_e( 'Cancel', 'xml-sitemap-generator-for-google' ); ?></span>
				</a>
				<a href="#" id="run_add_bulk_urls" class="grim-button secondary">
					<span><?php esc_html_e( 'Add', 'xml-sitemap-generator-for-google' ); ?></span>
				</a>
			</div>
		</div>
	</div>
</div>
<div class="hidden-area">
	<div id="additional_priorities_selector">
		<?php Dashboard::render_priority_field( 'additional_priorities[]', 3 ); ?>
	</div>
	<div id="additional_frequencies_selector">
		<?php Dashboard::render_frequency_field( 'additional_frequencies[]', PTSettings::$WEEKLY ); ?>
	</div>
</div>