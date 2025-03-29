<?php
/**
 * Render import history table
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 *
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
 */

$history = get_option( '_advads_importer_history', [] );
if ( empty( $history ) ) {
	return;
}
?>
<div id="import-history" class="advads-import-history mt-8">
	<header>
		<h2 class="advads-h2"><?php esc_html_e( 'Import History', 'advanced-ads' ); ?></h2>
		<p class="text-sm"><?php esc_html_e( 'Import history to rollback changes.', 'advanced-ads' ); ?></p>
	</header>

	<table class="widefat striped">
		<thead>
			<tr>
				<td>Importer</td>
				<td>Session Key</td>
				<td>Ads Created</td>
				<td>Create At</td>
				<td></td>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $history as $session_key => $session ) :
				$importer    = wp_advads()->importers->get_importer( $session['importer_id'] );
				$delete_link = wp_nonce_url(
					add_query_arg(
						[
							'action'      => 'advads_import_delete',
							'session_key' => $session['session_key'],
						]
					),
					'advads_import_delete'
				);
				?>
			<tr>
				<td><?php echo $importer->get_title(); ?></td>
				<td><?php echo $session['session_key']; ?></td>
				<td><?php echo $session['count']; ?></td>
				<td><?php echo wp_date( get_option( 'date_format' ), $session['created_at'] ); ?></td>
				<td>
					<a href="<?php echo esc_url( $delete_link ); ?>" class="button-link !text-red-500">Rollback Changes</a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
