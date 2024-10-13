<?php
/**
 * Template for allow unfiltered_html for user roles on multisites.
 *
 * @var WP_Role[] $user_roles_to_display Array of user roles that are allowed to edit ads.
 * @var array     $allowed_roles         Array of user role names that are allowed to use unfiltered_html on ads.
 */
?>
<fieldset>
	<legend><?php esc_html_e( 'Allow unfiltered HTML for these user roles:', 'advanced-ads' ); ?></legend>
	<?php foreach ( $user_roles_to_display as $user_role ) : ?>
		<p>
			<input
				type="checkbox"
				id="<?php echo esc_attr( $user_role->name ); ?>"
				name="<?php echo esc_attr( ADVADS_SLUG ); ?>[allow-unfiltered-html][]"
				value="<?php echo esc_attr( $user_role->name ); ?>"
				<?php checked( in_array( $user_role->name, $allowed_roles, true ) ); ?>
			>
			<label for="<?php echo esc_attr( $user_role->name ); ?>"><?php echo esc_html( $user_role->name ); ?></label>
		</p>
	<?php endforeach; ?>
</fieldset>
<p class="description">
	<?php esc_html_e( 'Enabling this option for untrusted users may cause them to publish malicious or poorly formatted code in their ads and can be a potential security risk. You should carefully consider which user roles you grant this capability.', 'advanced-ads' ); ?>
</p>
