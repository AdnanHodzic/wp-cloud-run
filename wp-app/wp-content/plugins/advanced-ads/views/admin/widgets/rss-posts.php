<?php
/**
 * Render RSS Posts
 *
 * @package AdvancedAds
 *
 * @var array $rss_posts RSS posts.
 */

?>
<?php if ( empty( $rss_posts ) || ! is_array( $rss_posts ) ) : ?>
	<p>
		<?php esc_html_e( 'Error: the Advanced Ads blog feed could not be downloaded.', 'advanced-ads' ); ?>
	</p>
	<?php
	return;
endif;
?>

<div class="advads-rss-widget">
	<ul>
		<?php
		foreach ( $rss_posts as $rss_post ) :
			$utm_params = [
				'utm_source'   => 'advanced-ads',
				'utm_medium'   => 'rss-link',
				'utm_campaign' => 'dashboard',
			];

			$rss_link = add_query_arg( $utm_params, $rss_post['link'] );
			?>
			<li>
				<a class="rsswidget" target="_blank" href="<?php echo esc_url( $rss_link ); ?>">
					<?php echo esc_html( $rss_post['title']['rendered'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
