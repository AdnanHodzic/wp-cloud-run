<?php
/**
 * @var $args
 */
?>
<div class="grim-select-label <?php echo esc_attr( $args['class'] ); ?>" data-search-id="<?php echo esc_attr( $args['name'] ?? '' ); ?>">
	<?php echo esc_html( $args['label'] ); ?>
</div>
<div class="grim-select <?php echo esc_attr( $args['select_size'] ?? 'grim-select-default' ); ?> <?php echo esc_attr( $args['class'] ); ?>" data-name="<?php echo esc_attr( $args['name'] ); ?>">
	<div class="grim-select__trigger">
		<span>
			<?php echo esc_html( $args['options'][$args['value']] ?? __( 'None', 'xml-sitemap-generator-for-google' ) ); ?>
		</span>
		<i class="grim-icon-chevron-down"></i>
	</div>
	<div class="grim-options">
		<div class="grim-option" data-value="">
			<?php esc_html_e( 'None', 'xml-sitemap-generator-for-google' ); ?>
		</div>
		<?php foreach ( $args['options'] as $value => $label ) { ?>
			<div class="grim-option <?php echo $args['value'] === $value ? 'selected' : ''; ?>" data-value="<?php echo esc_attr( $value ); ?>">
				<?php echo esc_html( $label ); ?>
			</div>
		<?php } ?>
	</div>
</div>

<select id="<?php echo esc_attr( $args['name'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" class="grim-hidden-select" hidden>
	<option value="" <?php selected( $args['value'], '' ); ?>><?php esc_html_e( 'None', 'xml-sitemap-generator-for-google' ); ?></option>
	<?php foreach ( $args['options'] as $value => $label ) { ?>
		<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value , $args['value'] ); ?>>
			<?php echo esc_html( $label ); ?>
		</option>
	<?php } ?>
</select>
