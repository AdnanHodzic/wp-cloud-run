<?php
/**
 * Option to enter notes for a given ad
 *
 * @var Advanced_Ads_Ad $ad Ad object.
 */
?>
<label class="label" for="advads-usage-notes" onclick="advads_toggle('#advads-ad-notes textarea'); advads_toggle('#advads-ad-notes p')"><?php esc_html_e( 'notes', 'advanced-ads' ); ?></label>
<div id="advads-ad-notes">
	<p title="<?php esc_html_e( 'click to change', 'advanced-ads' ); ?>" onclick="advads_toggle('#advads-ad-notes textarea'); advads_toggle('#advads-ad-notes p')">
		<?php
		if ( ! empty( $ad->description ) ) {
			echo nl2br( esc_html( $ad->description ) );
		} else {
			esc_html_e( 'Click to add notes', 'advanced-ads' );
		}
		?>
		<span class="dashicons dashicons-edit"></span>
	</p>
	<textarea name="advanced_ad[description]" id="advads-usage-notes"><?php echo esc_html( $ad->description ); ?></textarea>
</div>
<hr/>
