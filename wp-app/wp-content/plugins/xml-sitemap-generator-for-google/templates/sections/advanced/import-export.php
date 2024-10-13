<?php
/**
 * @var $args
 */
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Import Settings', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p class="import-alert"><?php esc_html_e( 'WARNING! This will overwrite all existing Settings, please proceed with caution or backup current Settings!', 'xml-sitemap-generator-for-google' ); ?></p>

		<p>
			<input type="file" name="import_file" accept=".json">
			<input type="hidden" name="import_settings" value="">
			<input type="submit" id="import-settings" class="button button-primary" value="<?php esc_html_e( 'Import Settings', 'xml-sitemap-generator-for-google' ); ?>">
			<br>
			<small><?php esc_html_e( 'Select the JSON file in order to Import Settings.', 'xml-sitemap-generator-for-google' ); ?></small>
		</p>
	</div>
</div>

<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Export Settings', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p><?php esc_html_e( 'Here you can download your current Settings. Keep this safe as you can use it as a backup if anything goes wrong.', 'xml-sitemap-generator-for-google' ); ?></p>

		<p>
			<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=export_sitemap_settings&nonce=' . wp_create_nonce( 'sgg_export_settings' ) ) ); ?>" class="button">
				<?php esc_html_e( 'Download Settings Data File', 'xml-sitemap-generator-for-google' ); ?>
			</a>
		</p>
	</div>
</div>
