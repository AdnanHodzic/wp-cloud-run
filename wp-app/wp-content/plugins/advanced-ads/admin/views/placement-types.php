<?php

/**
 * Render all placement types for forms.
 *
 * @var array $placement_types
 */
?>
<?php if ( is_array( $placement_types ) ) : ?>
	<div class="advads-form-types advads-buttonset">
		<?php foreach ( $placement_types as $key => $placement_type ) : ?>
			<div class="advads-form-type">
				<label for="advads-form-type-<?php echo esc_attr( $key ); ?>">
					<?php if ( isset( $placement_type['image'] ) ) : ?>
						<img src="<?php echo esc_attr( $placement_type['image'] ); ?>" alt="<?php echo esc_attr( $placement_type['title'] ); ?>"/>
					<?php else : ?>
						<strong><?php echo esc_html( $placement_type['title'] ); ?></strong><br/>
						<p class="description"><?php echo esc_html( $placement_type['description'] ); ?></p>
					<?php endif; ?>
				</label>
				<input type="radio" id="advads-form-type-<?php echo esc_attr( $key ); ?>" name="advads[placement][type]" value="<?php echo esc_attr( $key ); ?>"/>
				<div class="advads-form-description">
					<h4><?php echo esc_html( $placement_type['title'] ); ?></h4>
					<?php echo esc_html( $placement_type['description'] ); ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
