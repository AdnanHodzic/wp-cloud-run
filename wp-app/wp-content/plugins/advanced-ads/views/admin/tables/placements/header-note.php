<?php
/**
 * Show a note about placing ads in the header.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

?>
<div style="margin-top: 12px;">
	<?php
	printf(
		wp_kses(
			/* translators: %s is a URL. */
			__( 'Tutorial: <a href="%s" target="_blank">How to place visible ads in the header of your website</a>.', 'advanced-ads' ),
			[
				'a' => [
					'href'   => [],
					'target' => [],
				],
			]
		),
		'https://wpadvancedads.com/place-ads-in-website-header/?utm_source=advanced-ads&utm_medium=link&utm_campaign=header-ad-tutorial'
	);
	?>
</div>
<?php
