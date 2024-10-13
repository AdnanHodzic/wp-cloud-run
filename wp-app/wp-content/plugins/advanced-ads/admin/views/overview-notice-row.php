<?php
/**
 * Render a line in the notice meta box on the Advanced Ads overview page
 *
 * @var string $_notice_key index of the notice.
 * @var bool $is_hidden true if the notice is currently hidden
 * @var bool $can_hide true if the notice can be hidden
 * @var bool $hide true if the notice is hidden
 * @var string $date date string
 */
?>
<li class="advads-notice-inline" data-notice="<?php echo esc_attr( $_notice_key ); ?>" <?php echo $is_hidden ? 'style="display: none;"' : ''; ?>>
	<span>
	<?php
		// phpcs:ignore
		echo $text;
	?>
		</span>
	<?php if ( $can_hide ) : ?>
		<button type="button" class="advads-ad-health-notice-hide<?php echo ! $hide ? ' remove' : ''; ?>"><span class="dashicons dashicons-no-alt"></span></button>
	<?php endif; ?>
	<?php if ( $date ) : ?>
		<span class="date"><?php echo esc_attr( $date ); ?></span>
	<?php endif; ?>
</li>
