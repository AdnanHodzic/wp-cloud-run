<?php
/**
 * Render Layout/Output meta box on ad edit screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 *
 * @var Ad $ad Ad instance.
 */

use AdvancedAds\Admin\Upgrades;
use AdvancedAds\Options;

?>
<div class="advads-ad-positioning">
	<?php echo ( new Advanced_Ads_Ad_Positioning( $ad ) )->return_admin_view(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>

<div class="advads-option-list">
	<hr class="advads-hide-in-wizard"/>

	<label class='label advads-hide-in-wizard' for="advads-output-wrapper-id">
		<?php esc_html_e( 'container ID', 'advanced-ads' ); ?>
	</label>

	<div class="advads-hide-in-wizard">
		<input type="text" id="advads-output-wrapper-id" name="advanced_ad[output][wrapper-id]" value="<?php echo esc_attr( $ad->get_wrapper_id() ); ?>"/>
		<span class="advads-help">
			<span class="advads-tooltip">
			<?php esc_html_e( 'Specify the id of the ad container. Leave blank for random or no id.', 'advanced-ads' ); ?>
			<?php esc_html_e( 'An id-like string with only letters in lower case, numbers, and hyphens.', 'advanced-ads' ); ?>
			</span>
		</span>
		&nbsp;<p class="advads-notice-inline advads-error advads-output-wrapper-id-error hidden"><?php esc_attr_e( 'An id-like string with only letters in lower case, numbers, and hyphens.', 'advanced-ads' ); ?></p>
	</div>

	<hr class="advads-hide-in-wizard"/>

	<label class='label advads-hide-in-wizard' for="advads-output-wrapper-class">
		<?php esc_html_e( 'container classes', 'advanced-ads' ); ?>
	</label>

	<div class="advads-hide-in-wizard">
		<input type="text" id="advads-output-wrapper-class" name="advanced_ad[output][wrapper-class]" value="<?php echo esc_attr( $ad->get_wrapper_class() ); ?>"/>
		<span class="advads-help"><span class="advads-tooltip"><?php esc_html_e( 'Specify one or more classes for the container. Separate multiple classes with a space', 'advanced-ads' ); ?>.</span></span>
	</div>

	<hr class="advads-hide-in-wizard"/>

	<label class="label advads-hide-in-wizard" for="advads-output-ad-label">
		<?php esc_html_e( 'Ad label', 'advanced-ads' ); ?>
	</label>
	<div class="advads-hide-in-wizard">
		<input type="text" id="advads-output-ad-label" name="advanced_ad[output][ad_label]" value="<?php echo esc_attr( $ad->get_prop( 'ad_label' ) ); ?>" <?php echo Options::instance()->get( 'advanced-ads.custom-label.enabled' ) ? '' : 'disabled'; ?>/>
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
	</div>
	<hr />

	<label for="advads-output-debugmode" class="label advads-hide-in-wizard">
		<?php esc_html_e( 'Enable debug mode', 'advanced-ads' ); ?>
	</label>

	<div class="advads-hide-in-wizard">
		<input type="hidden" name="advanced_ad[debugmode]" value="off">
		<input id="advads-output-debugmode" type="checkbox" name="advanced_ad[debugmode]" value="on" <?php checked( $ad->is_debug_mode() ); ?>/>
		<a href="https://wpadvancedads.com/manual/ad-debug-mode/?utm_source=advanced-ads&utm_medium=link&utm_campaign=ad-debug-mode" target="_blank" class="advads-manual-link"><?php esc_html_e( 'Manual', 'advanced-ads' ); ?></a>
	</div>

	<?php if ( ! defined( 'AAP_VERSION' ) ) : ?>
		<hr class="advads-hide-in-wizard"/>
		<label class="label advads-hide-in-wizard"><?php esc_html_e( 'Display only once', 'advanced-ads' ); ?></label>
		<div class="advads-hide-in-wizard">
			<?php esc_html_e( 'Display the ad only once per page', 'advanced-ads' ); ?>
			<p>
				<?php
				Upgrades::pro_feature_link( 'upgrade-pro-display-only-once' );
				?>
				</p>
		</div><hr class="advads-hide-in-wizard"/>
		<label class="label advads-hide-in-wizard"><?php esc_html_e( 'Custom Code', 'advanced-ads' ); ?></label>
		<div class="advads-hide-in-wizard">
			<?php
			esc_html_e( 'Place your own code below the ad', 'advanced-ads' );
			?>
			<p>
			<?php
			Upgrades::pro_feature_link( 'upgrade-pro-custom-code' );
			?>
				</p>		</div>
	<?php endif; ?>

	<?php do_action( 'advanced-ads-output-metabox-after', $ad ); ?>

</div>
