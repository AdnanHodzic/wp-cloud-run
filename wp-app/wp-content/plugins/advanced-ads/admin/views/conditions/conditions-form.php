<fieldset data-condition-list-target="<?php echo esc_attr( $list_target ); ?>"
		data-condition-form-name="<?php echo esc_attr( $form_name ); ?>"
		data-condition-action="<?php echo esc_attr( $action ); ?>"
		data-condition-connector-default="<?php echo esc_attr( $connector_default ); ?>"
	<?php
	if ( $empty_options ) :
		?> class="advads-hide-in-wizard"<?php
	endif;
	?>>
	<legend><?php esc_attr_e( 'New condition', 'advanced-ads' ); ?></legend>
	<input type="hidden" class="advads-conditions-index"
			value="<?php echo is_array( $set_conditions ) ? count( $set_conditions ) : 0; ?>"/>
	<div class="advads-conditions-new">
		<select>
			<option value=""><?php esc_attr_e( '-- choose a condition --', 'advanced-ads' ); ?></option>
			<?php foreach ( $conditions as $_condition_id => $_condition ) : ?>
				<?php if ( empty( $_condition['disabled'] ) ) : ?>
					<option value="<?php echo esc_attr( $_condition_id ); ?>"><?php echo esc_html( $_condition['label'] ); ?></option>
				<?php endif; ?>
			<?php
			endforeach;
			if ( isset( $pro_conditions ) && count( $pro_conditions ) ) :
				?>
				<optgroup label="<?php esc_attr_e( 'Add-On features', 'advanced-ads' ); ?>">
					<?php
					foreach ( $pro_conditions as $_pro_condition ) :
						?>
						<option disabled="disabled"><?php echo esc_html( $_pro_condition ); ?></option>
					<?php
					endforeach;
					?>
				</optgroup>
			<?php
			endif;
			?>
		</select>
		<span class="advads-loader" style="display: none;"></span>
	</div>
</fieldset>
