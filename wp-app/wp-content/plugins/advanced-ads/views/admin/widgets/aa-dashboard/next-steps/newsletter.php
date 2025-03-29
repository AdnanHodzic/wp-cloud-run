<?php
/**
 * Render newsletter in next steps widget
 *
 * @package AdvancedAds
 */

use AdvancedAds\Utilities\Conditional;

?>
<div class="section-title !mt-0">
	<h3><?php esc_html_e( 'Newsletter & Email Courses', 'advanced-ads' ); ?></h3>
</div>

<p>
	<?php
	printf(
		/* translators: %s is 'free email courses'. */
		esc_html__( 'Join our %s for more benefits and insights:', 'advanced-ads' ),
		'<strong>' . esc_html__( 'free email courses', 'advanced-ads' ) . '</strong>'
	);
	?>
</p>
<ul class="list-disc list-outside pl-3 ml-2">
	<li>
		<?php
		printf(
			/* translators: %s is '2 free add-ons' in bold. */
			esc_html__( 'Gain %s', 'advanced-ads' ),
			'<strong>' . esc_html__( '2 free add-ons', 'advanced-ads' ) . '</strong>'
		);
		?>
	</li>
	<li><?php esc_html_e( 'Take the First Steps with Advanced Ads', 'advanced-ads' ); ?></li>
	<li><?php esc_html_e( 'Learn how to increase your Google AdSense earnings', 'advanced-ads' ); ?></li>
	<li><?php esc_html_e( 'Get periodic ad monetization tutorials', 'advanced-ads' ); ?></li>
</ul>

<div class="advads-multiple-subscribe">
	<fieldset class="space-y-2 mb-3">
		<?php
		if ( Conditional::user_can_subscribe( 'nl_first_steps' ) ) :
			?>
		<div>
			<input type="checkbox" name="advads-multiple-subscribe" id="advads-dashboard-subscribe-first-steps" value="nl_first_steps" />
			<label for="advads-dashboard-subscribe-first-steps">
				<?php esc_html_e( '5-part First Steps series', 'advanced-ads' ); ?>
			</label>
		</div>
		<?php endif; ?>
		<?php
		if ( Conditional::user_can_subscribe( 'nl_adsense' ) ) :
			?>
		<div>
			<input type="checkbox" name="advads-multiple-subscribe" id="advads-dashboard-subscribe-google-adsense" value="nl_adsense" />
			<label for="advads-dashboard-subscribe-google-adsense">
				<?php esc_html_e( '8-part Google AdSense series', 'advanced-ads' ); ?>
			</label>
		</div>
		<?php endif; ?>
		<?php
		if ( Conditional::user_can_subscribe( 'nl_first_steps' ) && Conditional::user_can_subscribe( 'nl_adsense' ) ) :
			?>
		<div>
			<input type="checkbox" id="advads-dashboard-subscribe-periodic-expert" checked disabled/>
			<label for="advads-dashboard-subscribe-periodic-expert">
				<?php esc_html_e( 'Periodic expert tutorials', 'advanced-ads' ); ?>
			</label>
		</div>
		<?php endif; ?>
	</fieldset>

	<button type="button" class="advads-btn_primary advads-multiple-subscribe_button">
		<span class="dashicons dashicons-email-alt"></span>
		<?php esc_html_e( 'Subscribe me now', 'advanced-ads' ); ?>
	</button>
</div>
