<?php
/**
 * @var $args
 */
?>

<?php
$is_default = ! empty( $args['is_default'] );
$name       = esc_attr( $args['name'] );
$class      = esc_attr( $args['class'] ?? '' );
$value      = esc_attr( $args['value'] );
$label      = esc_html( $args['label'] );
?>

<label
	class="<?php echo ! $is_default ? esc_attr( "grim-toggle {$class}" ) : ''; ?>"
	for="<?php echo esc_attr( $name ); ?>">

	<input type="checkbox"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo esc_attr( $name ); ?>"
		value="1"
		class="<?php echo $is_default ? esc_attr( "grim-default-checkbox {$class}" ) : esc_attr( "grim-toggle-input {$class}" ); ?>"
		<?php
		checked( $value, '1' );
		if ( ! empty( $args['data'] ) ) {
			foreach ( $args['data'] as $attr => $val ) {
				echo " data-{$attr}='" . esc_attr( $val ) . "' ";
			}
		}
		?> />

	<?php if ( $is_default ) : ?>
		<?php echo esc_html( $label ); ?>
	<?php else : ?>
		<span class="grim-toggle-slider"></span>
		<span class="grim-toggle-label" data-search-id="<?php echo esc_attr( $name ); ?>">
			<?php echo esc_html( $label ); ?>
		</span>
	<?php endif; ?>
</label>
