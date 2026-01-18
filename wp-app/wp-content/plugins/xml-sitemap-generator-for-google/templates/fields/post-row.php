<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;
?>
<tr>
	<td><?php echo esc_html( $args['title'] ?? '' ); ?></td>
	<td>
		<div class="grim-select grim-select-default" data-name="<?php echo esc_attr( $args['option'] ); ?>_include">
			<div class="grim-select__trigger">
				<span>
					<?php
					if ( $args['data']->include === '1' ) {
						esc_html_e( 'Include', 'xml-sitemap-generator-for-google' );
					} else {
						esc_html_e( 'Exclude', 'xml-sitemap-generator-for-google' );
					}
					?>
				</span>
				<i class="grim-icon-chevron-down"></i>
			</div>
			<div class="grim-options">
				<div class="grim-option <?php echo $args['data']->include === '1' ? 'selected' : ''; ?>" data-value="1">
					<?php esc_html_e( 'Include', 'xml-sitemap-generator-for-google' ); ?>
				</div>
				<div class="grim-option <?php echo $args['data']->include === '0' ? 'selected' : ''; ?>" data-value="0">
					<?php esc_html_e( 'Exclude', 'xml-sitemap-generator-for-google' ); ?>
				</div>
			</div>
		</div>

		<select id="<?php echo esc_attr( $args['option'] ); ?>_include"
				name="<?php echo esc_attr( $args['option'] ); ?>_include"
				class="grim-hidden-select"
				hidden>
			<option value="1" <?php selected( $args['data']->include, '1' ); ?>>
				<?php esc_html_e( 'Include', 'xml-sitemap-generator-for-google' ); ?>
			</option>
			<option value="0" <?php selected( $args['data']->include, '0' ); ?>>
				<?php esc_html_e( 'Exclude', 'xml-sitemap-generator-for-google' ); ?>
			</option>
		</select>
	</td>
	<td><?php Dashboard::render_priority_field( $args['option'] . '_priority', $args['data']->priority ); ?></td>
	<td><?php Dashboard::render_frequency_field( $args['option'] . '_frequency', $args['data']->frequency ); ?></td>
</tr>
