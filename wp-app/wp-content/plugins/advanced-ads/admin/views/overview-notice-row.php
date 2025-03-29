<?php
/**
 * Render a line in the notice meta box on the Advanced Ads overview page
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var string $type        type of the notice.
 * @var string $_notice_key index of the notice.
 * @var bool   $is_hidden   true if the notice is currently hidden.
 * @var bool   $can_hide    true if the notice can be hidden.
 * @var bool   $hide        true if the notice is hidden.
 * @var string $date        date string.
 * @var string $dashicon    the dashicons class to use.
 * @var string $text        the notice text.
 */

?>
<ul class="advads-ad-health-notices advads-ad-health-notices-<?php echo esc_attr( $type ); ?>">
	<li class="advads-notice-inline" data-notice="<?php echo esc_attr( $_notice_key ); ?>" <?php echo $is_hidden ? 'style="display: none;"' : ''; ?>>
		<span class="dashicons <?php echo esc_attr( $dashicon ); ?>"></span>
		<div class="text">
			<?php echo $text; // phpcs:ignore ?>
			<?php if ( $date ) : ?>
				<br>
				<small class="date">(<?php echo esc_attr( $date ); ?>)</small>
			<?php endif; ?>
		</div>
		<?php if ( $can_hide ) : ?>
			<button type="button" class="advads-ad-health-notice-hide <?php echo ! $hide ? 'remove' : ''; ?>">
				<span class="dashicons dashicons-dismiss"></span>
			</button>
		<?php endif; ?>
	</li>
</ul>
