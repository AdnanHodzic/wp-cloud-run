<?php
/**
 * Render Placement Bulk Edit Form
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0.0
 */

?>
<fieldset class="inline-edit-col-right advanced-ads advads-bulk-edit">
	<div class="inline-edit-col">
		<div class="wp-clearfix">
			<label>
				<span class="title"><?php esc_html_e( 'Ad Label', 'advanced-ads' ); ?></span>
				<select name="ad_label">
					<option value="">— <?php esc_html_e( 'No Change', 'advanced-ads' ); ?> —</option>
					<option value="default"><?php esc_html_e( 'Default', 'advanced-ads' ); ?></option>
					<option value="enabled"><?php esc_html_e( 'Enabled', 'advanced-ads' ); ?></option>
					<option value="disabled"><?php esc_html_e( 'Disabled', 'advanced-ads' ); ?></option>
				</select>
			</label>
		</div>
	</div>
</fieldset>
