<?php
/**
 * @var $args
 */
?>
<div class="pro-overlay">
	<div>
		<?php esc_html_e( 'This feature is available on Premium version', 'xml-sitemap-generator-for-google' ); ?>
		<a href="<?php echo esc_url( sgg_get_pro_url( $args['utm'] ?? 'buy-now' ) ); ?>" target="_blank"><?php esc_html_e( 'Get Now', 'xml-sitemap-generator-for-google' ); ?></a>
	</div>
</div>
