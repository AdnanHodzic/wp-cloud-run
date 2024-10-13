<?php
/**
 * Page/Post bulk edit fields
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0
 */

?>
<fieldset class="inline-edit-col-right">
	<table class="advads-bulk-edit-fields">
		<tr>
			<td>
				<span class="title"><?php esc_html_e( 'Disable ads', 'advanced-ads' ); ?></span></td>
			<td>
				<label>
					<select name="advads_disable_ads">
						<option value="">— <?php esc_html_e( 'No Change', 'advanced-ads' ); ?> —</option>
						<option value="on"><?php esc_html_e( 'Disable', 'advanced-ads' ); ?></option>
						<option value="off"><?php esc_html_e( 'Allow', 'advanced-ads' ); ?></option>
					</select>
				</label>
			</td>
		</tr>
		<?php if ( defined( 'AAP_VERSION' ) ) : ?>
			<tr>
				<td>
					<span class="title"><?php esc_html_e( 'Disable injection into the content', 'advanced-ads' ); ?></span></td>
				<td>
					<label>
						<select name="advads_disable_the_content">
							<option value="">— <?php esc_html_e( 'No Change', 'advanced-ads' ); ?> —</option>
							<option value="on"><?php esc_html_e( 'Disable', 'advanced-ads' ); ?></option>
							<option value="off"><?php esc_html_e( 'Allow', 'advanced-ads' ); ?></option>
						</select>
					</label>
				</td>
			</tr>
		<?php endif; ?>
	</table>
</fieldset>
