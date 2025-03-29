<?php
/**
 * Render content of the Ad Schedule column in the ad overview list
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var string   $html_classes   Additonal values for class attribute.
 * @var string[] $status_strings Status string.
 * @var string   $content_after  HTML to load after the schedule content.
 */

?>
<fieldset class="inline-edit-col-left">
	<div class="inline-edit-col <?php echo esc_attr( $html_classes ); ?>">
		<p>
			<?php echo wp_kses( implode( '<br/>', $status_strings ), [ 'br' => [] ] ); ?>
		</p>
		<?php echo $content_after; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</fieldset>
