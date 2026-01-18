<?php
/**
 * Bulk edit fields
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0
 *
 * @var array $privacy_options privacy module options.
 */

use AdvancedAds\Admin\Quick_Bulk_Edit;
use AdvancedAds\Options;

global $wp_locale;

?>
<fieldset class="inline-edit-col-right advads-bulk-edit">
	<div class="advads-bulk-edit-grid">
		<label>
			<span class="title"><?php esc_html_e( 'Debug mode', 'advanced-ads' ); ?></span>
			<select name="debug_mode">
				<option value="">— <?php esc_html_e( 'No Change', 'advanced-ads' ); ?> —</option>
				<option value="on"><?php esc_html_e( 'Enabled', 'advanced-ads' ); ?></option>
				<option value="off"><?php esc_html_e( 'Disabled', 'advanced-ads' ); ?></option>
			</select>
		</label>

		<label>
			<span class="title"><?php esc_html_e( 'Expiry date', 'advanced-ads' ); ?></span>
			<select name="expiry_date">
				<option value="">— <?php esc_html_e( 'No Change', 'advanced-ads' ); ?> —</option>
				<option value="on"><?php esc_html_e( 'Set', 'advanced-ads' ); ?></option>
				<option value="off"><?php esc_html_e( 'Unset', 'advanced-ads' ); ?></option>
			</select>
		</label>
		<div class="expiry-inputs advads-datetime">
			<?php Quick_Bulk_Edit::print_date_time_inputs(); ?>
		</div>

		<label>
			<span class="title"><?php esc_html_e( 'Ad label', 'advanced-ads' ); ?></span>
			<span>
			<input type="text" name="ad_label" value="" placeholder="— <?php esc_html_e( 'No Change', 'advanced-ads' ); ?> —" <?php echo Options::instance()->get( 'advanced-ads.custom-label.enabled' ) ? '' : 'disabled'; ?>>
				<?php if ( ! Options::instance()->get( 'advanced-ads.custom-label.enabled' ) ) : ?>
					<span class="advads-help">
					<span class="advads-tooltip">
					<?php
					printf(
					/* Translators: %s is the URL to the settings page. */
						esc_html__( 'Enable the Ad Label %1$s in the settings%2$s.', 'advanced-ads' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=advanced-ads-settings' ) ) . '" target="_blank">',
						'</a>'
					);
					?>
					</span>
				</span>
				<?php endif; ?>
			</span>

		</label>

		<?php if ( isset( $privacy_options['enabled'] ) ) : ?>
			<label>
				<span><?php esc_html_e( 'Ignore privacy settings', 'advanced-ads' ); ?></span>
				<select name="ignore_privacy">
					<option value="">— <?php esc_html_e( 'No Change', 'advanced-ads' ); ?> —</option>
					<option value="on"><?php esc_html_e( 'Enabled', 'advanced-ads' ); ?></option>
					<option value="off"><?php esc_html_e( 'Disabled', 'advanced-ads' ); ?></option>
				</select>
			</label>
		<?php endif; ?>
	</div>
</fieldset>
