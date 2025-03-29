<?php
/**
 * Render the Usage meta box on the ad edit screen
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var WP_Post|object $post   Post instance.
 * @var array          $values Setting array.
 */

wp_nonce_field( 'advads_post_meta_box', 'advads_post_meta_box_nonce' );
?>
<p>
	<a href="https://wpadvancedads.com/how-to-block-ads-on-a-specific-page/?utm_source=advanced-ads&utm_medium=link&utm_campaign=disable-ads-on-specific-pages" target="_blank">
		<?php esc_html_e( 'How to disable ads on specific pages', 'advanced-ads' ); ?>
	</a>
</p>
<label>
	<input type="checkbox" name="advanced_ads[disable_ads]" value="1"<?php checked( $values['disable_ads'] ?? false ); ?>/>
	<?php esc_html_e( 'Disable ads on this page', 'advanced-ads' ); ?>
</label>
<?php
do_action( 'advanced_ads_render_post_meta_box', $post, $values );
