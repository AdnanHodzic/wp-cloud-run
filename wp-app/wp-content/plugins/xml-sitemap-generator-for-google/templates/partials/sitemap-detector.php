<?php
/**
 * @var $args
 */

$sitemap_url = $args['sitemap_url'];

if ( file_exists( ABSPATH . $sitemap_url ) ) {
	$notice_status  = 'error';
	$notice_message = esc_html__( 'Warning! Static Sitemap File was detected in this URL. Please remove this file from the WordPress Root Directory to use Dynamic Sitemap.', 'xml-sitemap-generator-for-google' );
} else {
	$notice_status  = 'success';
	$notice_message = sprintf(
		esc_html__( 'No static sitemap file was detected in this URL. %s', 'xml-sitemap-generator-for-google' ),
		'<span>' . esc_html__( 'Above URL will open the awesome Dynamic Sitemap.', 'xml-sitemap-generator-for-google' ) . '</span>'
	);
}

if ( ! empty( $sitemap_url ) && ! empty( $notice_message ) ) {
	?>
	<div class="grim-notice notice-<?php echo esc_html( $notice_status ); ?> inline sitemap-detector">
		<i class="grim-icon-check-circle"></i>
		<p>
			<?php echo wp_kses_post( $notice_message ); ?>
		</p>
	</div>
	<?php
}
