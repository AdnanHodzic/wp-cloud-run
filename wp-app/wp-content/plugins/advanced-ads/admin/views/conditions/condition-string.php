<input type="hidden" name="<?php echo esc_attr( $name ); ?>[type]" value="<?php echo esc_attr( $options['type'] ); ?>"/>
<div class="advads-condition-line-wrap">
	<?php include ADVADS_ABSPATH . 'admin/views/ad-conditions-string-operators.php'; ?>
	<input type="text" name="<?php echo esc_attr( $name ); ?>[value]" value="<?php echo esc_attr( $value ); ?>"/>
</div>
<p class="description">
	<?php echo esc_html( $type_options[ $options['type'] ]['description'] ); ?>
	<?php if ( isset( $type_options[ $options['type'] ]['helplink'] ) ) : ?>
		<a href="<?php echo esc_url( $type_options[ $options['type'] ]['helplink'] ); ?>" class="advads-manual-link" target="_blank"><?php esc_html_e( 'Manual', 'advanced-ads' ); ?></a>
	<?php endif; ?>
</p>
