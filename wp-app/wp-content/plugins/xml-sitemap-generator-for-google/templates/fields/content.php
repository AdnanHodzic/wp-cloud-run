<?php
/**
 * @var $args
 */
?>
<tr>
	<td class="<?php echo esc_attr( $args['class'] ?? '' ); ?>">
		<?php echo esc_html( $args['title'] ?? '' ); ?>
	</td>
	<td>
		<div class="grim-select grim-select-default" data-name="<?php echo esc_attr( $args['name'] ); ?>">
			<div class="grim-select__trigger">
				<span>
					<?php
					if ( $args['value'] === '1' ) {
						esc_html_e( 'Include', 'xml-sitemap-generator-for-google' );
					} else {
						esc_html_e( 'Exclude', 'xml-sitemap-generator-for-google' );
					}
					?>
				</span>
				<i class="grim-icon-chevron-down"></i>
			</div>
			<div class="grim-options">
				<div class="grim-option <?php echo $args['value'] === '1' ? 'selected' : ''; ?>" data-value="1">
					<?php esc_html_e( 'Include', 'xml-sitemap-generator-for-google' ); ?>
				</div>
				<div class="grim-option <?php echo $args['value'] === '0' || $args['value'] === false ? 'selected' : ''; ?>" data-value="0">
					<?php esc_html_e( 'Exclude', 'xml-sitemap-generator-for-google' ); ?>
				</div>
			</div>
		</div>

		<!-- скрытый настоящий select -->
		<select id="<?php echo esc_attr( $args['name'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" class="grim-hidden-select" hidden>
			<option value="1" <?php selected( $args['value'], '1' ); ?>>
				<?php esc_html_e( 'Include', 'xml-sitemap-generator-for-google' ); ?>
			</option>
			<option value="0" <?php selected( $args['value'], '0' ); selected( $args['value'], false ); ?>>
				<?php esc_html_e( 'Exclude', 'xml-sitemap-generator-for-google' ); ?>
			</option>
		</select>
	</td>
</tr>
