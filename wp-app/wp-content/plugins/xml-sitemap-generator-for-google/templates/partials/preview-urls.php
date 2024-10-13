<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$sitemap_type   = $args['sitemap_type'] ?? 'sitemap_xml';
$sitemap_url    = $args['sitemap_url'] ?? '';
$languages      = sgg_get_languages();
$wpml_languages = apply_filters( 'wpml_active_languages', array() );
?>
<div class="<?php echo esc_attr( $args['class'] ?? '' ); ?>">
	<?php echo esc_html( $args['label'] ?? '' ); ?>
	<a href="<?php echo esc_url( sgg_get_sitemap_url( $sitemap_url, $sitemap_type ) ); ?>" target="_blank">
		<?php echo esc_url( sgg_get_sitemap_url( $sitemap_url, $sitemap_type ) ); ?>
	</a>

	<?php
	Dashboard::render(
		'partials/sitemap-detector.php',
		array(
			'sitemap_url' => $sitemap_url,
		)
	);
	?>
</div>

<?php if ( ! empty( $languages ) || ! empty( $wpml_languages ) ) { ?>
	<p class="<?php echo esc_attr( $args['class'] ?? '' ); ?>">
		<?php
		echo esc_html( $args['languages_label'] ?? '' );

		foreach ( $languages as $language ) {
			?>
			<br>
			<a href="<?php echo esc_url( sgg_get_sitemap_url( "{$language}/{$sitemap_url}", $sitemap_type ) ); ?>" target="_blank">
				<?php echo esc_url( sgg_get_sitemap_url( "{$language}/{$sitemap_url}", $sitemap_type ) ); ?>
			</a>
			<?php
		}

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			foreach ( $wpml_languages as $language ) {
				if ( apply_filters( 'wpml_default_language', null ) === $language['code'] ) {
					continue;
				}

				$url = strpos( $language['url'], '?lang=' ) !== false
					? str_replace( '?lang=', "{$sitemap_url}?lang=", $language['url'] )
					: trim( $language['url'], '/' ) . "/$sitemap_url";
				?>
				<br>
				<a href="<?php echo esc_url( $url ); ?>" target="_blank">
					<?php echo esc_url( $url ); ?>
				</a>
				<?php
			}
		}
		?>
	</p>

	<?php if ( 'sitemap_xml' === $sitemap_type ) { ?>
		<p class="<?php echo esc_attr( $args['class'] ?? '' ); ?>">
			<?php esc_html_e( 'Multilingual Sitemap Index:', 'xml-sitemap-generator-for-google' ); ?>
			<a href="<?php echo esc_url( site_url( 'multilingual-sitemap.xml' ) ); ?>" target="_blank">
				<?php echo esc_url( site_url( 'multilingual-sitemap.xml' ) ); ?>
			</a>
		</p>
	<?php } ?>
<?php } ?>
