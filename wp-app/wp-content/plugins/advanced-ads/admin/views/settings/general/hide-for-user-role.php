<div id="advads-settings-hide-by-user-role"><?php
foreach ( $roles as $_role => $_display_name ) :
	$checked = in_array( $_role, $hide_for_roles, true );
	?><label>
		<input type="checkbox" value="<?php echo esc_attr( $_role ); ?>" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[hide-for-user-role][]"
												 <?php
													checked( $checked, true );
													?>
		><?php echo esc_html( $_display_name ); ?></label>
	<?php
endforeach;
?>
</div>
<p class="description"><?php esc_html_e( 'Choose the roles a user must have in order to not see any ads.', 'advanced-ads' ); ?></p>
