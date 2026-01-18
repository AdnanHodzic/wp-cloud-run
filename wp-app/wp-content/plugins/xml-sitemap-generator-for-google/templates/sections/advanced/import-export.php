<?php
/**
 * @var $args
 */
?>
<div class="grim-section">
	<h3 class="grim-section-title" data-search-id="advanced_import_settings"><?php esc_html_e( 'Import Settings', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<div class="grim-notice grim-notice-error grim-mb-20">
			<i class="grim-icon-information"></i>
			<p>
				<?php
				echo sprintf(
					esc_html__( 'WARNING: %s', 'xml-sitemap-generator-for-google' ),
					'<span>' . esc_html__( 'This will overwrite all existing Settings, please proceed with caution or backup current Settings!', 'xml-sitemap-generator-for-google' ) . '</span>'
				)
				?>
			</p>
		</div>
		<div class="grim-file-upload-wrapper">
			<div class="grim-import-label"><?php esc_html_e( 'Choose File', 'xml-sitemap-generator-for-google' ); ?></div>
			<div class="grim-import-file grim-button-section">
				<div class="grim-file-upload">
					<label class="grim-upload-btn" for="grim-import-file">
						<span class="grim-file-name" id="grim-file-name"><?php esc_html_e( 'No file chosen', 'xml-sitemap-generator-for-google' ); ?></span>
						<input type="file" id="grim-import-file" name="import_file" accept=".json">
						<input type="hidden" name="import_settings" value="">
					</label>
				</div>
				<button type="submit" id="import-settings" class="grim-button white">
				<span>
					<i class="grim-icon-upload"></i>
					<?php esc_html_e( 'Import Settings', 'xml-sitemap-generator-for-google' ); ?>
				</span>
				</button>
			</div>
			<p><?php esc_html_e( 'Select the JSON file in order to Import Settings.', 'xml-sitemap-generator-for-google' ); ?></p>
		</div>
	</div>
</div>

<div class="grim-section grim-button-section">
	<h3 class="grim-section-title" data-search-id="advanced_export_settings"><?php esc_html_e( 'Export Settings', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20"><?php esc_html_e( 'Here you can download your current Settings. Keep this safe as you can use it as a backup if anything goes wrong.', 'xml-sitemap-generator-for-google' ); ?></p>

		<p>
			<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=export_sitemap_settings&nonce=' . wp_create_nonce( 'sgg_export_settings' ) ) ); ?>" class="grim-button white">
				<span>
					<i class="grim-icon-download"></i>
					<?php esc_html_e( 'Download Settings Data File', 'xml-sitemap-generator-for-google' ); ?>
				</span>
			</a>
		</p>
	</div>
</div>
