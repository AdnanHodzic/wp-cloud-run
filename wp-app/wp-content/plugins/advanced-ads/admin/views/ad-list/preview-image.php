<?php
/**
 * Render preview information for the image ad type
 *
 * @var string $src              image source URL.
 * @var string $alt              alt attribute value.
 * @var string $preview_hwstring width and height information for the smaller preview icon.
 * @var string $tooltip_hwstring width and height information for the larger version in the tooltip.
 */
?>
<span class="advads-ad-list-tooltip">
	<span class="advads-ad-list-tooltip-content">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $hwstring is not something we can escape.
		printf( '<img src="%s" alt="%s" %s/>', esc_url( $src ), esc_attr( $alt ), $tooltip_hwstring );
		?>
	</span>
<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $hwstring is not something we can escape.
	printf( '<img src="%s" alt="%s" %s/>', esc_url( $src ), esc_attr( $alt ), $preview_hwstring );
?>
</span>
