<?php
/**
 * @var $args
 */
?>
<label for="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo esc_attr( $args['class'] ?? '' ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
<select id="<?php echo esc_attr( $args['name'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo esc_attr( $args['class'] ?? '' ); ?>">
	<option value="" <?php selected( $args['value'], '' ); ?> disabled>
		<?php esc_html_e( 'None', 'xml-sitemap-generator-for-google' ); ?>
	</option>
	<?php foreach ( $args['options'] as $value => $label ) { ?>
		<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value , $args['value'] ); ?>>
			<?php echo esc_html( $label ); ?>
		</option>
	<?php } ?>
</select>