<table id="<?php echo esc_attr( $list_target ); ?>" class="advads-conditions-table">
	<tbody>
	<?php
	if ( isset( $set_conditions ) ) :
		$i = 0;
		foreach ( $set_conditions as $_options ) :
			if ( isset( $conditions[ $_options['type'] ]['metabox'] ) ) {
				$metabox = $conditions[ $_options['type'] ]['metabox'];
			} else {
				continue;
			}
			$connector = ( ! isset( $_options['connector'] ) || 'or' !== $_options['connector'] ) ? 'and' : 'or';
			if ( method_exists( $metabox[0], $metabox[1] ) ) {
				if ( $i > 0 ) :
					?>
					<tr class="advads-conditions-connector advads-conditions-connector-<?php echo esc_attr( $connector ); ?>">
						<td colspan="3">
							<?php
							echo Advanced_Ads_Visitor_Conditions::render_connector_option( $i, $connector, $form_name );
							?>
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<td class="advads-conditions-type"><?php echo esc_html( $conditions[ $_options['type'] ]['label'] ); ?></td>
					<td>
						<?php
						call_user_func( [ $metabox[0], $metabox[1] ], $_options, $i ++, $form_name );
						?>
					</td>
					<td>
						<button type="button" class="advads-conditions-remove button">x</button>
					</td>
				</tr>
				<?php
			}
		endforeach;
	endif;
	?>
	</tbody>
</table>
