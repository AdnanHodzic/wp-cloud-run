<?php
/**
 * Settings for the spacing
 *
 * @var array $spacings    array with values for top, right, bottom, left spacing pixel values.
 * @var bool  $is_centered Whether the "Block Center" position has been selected.
 */
?>
<hr class"advads-hide-in-wizard">

<div class="advads-ad-positioning-spacing advads-option-list">
	<span class="label"><?php esc_html_e( 'Margin', 'advanced-ads' ); ?></span>

	<div class="advads-ad-positioning-spacing-wrapper">
		<?php foreach ( $spacings as $direction => $spacing ) : ?>
			<?php $input_id = 'advads-ad-positioning-spacing-' . $direction; ?>

			<label for="<?php echo esc_attr( $input_id ); ?>">
				<span class="label screen-reader-text"><?php echo esc_html( $spacing['label'] ); ?></span>

				<input
					type="number"
					step="1"
					id="<?php echo esc_attr( $input_id ); ?>"
					class="advads-ad-positioning-spacing-option"
					name="advanced_ad[output][margin][<?php echo esc_attr( $direction ); ?>]"
					value="<?php echo esc_attr( $spacing['value'] ); ?>"
					<?php __checked_selected_helper( $is_centered && ( in_array( $direction, [ 'left', 'right' ], true ) ), true, true, 'readonly' ); ?>
				>
			</label>

			<div class="advads-ad-positioning-spacing-direction <?php echo esc_attr( $input_id ); ?>" aria-hidden="true">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- allow inline svg
				echo preg_replace( '/\s+/', ' ', file_get_contents( ADVADS_ABSPATH . 'modules/ad-positioning/assets/img/advads-bknd-ui-pos-margin.svg' ) );
				?>
			</div>
		<?php endforeach; ?>
		<div class="advads-ad-positioning-spacing-adcenter" aria-hidden="true">
			<?php esc_html_e( 'Ad', 'advanced-ads' ); ?>
		</div>
		<span class="advads-ad-positioning-spacing-legend"><?php echo esc_html_x( 'in px', 'Ad positioning spacing legend text', 'advanced-ads' ); ?></span>
	</div>
</div>
