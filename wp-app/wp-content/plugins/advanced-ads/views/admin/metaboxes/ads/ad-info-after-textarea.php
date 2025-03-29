<?php
/**
 * Render additional information below the text area on the ad edit page
 * currently "plain text" and "rich content" ad types
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 *
 * @var Ad $ad Ad instance.
 *
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
 */

use AdvancedAds\Utilities\Validation;

if ( defined( 'WP_DEBUG' ) && WP_DEBUG &&
	( $error = Validation::is_valid_ad_dom( $ad ) ) ) : // phpcs:ignore ?>
	<p class="advads-notice-inline advads-error">
		<?php
		esc_html_e( 'The code of this ad might not work properly with the Content placement.', 'advanced-ads' );
		?>
		&nbsp;
		<?php
		printf(
			wp_kses(
				/* translators: %s is a URL. */
				__( 'Reach out to <a href="%s">support</a> to get help.', 'advanced-ads' ),
				[
					'a' => [
						'href' => [],
					],
				]
			),
			esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#support' ) )
		);
		?>
		<span style="white-space:pre-wrap"><?php echo $error; ?></span>
	</p>
	<?php
endif;

do_action( 'advanced-ads-ad-params-below-textarea', $ad );
