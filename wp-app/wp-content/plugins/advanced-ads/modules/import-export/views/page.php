<?php // phpcs:ignoreFile
/**
 * The view for the import & export page
 */

class_exists( 'Advanced_Ads', false ) || exit();

?><div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<?php
	foreach( $messages as $_message ) : ?>
		<div class="<?php echo $_message[0] === 'error' ? 'error' : 'updated'; ?>"><p><?php echo $_message[1]; ?></p></div>
	<?php endforeach; ?>


	<h2><?php _e( 'Export', 'advanced-ads' ); ?></h2>
	<p><?php _e( 'When you click the button below Advanced Ads will create an XML file for you to save to your computer.', 'advanced-ads' ); ?></p>

	<form method="post" action="">
		<fieldset>
			<input type="hidden" name="action" value="export" />
			<?php wp_nonce_field( 'advads-export' ); ?>
			<p><label><input type="checkbox" name="content[]" value="ads" checked="checked" /> <?php esc_html_e( 'Ads', 'advanced-ads' ); ?></label></p>
			<p><label><input type="checkbox" name="content[]" value="groups" checked="checked" /> <?php esc_html_e( 'Groups', 'advanced-ads' ); ?></label></p>
			<p><label><input type="checkbox" name="content[]" value="placements" checked="checked" /> <?php esc_html_e( 'Placements', 'advanced-ads' ); ?></label></p>
			<p><label><input type="checkbox" name="content[]" value="options" /> <?php esc_html_e( 'Options', 'advanced-ads' ); ?></label></p>
		</fieldset>
		<?php submit_button( __( 'Download Export File', 'advanced-ads' ) ); ?>
	</form>



	<h2><?php _e( 'Import', 'advanced-ads' ); ?></h2>
	<?php
	// filter the maximum allowed upload size for import files
	$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
	$size = size_format( $bytes );
	$upload_dir = wp_upload_dir();
	?>

	<form enctype="multipart/form-data" id="import-upload-form" method="post" action="">
		<?php wp_nonce_field( 'advads-import' ); ?>
		<fieldset>
			<p><label><input class="advads_import_type" type="radio" name="import_type" value="xml_file" checked="checked" /> <?php _e( 'Choose an XML file', 'advanced-ads' ); ?></label></p>
			<p><label><input class="advads_import_type" type="radio" name="import_type" value="xml_content" /> <?php _e( 'Copy an XML content', 'advanced-ads' ); ?></label></p>
		</fieldset>

		<div id="advads_xml_file">
			<?php
			if ( ! empty( $upload_dir['error'] ) ) : ?>
				<p class="advads-notice-inline advads-error">
					<?php _e( 'Before you can upload your import file, you will need to fix the following error:', 'advanced-ads' ); ?>
					<strong><?php echo $upload_dir['error']; ?>guu</strong>
				</p>
			<?php else: ?>
				<p>
					<input type="file" id="upload" name="import" size="25" /> (<?php /* translators: %s maximum size allowed */ printf( __( 'Maximum size: %s', 'advanced-ads' ), $size ); ?>)
					<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
				</p>
			<?php endif; ?>
		</div>
		<div id="advads_xml_content" style="display:none;">
			<p><textarea id="xml_textarea" name="xml_textarea" rows="10" cols="20" class="large-text code"></textarea></p>
		</div>
		<?php submit_button( __( 'Start import', 'advanced-ads' ), 'primary' ); ?>
	</form>

</div>






