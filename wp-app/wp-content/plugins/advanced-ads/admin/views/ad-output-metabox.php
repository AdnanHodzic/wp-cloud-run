<?php
/**
 * Render Layout/Output meta box on ad edit screen.
 *
 * @var bool   $has_position       true if the position option is set.
 * @var bool   $has_clearfix       true if the clearfix option is enabled.
 * @var string $margin             value for margin option.
 * @var string $wrapper_id         value for wrapper ID option.
 * @var bool   $debug_mode_enabled true if the ad debug mode option is enabled.
 * @var string $positioning        The positioning view.
 */
?>
<div class="advads-ad-positioning">
	<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped template string.
		echo $positioning;
	?>
</div>

<div class="advads-option-list">
	<hr class="advads-hide-in-wizard"/>
	<label class='label advads-hide-in-wizard' for="advads-output-wrapper-id"><?php esc_html_e( 'container ID', 'advanced-ads' ); ?></label>
	<div class="advads-hide-in-wizard">
	<input type="text" id="advads-output-wrapper-id" name="advanced_ad[output][wrapper-id]" value="<?php echo esc_attr( $wrapper_id ); ?>"/>
	<span class="advads-help">
		<span class="advads-tooltip">
		<?php esc_html_e( 'Specify the id of the ad container. Leave blank for random or no id.', 'advanced-ads' ); ?>
		<?php esc_html_e( 'An id-like string with only letters in lower case, numbers, and hyphens.', 'advanced-ads' ); ?>
		</span>
	</span>
	&nbsp;<p class="advads-notice-inline advads-error advads-output-wrapper-id-error hidden"><?php esc_attr_e( 'An id-like string with only letters in lower case, numbers, and hyphens.', 'advanced-ads' ); ?></p>
	</div>
	<hr  class="advads-hide-in-wizard"/>
	<label class='label advads-hide-in-wizard' for="advads-output-wrapper-class"><?php esc_html_e( 'container classes', 'advanced-ads' ); ?></label>
	<div class="advads-hide-in-wizard">
	<input type="text" id="advads-output-wrapper-class" name="advanced_ad[output][wrapper-class]" value="<?php echo esc_attr( $wrapper_class ); ?>"/>
		<span class="advads-help"><span class="advads-tooltip"><?php esc_html_e( 'Specify one or more classes for the container. Separate multiple classes with a space', 'advanced-ads' ); ?>.</span></span>
	</div>
	<hr class="advads-hide-in-wizard"/>
	<label for="advads-output-debugmode" class="label advads-hide-in-wizard"><?php esc_html_e( 'Enable debug mode', 'advanced-ads' ); ?></label>
	<div class="advads-hide-in-wizard">
	<input id="advads-output-debugmode" type="checkbox" name="advanced_ad[output][debugmode]" value="1" <?php checked( $debug_mode_enabled, true ); ?>/>
	<a href="https://wpadvancedads.com/manual/ad-debug-mode/?utm_source=advanced-ads&utm_medium=link&utm_campaign=ad-debug-mode" target="_blank" class="advads-manual-link"><?php esc_html_e( 'Manual', 'advanced-ads' ); ?></a>
	</div>

	<?php if ( ! defined( 'AAP_VERSION' ) ) : ?>
		<hr class="advads-hide-in-wizard"/>
		<label class="label advads-hide-in-wizard"><?php esc_html_e( 'Display only once', 'advanced-ads' ); ?></label>
		<div class="advads-hide-in-wizard">
			<?php
			esc_html_e( 'Display the ad only once per page', 'advanced-ads' );
			?>
			<p>
				<?php
				Advanced_Ads_Admin_Upgrades::pro_feature_link( 'upgrade-pro-display-only-once' );
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
			Advanced_Ads_Admin_Upgrades::pro_feature_link( 'upgrade-pro-custom-code' );
			?>
				</p>		</div>
	<?php endif; ?>

	<?php do_action( 'advanced-ads-output-metabox-after', $ad ); ?>

</div>
