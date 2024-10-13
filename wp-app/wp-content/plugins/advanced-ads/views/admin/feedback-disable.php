<?php
/**
 * Plugin deactivation feedback popup.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

?>
<div id="advanced-ads-feedback-overlay" style="display: none;">
	<div id="advanced-ads-feedback-content">
		<span id="advanced-ads-feedback-overlay-close-button">&#x2715;</span>
		<form action="" method="post">
			<p>
				<strong><?php esc_html_e( 'Why did you decide to disable Advanced Ads?', 'advanced-ads' ); ?></strong>
			</p>
			<ul>
				<li class="advanced_ads_disable_help">
					<label>
						<input type="radio" name="advanced_ads_disable_reason" value="get help" checked="checked"/><?php esc_html_e( 'I have a problem, a question or need help.', 'advanced-ads' ); ?>
					</label>
				</li>
				<li>
					<textarea class="advanced_ads_disable_help_text" name="advanced_ads_disable_text[]" placeholder="<?php esc_attr_e( 'Please let us know how we can help', 'advanced-ads' ); ?>"></textarea>
				</li>
				<?php if ( $email ) : ?>
				<li class="advanced_ads_disable_reply">
					<label>
						<?php esc_html_e( 'Send me free help to ', 'advanced-ads' ); ?>
						<input type="email" name="advanced_ads_disable_reply_email" value="<?php echo esc_attr( $email ); ?>"/>
					</label>
				</li>
				<?php endif; ?>

				<li>
					<label>
						<input type="radio" name="advanced_ads_disable_reason" value="ads not showing up"/><?php esc_html_e( 'Ads are not showing up', 'advanced-ads' ); ?>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="advanced_ads_disable_reason" value="temporary"/><?php esc_html_e( 'It is only temporary', 'advanced-ads' ); ?>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="advanced_ads_disable_reason" value="missing feature"/><?php esc_html_e( 'I miss a feature', 'advanced-ads' ); ?>
					</label>
				</li>
				<li>
					<input type="text" name="advanced_ads_disable_text[]" value="" placeholder="<?php esc_html_e( 'Which one?', 'advanced-ads' ); ?>"/>
				</li>
				<li>
					<label>
						<input type="radio" name="advanced_ads_disable_reason" value="stopped showing ads"/><?php esc_html_e( 'I stopped using ads on my site.', 'advanced-ads' ); ?>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="advanced_ads_disable_reason" value="other plugin"/><?php esc_html_e( 'I switched to another plugin', 'advanced-ads' ); ?>
					</label>
				</li>
			</ul>
			<?php if ( $from ) : ?>
			<input type="hidden" name="advanced_ads_disable_from" value="<?php echo esc_attr( $from ); ?>"/>
			<?php endif; ?>
			<input class="advanced-ads-feedback-submit button button-primary" type="submit" name="advanced_ads_disable_submit" value="<?php esc_attr_e( 'Send feedback & deactivate', 'advanced-ads' ); ?>"/>
			<input class="advanced-ads-feedback-not-deactivate advanced-ads-feedback-submit button" type="submit" name="advanced_ads_keep_submit" value="<?php esc_attr_e( 'Send feedback', 'advanced-ads' ); ?>">
			<?php wp_nonce_field( 'advanced_ads_disable_form', 'advanced_ads_disable_form_nonce' ); ?>
			<a class="advanced-ads-feedback-only-deactivate" href="#"><?php esc_html_e( 'Only Deactivate', 'advanced-ads' ); ?></a>
		</form>
		<div id="advanced-ads-feedback-after-submit">
			<h2 id="advanced-ads-feedback-after-submit-waiting" style="display: none">
				<?php esc_html_e( 'Thanks for submitting your feedback. I will reply within 24 hours on working days.', 'advanced-ads' ); ?>
			</h2>
			<h2 id="advanced-ads-feedback-after-submit-goodbye" style="display: none">
			<?php
			printf(
				// translators: %s is the title of the website.
				wp_kses_post( 'All the best to you and <em>%s</em>.', 'advanced-ads' ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
			</h2>
			<p id="advanced-ads-feedback-after-submit-disabling-plugin" style="display: none">
				<?php esc_html_e( 'Disabling the plugin now…', 'advanced-ads' ); ?>
			</p>
		</div>
	</div>
</div>
