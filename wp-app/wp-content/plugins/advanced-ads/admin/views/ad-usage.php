<?php
/**
 * Render usage information for ads.
 *
 * @var WP_Post $post current WP_Post object.
 */
?>
<label class="label" for="advads-usage-shortcode"><?php esc_html_e( 'Shortcode', 'advanced-ads' ); ?></label>
<div class="advads-usage">
	<code><input type="text" id="advads-usage-shortcode" onclick="this.select();" value='[the_ad id="<?php echo (int) $post->ID; ?>"]' readonly/></code>
</div>
<hr/>
<label class="label" for="advads-usage-function"><?php esc_html_e( 'Template (PHP)', 'advanced-ads' ); ?></label>
<div class="advads-usage">
	<code><input type="text" id="advads-usage-function" onclick="this.select();" value="&lt;?php the_ad('<?php echo (int) $post->ID; ?>'); ?&gt;" readonly/></code>
	<?php
	printf(
	/* translators: 1: is an opening a tag, 2: is a closing a tag */
		esc_html__( 'Find more display options in the %1$smanual%2$s.', 'advanced-ads' ),
		sprintf( '<a href="%smanual/placements/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-ad-title" target="_blank" class="advads-manual-link">', 'https://wpadvancedads.com/' ),
		'</a>'
	);
	?>
</div>
