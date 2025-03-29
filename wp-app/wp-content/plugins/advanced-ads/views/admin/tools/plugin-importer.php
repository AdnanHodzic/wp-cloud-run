<?php
/**
 * Render Importers
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Framework\Utilities\Params;

$bytes      = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
$size       = size_format( $bytes );
$upload_dir = wp_upload_dir();
?>
<div class="advads-plugin-importer mt-8">
	<header>
		<h2 class="advads-h2"><?php esc_html_e( 'Plugin Settings', 'advanced-ads' ); ?></h2>
		<p class="text-sm">
			<?php
				esc_html_e( 'Import or export your Advanced Ads settings, This option is useful for replicating the ads configuration across multiple websites.', 'advanced-ads' );
				WordPress::manual_link( 'https://wpadvancedads.com/manual/import-export/', 'tools-quicklinks' );
			?>

		</p>
	</header>
	<div class="advads-tab-container">
		<div class="advads-tab-menu">
			<a href="#import">
				<span class="dashicons dashicons-database-import"></span>
				<span><?php esc_html_e( 'Import Settings', 'advanced-ads' ); ?></span>
			</a>
			<a href="#export" class="is-active">
				<span class="dashicons dashicons-database-export"></span>
				<span><?php esc_html_e( 'Export Settings', 'advanced-ads' ); ?></span>
			</a>
		</div>
		<div class="advads-tab-content">
			<form id="import" class="advads-tab-target" enctype="multipart/form-data" method="post" action="<?php echo esc_url( Params::server( 'REQUEST_URI' ) ); ?>#import">
				<div class="advads-tab-content-body">
					<input type="hidden" name="action" value="advads_import">
					<input type="hidden" name="importer" value="xml">
					<?php wp_nonce_field( 'advads_import' ); ?>
					<p>
						<label>
							<input class="advads_import_type" type="radio" name="import_type" value="xml_file" checked="checked" />
							<?php esc_html_e( 'Choose an XML file', 'advanced-ads' ); ?>
						</label>
					</p>
					<p>
						<label>
							<input class="advads_import_type" type="radio" name="import_type" value="xml_content" />
							<?php esc_html_e( 'Copy an XML content', 'advanced-ads' ); ?>
						</label>
					</p>

					<div id="advads_xml_file">
						<?php if ( ! empty( $upload_dir['error'] ) ) : ?>
							<p class="advads-notice-inline advads-error">
								<?php esc_html_e( 'Before you can upload your import file, you will need to fix the following error:', 'advanced-ads' ); ?>
								<strong><?php echo $upload_dir['error']; // phpcs:ignore ?>guu</strong>
							</p>
						<?php else : ?>
							<p>
								<input type="file" id="upload" name="import" size="25" /> (<?php /* translators: %s maximum size allowed */ printf( __( 'Maximum size: %s', 'advanced-ads' ), $size ); // phpcs:ignore ?>)
								<input type="hidden" name="max_file_size" value="<?php echo $bytes; // phpcs:ignore ?>" />
							</p>
						<?php endif; ?>
					</div>
					<div id="advads_xml_content" style="display:none;">
						<p><textarea id="xml_textarea" name="xml_textarea" rows="10" cols="20" class="large-text code"></textarea></p>
						<?php WordPress::manual_link( 'https://wpadvancedads.com/manual/import-export/', 'tools-quicklinks', __( 'Ad Templates', 'advanced-ads' ) ); ?>
					</div>
				</div>
				<div class="advads-tab-content-footer">
					<button class="button button-primary button-large" type="submit">
						<?php esc_html_e( 'Start Import', 'advanced-ads' ); ?>
					</button>
				</div>
			</form>
			<form id="export" class="advads-tab-target" method="post" action="<?php echo esc_url( Params::server( 'REQUEST_URI' ) ); ?>#export">
				<div class="advads-tab-content-body">
					<input type="hidden" name="action" value="advads_export">
					<?php wp_nonce_field( 'advads_export' ); ?>
					<p class="text-sm"><?php esc_html_e( 'When you click the button below Advanced Ads will create an XML file for you to save to your computer.', 'advanced-ads' ); ?></p>
					<ul class="advads-checkbox-list mb-0">
						<li><label><input type="checkbox" name="content[]" value="ads" checked="checked" /> <?php esc_html_e( 'Ads', 'advanced-ads' ); ?></label></li>
						<li><label><input type="checkbox" name="content[]" value="groups" checked="checked" /> <?php esc_html_e( 'Groups', 'advanced-ads' ); ?></label></li>
						<li><label><input type="checkbox" name="content[]" value="placements" checked="checked" /> <?php esc_html_e( 'Placements', 'advanced-ads' ); ?></label></li>
						<li><label><input type="checkbox" name="content[]" value="options" /> <?php esc_html_e( 'Options', 'advanced-ads' ); ?></label></li>
					</ul>
				</div>
				<div class="advads-tab-content-footer">
					<button class="button button-primary button-large" type="submit">
						<?php esc_html_e( 'Download Export File', 'advanced-ads' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
