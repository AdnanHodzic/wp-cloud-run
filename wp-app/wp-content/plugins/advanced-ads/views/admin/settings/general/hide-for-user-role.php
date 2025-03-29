<?php
/**
 * The view to render the option.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 *
 * @var array $roles Array of user roles.
 * @var array $hide_for_roles Array of roles that should not see ads.
 */

?>
<div id="advads-settings-hide-by-user-role">
	<?php foreach ( $roles as $_role => $_display_name ) : ?>
		<label>
			<input type="checkbox" value="<?php echo esc_attr( $_role ); ?>" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[hide-for-user-role][]"<?php checked( in_array( $_role, $hide_for_roles, true ), true ); ?>>
			<?php echo esc_html( $_display_name ); ?>
		</label>
	<?php endforeach; ?>
</div>
<p class="description"><?php esc_html_e( 'Choose the roles a user must have in order to not see any ads.', 'advanced-ads' ); ?></p>
