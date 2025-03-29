<?php
/**
 * Quick edit fields
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0
 *
 * @var array $privacy_options privacy module options.
 */

use AdvancedAds\Options;

global $wp_locale;

?>
<fieldset class="inline-edit-col-right advads-quick-edit" disabled>
	<div class="inline-edit-col">
		<div class="wp-clearfix">
			<label><input value="1" type="checkbox" name="debugmode"><?php esc_html_e( 'Debug mode', 'advanced-ads' ); ?></label>
		</div>

		<div class="wp-clearfix">
			<label><input type="checkbox" name="enable_expiry" value="1"/><?php esc_html_e( 'Set expiry date', 'advanced-ads' ); ?></label>
			<div class="expiry-inputs advads-datetime">
				<?php \AdvancedAds\Admin\Quick_Bulk_Edit::print_date_time_inputs(); ?>
			</div>
		</div>

		<?php if ( isset( $privacy_options['enabled'] ) ) : ?>
			<div class="wp-clearfix">
				<label><input type="checkbox" name="ignore_privacy" value="1"/><?php esc_html_e( 'Ignore privacy settings', 'advanced-ads' ); ?></label>
			</div>
		<?php endif; ?>

		<div class="wp-clearfix">
			<label>
				<span class="title"><?php esc_html_e( 'Ad label', 'advanced-ads' ); ?></span>
				<input type="text" name="ad_label" value="" <?php echo Options::instance()->get( 'advanced-ads.custom-label.enabled' ) ? '' : 'disabled'; ?>>
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
			</label>
		</div>
	</div>
</fieldset>
