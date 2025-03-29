<?php
/**
 * Page/Post bulk edit fields
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0
 */

use AdvancedAds\Admin\Upgrades;

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
			<tr>
				<td>
					<span class="title"><?php esc_html_e( 'Disable injection into the content', 'advanced-ads' ); ?></span></td>
				<td>
					<label>
						<select name="advads_disable_the_content" <?php echo defined( 'AAP_VERSION' ) ? null : 'disabled'; ?>>
							<option value="">— <?php esc_html_e( 'No Change', 'advanced-ads' ); ?> —</option>
							<option value="on"><?php esc_html_e( 'Disable', 'advanced-ads' ); ?></option>
							<option value="off"><?php esc_html_e( 'Allow', 'advanced-ads' ); ?></option>
						</select>
						<?php
						if ( ! defined( 'AAP_VERSION' ) ) {
							Upgrades::upgrade_link( '', 'https://wpadvancedads.com/advanced-ads-pro/', 'upgrade-pro-disable-post-quick-edit' );
						}
						?>
					</label>
				</td>
			</tr>
	</table>
</fieldset>
