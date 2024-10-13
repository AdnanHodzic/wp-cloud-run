<div id="advads-ads-txt">
    <label title="<?php _e( 'enabled', 'advanced-ads' ); ?>">
        <input type="radio" name="advads-ads-txt-create" value="1" <?php checked( $is_enabled, true ); ?> />
		<?php _e( 'enabled', 'advanced-ads' ); ?>
    </label>
    <label title="<?php _e( 'disabled', 'advanced-ads' ); ?>">
        <input type="radio" name="advads-ads-txt-create" value="0" <?php checked( $is_enabled, false ); ?> />
		<?php _e( 'disabled', 'advanced-ads' ); ?>
    </label>
	<span class="description">
		<a target="_blank" href="https://wpadvancedads.com/manual/ads-txt/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-ads-txt" class="advads-manual-link">
			<?php esc_html_e( 'Manual', 'advanced-ads' ); ?>
		</a>
    </span>

	<?php if ( $can_process_all_network ) : ?>
        <p>
            <label><input name="advads-ads-txt-all-network" type="checkbox" <?php
                checked( $is_all_network, true ); ?> />
				<?php printf(
					esc_html__( 'Generate a single ads.txt file for all sites in the multisite network.', 'advanced-ads' ),
					'<code>abc.' . $domain . '</code>',
					'<code>' . $domain . '/abc</code>'
				) ?>
            </label>
        </p>
        <p class="description">
			<?php
			esc_html_e( 'Usually, this should be enabled on the main site of the network - often the one without a subdomain or subdirectory.', 'advanced-ads' );
			?>
        </p>
	<?php endif; ?>
</div>
