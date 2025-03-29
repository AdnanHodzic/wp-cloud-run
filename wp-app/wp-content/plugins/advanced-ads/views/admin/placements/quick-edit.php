<?php
/**
 * Placement Quick Edit form
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */
?>
<fieldset class="inline-edit-col-left advanced-ads" disabled>
	<div class="inline-edit-col">
		<div class="inline-edit-group">
			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Status', 'advanced-ads' ); ?></span>
				<select name="status">
					<option value="publish"><?php esc_html_e( 'Published', 'advanced-ads' ); ?></option>
					<option value="draft"><?php esc_html_e( 'Draft', 'advanced-ads' ); ?></option>
				</select>
			</label>
		</div>
	</div>
</fieldset>
