<?php
/**
 * Peepso Compatibility.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Compatibility;

use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Interfaces\Ad_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Peepso Ad.
 */
class Peepso_Ad extends Ad implements Ad_Interface {

	/**
	 * Prepare output for frontend.
	 *
	 * @return string
	 */
	public function prepare_frontend_output(): string {
		$content = $this->get_content();

		$image_url = wp_get_attachment_image_url( $this->get_prop( 'image_id' ), 'full' );
		$image_url = $image_url[0] ?? null;

		$avatar_url = wp_get_attachment_image_url( $this->get_prop( 'avatar_id' ), 'full' );
		$avatar_url = $avatar_url[0] ?? null;

		$url            = $this->get_url() ?? '#';
		$title          = $this->get_title() ?? '';
		$title_override = $this->get_prop( 'title_override' ) ?? '';
		if ( $title_override && '' !== $title_override ) {
			$title = $title_override;
		}

		ob_start();
		?>
		<div class="ps-post__header">
			<?php if ( $avatar_url ) : ?>
			<a class="ps-avatar ps-avatar--post" target="_blank" href="<?php echo esc_url( $url ); ?>">
				<img src="<?php echo esc_url( $avatar_url ); ?>" alt="" />
			</a>
			<?php endif; ?>
			<div class="ps-post__meta">
				<div class="ps-post__title">
					<a target="_blank" href="<?php echo esc_url( $url ); ?>">
						<?php echo $title; // phpcs:ignore ?>
					</a>
				</div>
				<div class="ps-post__info">
					<?php
					if ( \PeepSo::get_option( 'advancedads_stream_sponsored_mark', 0 ) ) {
						echo \PeepSo::get_option( 'advancedads_stream_sponsored_text' ); // phpcs:ignore
					}
					?>
				</div>
			</div>
		</div>

		<div class="ps-post__body">
			<div class="ps-post__content">
				<?php
				$content    = $this->get_content();
				$allow_tags = \PeepSo::get_option_new( 'advanced_ads_allow_all_tags' );
				echo $allow_tags
					? $content // phpcs:ignore
					: wpautop( strip_tags( $content, \PeepSoAdvancedAdsAdTypePeepSo::get_allowed_html() ) ); // phpcs:ignore
				?>
			</div>

			<?php if ( $image_url ) : ?>
			<a target="_blank" class="ps-advads__image" href="<?php echo esc_url( $url ); ?>">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="" />
			</a>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}
}
