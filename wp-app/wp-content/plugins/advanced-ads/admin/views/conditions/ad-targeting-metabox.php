<?php
/**
 * Render targeting meta box for Display and Visitor Conditions on ad edit page
 *
 * @package   Advanced_Ads_Admin
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, Advanced Ads GmbH
 *
 * @var array           $display_conditions           All set display conditions.
 * @var array           $visitor_conditions           All set visitor conditions.
 * @var boolean         $display_conditions_available True if there aren't any display conditions.
 * @var boolean         $visitor_conditions_available True if there aren't any visitor conditions.
 * @var Advanced_Ads_Ad $ad                           The current ad object.
 */
?>
<h3>
	<?php echo esc_html__( 'Display Conditions', 'advanced-ads' ); ?>
	<span class="advads-help">
	<span class="advads-tooltip">
		<?php esc_html_e( 'Limit the ad to pages that match the following conditions. Don‘t do anything here if the ad should appear everywhere you embed it.', 'advanced-ads' ); ?>
	</span>
</span>
</h3>
<?php if ( $display_conditions_available ) : ?>
	<div class="advads-show-in-wizard">
		<p><?php esc_html_e( 'Click on the button below if the ad should NOT show up on all pages when included automatically.', 'advanced-ads' ); ?></p>
		<button type="button" class="button button-secondary" id="advads-wizard-display-conditions-show"><?php esc_html_e( 'Hide the ad on some pages', 'advanced-ads' ); ?></button>
	</div>
<?php endif; ?>
<div id="advads-display-conditions" <?php echo $display_conditions_available ? 'class="advads-hide-in-wizard"' : ''; ?>>
	<?php Advanced_Ads_Display_Conditions::render_condition_list( $display_conditions, 'advads-ad-display-conditions' ); ?>
</div>
<?php do_action( 'advanced-ads-display-conditions-after', $ad ); ?>
<hr/>
<h3>
	<?php echo esc_html__( 'Visitor Conditions', 'advanced-ads' ); ?>
	<span class="advads-help">
		<span class="advads-tooltip">
			<?php esc_html_e( 'Target the ad to specific user groups that match the following conditions. Don‘t do anything here if all users should see the ad.', 'advanced-ads' ); ?>
		</span>
	</span>
</h3>
<?php if ( $visitor_conditions_available ) : ?>
	<div class="advads-show-in-wizard">
		<p><?php esc_html_e( 'Click on the button below if the ad should NOT be visible to all visitors', 'advanced-ads' ); ?></p>
		<button type="button" class="button button-secondary" id="advads-wizard-visitor-conditions-show"><?php esc_html_e( 'Hide the ad from some users', 'advanced-ads' ); ?></button>
	</div>
<?php endif; ?>
<div id="advads-visitor-conditions" <?php echo $visitor_conditions_available ? 'class="advads-hide-in-wizard"' : ''; ?>>
	<?php Advanced_Ads_Visitor_Conditions::render_condition_list( $visitor_conditions, 'advads-ad-visitor-conditions' ); ?>
</div>
<?php do_action( 'advanced-ads-visitor-conditions-after', $ad ); ?>
