<?php
/**
 * @var $args
 */
?>
<div class="grim-select grim-select-default" data-name="<?php echo esc_attr( $args['name'] ); ?>">
	<div class="grim-select__trigger">
		<span>
			<?php
			$value = $args['value'] ?? '0';
			echo esc_html( number_format( (int) $value / 10, 1 ) );
			?>
		</span>
		<i class="grim-icon-chevron-down"></i>
	</div>
	<div class="grim-options">
		<?php for ( $i = 0; $i <= 10; $i++ ) :
			$label = number_format( $i / 10, 1 );
			?>
			<div class="grim-option <?php echo (string) $value === (string) $i ? 'selected' : ''; ?>" data-value="<?php echo esc_attr( $i ); ?>">
				<?php echo esc_html( $label ); ?>
			</div>
		<?php endfor; ?>
	</div>
</div>

<select id="<?php echo esc_attr( $args['name'] ); ?>"
		name="<?php echo esc_attr( $args['name'] ); ?>"
		class="grim-hidden-select"
		hidden>
	<?php for ( $i = 0; $i <= 10; $i++ ) :
		$label = number_format( $i / 10, 1 );
		?>
		<option value="<?php echo esc_attr( $i ); ?>" <?php selected( (string) $value, (string) $i ); ?>>
			<?php echo esc_html( $label ); ?>
		</option>
	<?php endfor; ?>
</select>
