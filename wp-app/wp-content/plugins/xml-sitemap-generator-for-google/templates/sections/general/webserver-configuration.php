<?php
/**
 * @var $args
 */

$settings       = $args['settings'] ?? new stdClass();
$stylesheet_url = apply_filters( 'sitemap_xsl_template_path', 'sitemap-stylesheet.xsl' );
?>

<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Webserver Configuration', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p><?php esc_html_e( 'As you are using NGINX web server, you may need to set up the following Rewrite Rules if you encounter 404 Not Found error for your sitemap:', 'xml-sitemap-generator-for-google' ); ?></p>

		<code>
			<?php if ( $settings->enable_sitemap ) { ?>
				rewrite ^/<?php echo esc_html( $settings->sitemap_url ); ?>$ /index.php?sitemap_xml=true last;
				<br>
			<?php } ?>

			<?php if ( sgg_pro_enabled() && $settings->enable_html_sitemap ) { ?>
				rewrite ^/<?php echo esc_html( $settings->html_sitemap_url ); ?>$ /index.php?sitemap_html=true last;
				<br>
			<?php } ?>

			<?php if ( $settings->enable_google_news ) { ?>
				rewrite ^/<?php echo esc_html( $settings->google_news_url ); ?>$ /index.php?google_news=true last;
				<br>
			<?php } ?>

			<?php if ( $settings->enable_image_sitemap ) { ?>
				rewrite ^/<?php echo esc_html( $settings->image_sitemap_url ); ?>$ /index.php?image_sitemap=true last;
				<br>
			<?php } ?>

			<?php if ( $settings->enable_video_sitemap ) { ?>
				rewrite ^/<?php echo esc_html( $settings->video_sitemap_url ); ?>$ /index.php?video_sitemap=true last;
				<br>
			<?php } ?>

			<?php if ( sgg_is_multilingual() ) { ?>
				rewrite ^/multilingual-sitemap.xml$ /index.php?multilingual_sitemap=true last;
				<br>
			<?php } ?>

			rewrite ^/<?php echo esc_html( $stylesheet_url ); ?>$ /index.php?sitemap_xsl=true&$args last;
			<br>

			rewrite ^/(.*)-sitemap([0-9]*)\.(xml|html)$ /index.php?sitemap_$3=true&inner_sitemap=$1&page=$2 last;
			<br>
		</code>

		<p><?php esc_html_e( 'Please note that you may need to adjust the rules based on your server configuration. Rules are generated dynamically according to your Settings.', 'xml-sitemap-generator-for-google' ); ?></p>
	</div>
</div>
