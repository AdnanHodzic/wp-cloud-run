<?php
/**
 * AdSense ad units table.
 *
 * @var bool $closeable
 * @var bool $use_dashicons
 * @var Advanced_Ads_Network_Adsense $network
 * @var array $ad_units
 * @var bool $display_slot_id
 * @var string $pub_id
 *
 * @package AdvancedAds
 */

global $external_ad_unit_id, $closeable, $display_slot_id;

if ( ! isset( $hide_idle_ads ) ) {
	$hide_idle_ads = true;
}
if ( ! isset( $ad_units ) ) {
	$ad_units = [];
}

?>
<div id="mapi-wrap" class="aa-select-list">
    <?php if ($closeable): ?>
        <button type="button" id="mapi-close-selector" class="notice-dismiss"></button>
    <?php endif;?>
	<i id="mapi-archived-ads" title="<?php esc_attr_e( 'Hide archived ads', 'advanced-ads' ); ?>" data-alt-title="<?php esc_attr_e( 'Show archived ads', 'advanced-ads' ); ?>" class="dashicons dashicons-hidden"></i>
	<i class="aa-select-list-update dashicons dashicons-update mapiaction" data-mapiaction="updateList" style="color:#0085ba;cursor:pointer;font-size:20px;" title="<?php esc_attr_e( 'Update the ad units list', 'advanced-ads' ); ?>"></i>
    <div id="mapi-loading-overlay" class="aa-select-list-loading-overlay">
        <img alt="..." src="<?php echo ADVADS_BASE_URL . 'admin/assets/img/loader.gif'; ?>" style="margin-top:8em;" />
    </div>
    <div id="mapi-table-wrap" class="aa-select-list-table-wrap">
        <table class="widefat striped">
            <thead>
            <tr>
                <th><?php esc_html_e( 'Name', 'advanced-ads' ); ?></th>
                <?php if ($display_slot_id):?><th><?php echo esc_html_x( 'Slot ID', 'AdSense ad', 'advanced-ads' ); ?></th><?php endif;?>
                <th><?php echo esc_html_x( 'Type', 'AdSense ad', 'advanced-ads' ); ?></th>
                <th><?php esc_html_e( 'Size', 'advanced-ads' ); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php if ( empty( $ad_units ) ) : ?>
				<tr id="mapi-notice-noads">
					<td colspan="5" style="text-align:center;">
						<?php esc_attr_e( 'No ad units found', 'advanced-ads' ); ?>
						<button type="button" class="mapiaction icon-button" data-mapiaction="updateList">
							<?php esc_attr_e( 'Update the ad units list', 'advanced-ads' ); ?>
							<i class="dashicons dashicons-update" style="color:#0085ba;font-size:20px;"></i>
						</button>
					</td>
				</tr>
			<?php else :
				// Force a refresh the first time the ad list is opened after an update.
				echo ! isset( $ad_units[0]->raw['nameV2'] ) ? '<input type="hidden" id="mapi-force-v2-list-update" value="" />' : '';
				foreach ( $ad_units as $ad_unit ) {
					$ad_unit->is_supported = $network->is_supported( $ad_unit );
				}
				$sorted_adunits = Advanced_Ads_Ad_Network_Ad_Unit::sort_ad_units( $ad_units, $external_ad_unit_id );
				?>
				<?php foreach ( $sorted_adunits as $unit ) : ?>
				<tr <?php echo $unit->raw['status'] === 'ARCHIVED' ? 'data-archived="1"' : ''; ?> class="advads-clickable-row mapiaction" data-mapiaction="getCode" data-slotid="<?php echo esc_attr( $unit->id ); ?>" data-active="<?php echo esc_attr( $unit->active ); ?>">
					<td><?php echo esc_html( $unit->name ); ?></td>
					<?php if ( $display_slot_id ) : ?>
						<td class="unitcode">
							<?php
							echo '<span>' . esc_html( $unit->slot_id ) . '</span>';
							echo $unit->raw['status'] === 'ARCHIVED' ? '&nbsp;<code>' . esc_html__( 'Archived', 'advanced-ads' ) . '</code>' : '';
							?>
						</td>
					<?php endif; ?>
					<td class="unittype">
						<?php if ( $unit->is_supported ) : ?>
							<?php if ( ! empty( $unit->code ) ) : ?>
								<?php echo esc_attr( Advanced_Ads_AdSense_MAPI::format_ad_data( $unit, 'type' ) ); ?>
							<?php else : ?>
								<button type="button" class="button-secondary button-small" title="<?php esc_attr_e( 'Get the code for this ad', 'advanced-ads' ); ?>">
									<span style="line-height: 26px;" class="dashicons dashicons-update"></span> <?php esc_html_e( 'Load', 'advanced-ads' ); ?>
								</button>
							<?php endif; ?>
						<?php elseif ( empty( $unit->code ) ) : ?>
							<span class="dashicons dashicons-warning" title="<?php esc_attr_e( 'Ad can\'t be imported, click for details', 'advanced-ads' ); ?>"></span>
						<?php endif; ?>
					</td>
					<td class="unitsize">
						<?php echo esc_attr( Advanced_Ads_AdSense_MAPI::format_ad_data( $unit, 'size' ) ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php endif; ?>
            </tbody>
        </table>
    </div>
	<p class="advads-notice-inline advads-error" id="remote-ad-code-error" style="display:none;"><strong><?php esc_attr_e( 'Unrecognized ad code', 'advanced-ads' ); ?></strong></p>
	<p class="advads-error-message" id="remote-ad-code-msg"></p>
</div>

<div style="display:none;" id="remote-ad-unsupported-ad-type">
	<h3 class="advads-notice-inline advads-error">
		<?php esc_html_e( 'This ad type can currently not be imported from AdSense.', 'advanced-ads' ); ?>
	</h3>
	<p>
		<?php esc_html_e( 'You can proceed with one of the following solutions', 'advanced-ads' ); ?>:
	</p>
	<ul>
		<li>
			<?php
			/* Translators: 1: opening tag for AdSense account link 2: opening tag for a link to insert ad code 3: closing a tag  */
			printf( esc_html__( '%1$sCopy the code from your AdSense account%3$s and %2$sinsert a new AdSense code here%3$s.', 'advanced-ads' ), '<a href="https://www.google.com/adsense/new/u/0/' . esc_attr( $pub_id ) . '/myads/units" target="_blank">', '<a href="#" class="mapi-insert-code prevent-default">', '</a>' );
			?>
		</li>
		<li>
			<?php
			/* Translators: 1: opening tag for a link to create an ad manually 2: closing a tag   */
			printf( wp_kses( __( '%1$sCreate an AdSense code manually%2$s: Select the <em>Normal</em> or <em>Responsive</em> type and the size.', 'advanced-ads' ), [
				'em'     => [],
				'strong' => [],
            ] ), '<a href="#" class="mapi-close-selector-link prevent-default">', '</a>' );
			?>
		</li>
		<li>
			<?php esc_html_e( 'Choose a different ad from your AdSense account above.', 'advanced-ads' ); ?>
		</li>
	</ul>
</div>
