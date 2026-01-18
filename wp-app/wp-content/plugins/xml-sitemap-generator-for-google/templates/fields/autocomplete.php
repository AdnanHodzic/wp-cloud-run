<?php
/**
 * @var $args
 */
?>
<div>
	<input type="text" name="<?php echo esc_attr( $args['name'] ); ?>-autocomplete" class="sgg-autocomplete grim-input <?php echo esc_attr( $args['class'] ?? '' ); ?>" size="50"
		data-target="<?php echo esc_attr( $args['name'] ); ?>"
		data-type="<?php echo esc_attr( $args['type'] ?? '' ); ?>"
		placeholder="<?php echo esc_attr__( 'Type to Search...', 'xml-sitemap-generator-for-google' ); ?>">
	<input type="hidden" id="<?php echo esc_attr( $args['name'] ); ?>"
		name="<?php echo esc_attr( $args['name'] ); ?>"
		value="<?php echo esc_attr( stripslashes( $args['value'] ) ); ?>">

	<div class="expand <?php echo esc_attr( $args['class'] ?? '' ); ?>">
		<table class="grim-table wp-list-table widefat fixed striped">
			<tbody class="widefat striped sgg-autocomplete-terms">
			</tbody>
		</table>

		<a href="#" class="grim-button grim-mt-10 secondary grim-expand-toggle">
			<span>Show More</span>
		</a>
	</div>
</div>
