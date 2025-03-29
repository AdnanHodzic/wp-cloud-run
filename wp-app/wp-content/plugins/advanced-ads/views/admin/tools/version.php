<?php
/**
 * Render version management page
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

use AdvancedAds\Admin\Version_Control;

$versions = get_transient( Version_Control::VERSIONS_TRANSIENT );
?>
<div class="wp-header-end hidden"></div>
<h3>
	<?php esc_html_e( 'Rollback to Previous Version', 'advanced-ads' ); ?>
</h3>
<p>
	<?php
	printf(
	/* translators: %s: current version */
		esc_html__( 'Experiencing an issue with Advanced Ads version %s? Rollback to a previous version before the issue appeared.', 'advanced-ads' ),
		esc_attr( wp_advads()->get_version() )
	)
	?>
</p>
<form method="post" action="" id="alternative-version">
	<input type="hidden" name="nonce" id="version-control-nonce" value="<?php echo esc_attr( wp_create_nonce( 'advads-version-control' ) ); ?>"/>
	<table class="form-table">
		<tbody>
		<tr>
			<th scope="row"><?php esc_html_e( 'Available versions', 'advanced-ads' ); ?></th>
			<td>
				<p>
					<label>
						<select name="version" id="plugin-version"<?php echo ! $versions ? ' disabled' : ''; ?> class="!border">
							<?php if ( ! $versions ) : ?>
								<option value="">--<?php esc_html_e( 'Fetching versions', 'advanced-ads' ); ?>--</option>
							<?php else : ?>
								<?php foreach ( $versions['order'] as $index => $version ) : ?>
									<option value="<?php echo esc_attr( $version . '|' . $versions['versions'][ $version ] ); ?>"<?php selected( $index, 0 ); ?>><?php echo esc_html( $version ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</label>
					<button type="submit" id="install-version" class="button button-primary"<?php echo ! $versions ? ' disabled' : ''; ?>><?php esc_html_e( 'Reinstall', 'advanced-ads' ); ?></button>
					<span class="spinner"></span>
				</p>
				<p class="text-sm italic text-red-600"><?php esc_html_e( 'Warning: It is advised that you backup your database before installing another version.', 'advanced-ads' ); ?></p>
			</td>
		</tr>
		</tbody>
	</table>
</form>
