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
	$notice_message = esc_html__( 'Success! No static sitemap file was detected in this URL. Above URL will open the awesome Dynamic Sitemap.', 'xml-sitemap-generator-for-google' );
}

if ( ! empty( $sitemap_url ) && ! empty( $notice_message ) ) {
	?>
	<div class="notice notice-<?php echo esc_html( $notice_status ); ?> inline sitemap-detector">
		<?php echo esc_html( $notice_message ); ?>
	</div>
	<?php
}
