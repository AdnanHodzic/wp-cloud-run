<?php
/**
 * Render newsletter section
 *
 * @package AdvancedAds
 */

use AdvancedAds\Utilities\Conditional;

?>
<div class="advads-widget-wrapper">
	<div class="section-title">
		<h3><?php esc_attr_e( 'Newsletter & Email Courses', 'advanced-ads' ); ?></h3>
	</div>
	<p>
		<?php
		printf(
			/* translators: %1$s 'free' (in bold), %2$s '2 free add-ons' (in bold) */
			esc_html__(
				'Join our newsletter and take our %1$s ad monetization email courses. Get tutorials, optimization tips, and %2$s!',
				'advanced-ads'
			),
			'<strong>' . esc_html__( 'free', 'advanced-ads' ) . '</strong>',
			'<strong>' . esc_html__( '2 free add-ons', 'advanced-ads' ) . '</strong>'
		);
		?>
	</p>

	<div class="advads-widget-buttons space-y-4">
		<?php if ( Conditional::user_can_subscribe( 'nl_first_steps' ) ) : ?>
			<div class="advads-admin-notice" data-notice="nl_first_steps">
				<p class="m-0">
					<button type="button" class="button-primary advads-notices-button-subscribe with-icon" data-notice="nl_first_steps">
						<span class="dashicons dashicons-email-alt"></span>
						<?php esc_html_e( '5-part First Steps series', 'advanced-ads' ); ?>
					</button>
				</p>
			</div>
		<?php endif; ?>
		<?php if ( Conditional::user_can_subscribe( 'nl_adsense' ) ) : ?>
			<div class="advads-admin-notice" data-notice="nl_adsense">
				<p class="m-0">
					<button type="button" class="button-primary advads-notices-button-subscribe with-icon" data-notice="nl_adsense">
						<span class="dashicons dashicons-email-alt"></span>
						<?php esc_html_e( '8-part Google AdSense series', 'advanced-ads' ); ?>
					</button>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
