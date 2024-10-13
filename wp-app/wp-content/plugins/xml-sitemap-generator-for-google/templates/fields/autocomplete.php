<?php
/**
 * @var $args
 */
?>
<div>
	<label for="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo esc_attr( $args['class'] ?? '' ); ?>"><?php echo wp_kses( $args['label'], array( 'strong' => array() ) ); ?></label>
	<input type="text" class="sgg-autocomplete <?php echo esc_attr( $args['class'] ?? '' ); ?>" size="50"
			data-target="<?php echo esc_attr( $args['name'] ); ?>"
			data-type="<?php echo esc_attr( $args['type'] ?? '' ); ?>"
			placeholder="<?php echo esc_attr__( 'Type to Search...', 'xml-sitemap-generator-for-google' ); ?>">
	<input type="hidden" id="<?php echo esc_attr( $args['name'] ); ?>"
			name="<?php echo esc_attr( $args['name'] ); ?>"
			value="<?php echo esc_attr( stripslashes( $args['value'] ) ); ?>">

	<div class="expand">
		<ul class="widefat striped sgg-autocomplete-terms"></ul>
		<a href="#" class="expand-toggle button button-default">Show More &#9660;</a>
	</div>
</div>
