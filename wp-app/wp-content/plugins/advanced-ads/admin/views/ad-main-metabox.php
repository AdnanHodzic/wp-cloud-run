<?php $types = Advanced_Ads::get_instance()->ad_types; ?>
<?php if ( empty( $types ) ) : ?>
	<p><?php esc_html_e( 'No ad types defined', 'advanced-ads' ); ?></p>
<?php else : ?>
	<ul id="advanced-ad-type">
		<?php
			// Choose first type if none set.
			$ad_type = ( isset( $ad->type ) ) ? $ad->type : current( $types )->ID;
		foreach ( $types as $_type ) :
			$ad_type_title = empty( $_type->title ) ? $_type->ID : $_type->title;
			if ( isset( $_type->is_upgrade ) && $_type->is_upgrade ) :
				// Ad types that are available through an upgrade.
				?>
				<li class="advanced-ads-type-list-<?php echo esc_attr( $_type->ID ); ?>">
					<input type="radio" disabled="disabled"/>
							<label><?php echo esc_html( $ad_type_title ); ?></label>
							<?php
							if ( ! empty( $_type->description ) ) :
								?>
						<span class="advads-help"><span class="advads-tooltip">
								<?php
								echo esc_html( $_type->description );
								?>
							</span></span>
								<?php
								// the URL needs to be placed outside the tooltip
								if ( ! empty( $_type->upgrade_url ) ) :
									echo ' ';
									Advanced_Ads_Admin_Upgrades::upgrade_link( __( 'Manual', 'advanced-ads' ), $_type->upgrade_url, 'upgrade-ad-type-' . $_type->ID );
							endif;
						endif;
							?>
				</li>
				<?php
			else :
				?>
					<li class="advanced-ads-type-list-<?php echo esc_attr( $_type->ID ); ?>">
						<input type="radio" name="advanced_ad[type]" id="advanced-ad-type-<?php echo esc_attr( $_type->ID ); ?>" value="<?php echo esc_attr( $_type->ID ); ?>" <?php checked( $ad_type, $_type->ID ); ?>/>
						<label for="advanced-ad-type-<?php echo esc_attr( $_type->ID ); ?>"><?php echo esc_html( $ad_type_title ); ?></label>
						<?php
						if ( ! empty( $_type->description ) ) :
							?>
							<span class="advads-help"><span class="advads-tooltip"><?php echo esc_html( $_type->description ); ?></span></span><?php endif; ?>
					</li>
				<?php
			endif;
			endforeach;
		?>
	</ul>
<?php endif; ?>
<script>
jQuery( document ).on('change', '#advanced-ad-type input', function () {
	AdvancedAdsAdmin.AdImporter.onChangedAdType();
	advads_update_ad_type_headline();
});

// dynamically move ad type to the meta box title
advads_main_metabox_title = jQuery('#ad-main-box h2').text();
function advads_update_ad_type_headline(){
	var advads_selected_type = jQuery('#advanced-ad-type input:checked + label').text();
	var advads_selected_id = jQuery('#advanced-ad-type input:checked').attr('id');
	jQuery('#ad-main-box h2').html( advads_main_metabox_title + ': ' + advads_selected_type );
}
advads_update_ad_type_headline();
</script>
