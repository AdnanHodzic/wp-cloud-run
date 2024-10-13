<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;
?>
<tr>
	<td><?php echo esc_html( $args['title'] ?? '' ); ?></td>
	<td>
		<select name="<?php echo esc_attr( $args['option'] ); ?>_include">
			<option value="1" <?php selected( $args['data']->include, '1' ); ?>><?php esc_html_e( 'Include', 'xml-sitemap-generator-for-google' ); ?></option>
			<option value="0" <?php selected( $args['data']->include, '0' ); ?>><?php esc_html_e( 'Exclude', 'xml-sitemap-generator-for-google' ); ?></option>
		</select>
	</td>
	<td><?php Dashboard::render_priority_field( $args['option'] . '_priority', $args['data']->priority ); ?></td>
	<td><?php Dashboard::render_frequency_field( $args['option'] . '_frequency', $args['data']->frequency ); ?></td>
</tr>
