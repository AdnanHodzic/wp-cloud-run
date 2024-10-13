<?php
/**
 * @var $args
 */
?>
<tr>
	<td class="<?php echo esc_attr( $args['class'] ?? '' ); ?>"><?php echo esc_html( $args['title'] ?? '' ); ?></td>
	<td>
		<select name="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo esc_attr( $args['class'] ?? '' ); ?>">
			<option value="1" <?php selected( $args['value'], '1' ); ?>><?php esc_html_e( 'Include', 'xml-sitemap-generator-for-google' ); ?></option>
			<option value="0" <?php selected( $args['value'], '0' ); selected( $args['value'], false ); ?>><?php esc_html_e( 'Exclude', 'xml-sitemap-generator-for-google' ); ?></option>
		</select>
	</td>
</tr>
