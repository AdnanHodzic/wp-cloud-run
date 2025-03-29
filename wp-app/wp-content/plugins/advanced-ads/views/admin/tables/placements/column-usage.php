<?php
/**
 * Content for the "Show Usage"
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var Placement $placement Placement instance.
 */

?>
<div class="advads-usage advads-placement-usage-modal">
	<h2>
		<label for="usage-shortcode-<?php echo esc_attr( $placement->get_slug() ); ?>">
			<?php esc_html_e( 'Shortcode', 'advanced-ads' ); ?>
		</label>
	</h2>
	<code>
		<input class="widefat" type="text" id="usage-shortcode-<?php echo esc_attr( $placement->get_slug() ); ?>" onclick="this.select();" value='[the_ad_placement id="<?php echo esc_attr( $placement->get_slug() ); ?>"]' readonly />
	</code>
	<h2>
		<label for="usage-template-<?php echo esc_attr( $placement->get_slug() ); ?>">
			<?php esc_html_e( 'Template (PHP)', 'advanced-ads' ); ?>
		</label>
	</h2>
	<code>
		<input class="widefat" type="text" id="usage-template-<?php echo esc_attr( $placement->get_slug() ); ?>" onclick="this.select();" value="&lt;?php if ( function_exists( 'the_ad_placement' ) ) { the_ad_placement( '<?php echo esc_attr( $placement->get_slug() ); ?>' ); } ?&gt;" readonly />
	</code>
</div>
