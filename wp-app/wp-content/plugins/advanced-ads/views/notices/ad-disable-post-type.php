<?php
/**
 * Display message indicating that ads are disabled for a specific post type.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var object $labels Post type labels.
 */

?>
<p>
	<?php
	printf(
		/* translators: %s post type plural name */
		esc_html__( 'Ads are disabled for all %s', 'advanced-ads' ),
		$labels->name // phpcs:ignore
	);
	?>
</p>
