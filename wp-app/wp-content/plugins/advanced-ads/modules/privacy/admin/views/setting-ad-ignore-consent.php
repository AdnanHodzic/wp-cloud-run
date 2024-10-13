<?php
/**
 * Single ad section for overriding privacy settings.
 * Not used if privacy not activated or method is 'iab_tcf_20' and ad type 'adsense'.
 *
 * @var bool $ignore_consent Whether to override privacy setting for this ad.
 */
?>
<div class="advads-option-list">
	<span class="label"><?php esc_html_e( 'privacy', 'advanced-ads' ); ?></span>
	<div id="advanced-ads-ad-parameters-privacy">
		<label>
			<input name="advanced_ad[privacy][ignore-consent]" type="checkbox" <?php checked( $ignore_consent ); ?>/>
			<?php
			printf(
			/* Translators: 1: a tag with link to general privacy settings, 2: closing a tag */
				esc_html__( 'Ignore %1$sgeneral Privacy settings%2$s and display the ad even without consent.', 'advanced-ads' ),
				'<a onclick="event.stopPropagation();" href="' . esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#privacy' ) ) . '">',
				'</a>'
			);
			?>
		</label>
	</div>
</div>
<hr/>
