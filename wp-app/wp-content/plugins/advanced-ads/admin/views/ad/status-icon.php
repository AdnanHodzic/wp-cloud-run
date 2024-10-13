<?php
/**
 * Render the tooltip for ad status on the group overview page.
 *
 * @var string[] $status_strings
 * @var string   $status_type
 */
?>
<span class="advads-help advads-help-no-icon advads-ad-status-icon advads-ad-status-icon-<?php echo esc_attr( $status_type ); ?>">
	<?php if ( $status_strings ) : ?>
		<span class="advads-tooltip">
		<?php
			echo wp_kses( implode( '<br/>', $status_strings ), [ 'br' => [] ] );
		?>
		</span>
	<?php endif; ?>
</span>
