<table id="<?php echo esc_attr( $list_target ); ?>" class="advads-conditions-table">
	<tbody>
	<?php
	$last_index = - 1;
	$i          = 0;
	if ( is_array( $set_conditions ) ) :
		foreach ( $set_conditions as $_index => $_options ) :
			$show_or_force_warning  = false;
			$show_is_not_or_warning = false;
			// get type attribute from previous option format.
			$_options['type'] = isset( $_options['type'] ) ? $_options['type'] : $_index;
			$connector        = ( ! isset( $_options['connector'] ) || 'or' !== $_options['connector'] ) ? 'and' : 'or';
			$operator         = ! isset( $_options['operator'] ) || $_options['operator'] !== 'is_not' ? 'is' : 'is_not';
			if ( isset( $_options['type'] ) && isset( $conditions[ $_options['type'] ]['metabox'] ) ) {
				$metabox = $conditions[ $_options['type'] ]['metabox'];
			} else {
				continue;
			}
			if ( method_exists( $metabox[0], $metabox[1] ) ) {
				/**
				 * Show warning for connector when
				 *  not set to OR already
				 *  this condition and the previous are on page level and not from the identical type
				 *  they are both set to SHOW
				 */
				$tax      = ( isset( $_options['type'] ) && isset( $conditions[ $_options['type'] ]['taxonomy'] ) ) ? $conditions[ $_options['type'] ]['taxonomy'] : false;
				$last_tax = ( isset( $set_conditions[ $last_index ]['type'] ) && isset( $conditions[ $set_conditions[ $last_index ]['type'] ]['taxonomy'] ) ) ? $conditions[ $set_conditions[ $last_index ]['type'] ]['taxonomy'] : false;
				if (
					$tax && $last_tax && $last_tax === $tax
					&& ( ! isset( $_options['connector'] ) || 'or' !== $_options['connector'] )
					&& 'is' === $operator && 'is' === $set_conditions[ $last_index ]['operator']
					&& $_options['type'] !== $set_conditions[ $last_index ]['type']
				) {
					$show_or_force_warning = true;
				}

				if (
					$operator === 'is_not'
					&& $connector === 'or'
					&& isset( $set_conditions[ $last_index ]['operator'] )
					&& $set_conditions[ $last_index ]['operator'] === 'is_not'
				) {
					$show_is_not_or_warning = true;
				}

				if ( $i > 0 ) :

					?>
				<tr class="advads-conditions-connector advads-conditions-connector-<?php echo esc_attr( $connector ); ?>">
					<td colspan="3">
						<?php
						echo Advanced_Ads_Display_Conditions::render_connector_option( $i, $connector, $form_name );
						if ( $show_or_force_warning || $show_is_not_or_warning ) {
							?>
							<p class="advads-notice-inline advads-error" style="display: block;">
								<?php
								if ( $show_or_force_warning ) {
									esc_attr_e( 'Forced to OR.', 'advanced-ads' );
									echo '&nbsp;<a target="_blank" href="https://wpadvancedads.com/manual/display-conditions#manual-combining-multiple-conditions">' . esc_attr__( 'manual', 'advanced-ads' ) . '</a>';
								} else {
									esc_attr_e( 'The ad might always show due to OR and "is not". Better use AND.', 'advanced-ads' );
									echo '&nbsp;<a target="_blank" href="https://wpadvancedads.com/manual/display-conditions/#Combining_conditions_with_AND_and_OR">' . esc_attr__( 'manual', 'advanced-ads' ) . '</a>';
								}
								?>
							</p>
							<?php

						}
						?>
					</td>
					</tr><?php endif; ?>
				<tr>
					<td class="advads-conditions-type"
						data-condition-type="<?php echo esc_attr( $_options['type'] ); ?>"><?php echo esc_html( $conditions[ $_options['type'] ]['label'] ); ?></td>
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
			$last_index = $_index;
		endforeach;
	endif;
	?>
	</tbody>
</table>
