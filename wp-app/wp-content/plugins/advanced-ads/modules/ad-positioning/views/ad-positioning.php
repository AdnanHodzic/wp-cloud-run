<?php

/**
 * Template for ad positioning.
 *
 * @var string $positioning         how should the ad be positioned.
 * @var array  $spacing             the spacing around the ad.
 * @var array  $positioning_options array of positioning options.
 */

?>
<div class="advads-ad-positioning-position advads-option-list">
	<span class="label"><?php esc_html_e( 'Text Flow', 'advanced-ads' ); ?></span>

	<div class="advads-ad-positioning-position-groups-wrapper">
		<?php foreach ( $positioning_options as $group_name => $group ) : ?>
			<div class="advads-ad-positioning-position-group">
				<h3 class="advads-ad-positioning-position-group-heading"><?php echo esc_html( $group['title'] ); ?></h3>

				<?php foreach ( $group['options'] as $option_name => $option ) : ?>
					<?php $input_id = 'advads-ad-positioning-position-' . $option_name; ?>
					<label
						class="advads-ad-positioning-position-wrapper<?php echo( $option_name === $positioning ? ' is-checked' : '' ); ?>"
						for="<?php echo esc_attr( $input_id ); ?>"
					>
						<input
							type="radio"
							class="advads-ad-positioning-position-option"
							name="advanced_ad[output][position]"
							id="<?php echo esc_attr( $input_id ); ?>"
							value="<?php echo esc_attr( $option_name ); ?>"
							<?php checked( $option_name, $positioning ); ?>
						>

						<div class="advads-ad-positioning-position-icon">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- allow inline svg
							echo preg_replace( '/\s+/', ' ', file_get_contents( sprintf( ADVADS_ABSPATH . 'modules/ad-positioning/assets/img/advads-bknd-ui-pos-%s.svg', esc_attr( isset( $option['img'] ) ? $option['img'] : $group_name ) ) ) );
							?>
						</div>
					</label>
				<?php endforeach; ?>
				<p class="advads-ad-positioning-position-group-description">
					<?php echo esc_html( $group['description'] ); ?>
				</p>
			</div>
		<?php endforeach; ?>
	</div>
</div>
