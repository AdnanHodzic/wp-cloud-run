<?php
/**
 * Peepso Compatibility.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Compatibility;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Peepso.
 *
 * phpcs:disable WordPress.WP.I18n.TextDomainMismatch
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
 */
class Peepso implements Integration_Interface {

	/**
	 * Object.
	 *
	 * @var object
	 */
	private $object = null;

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'advanced-ads-ad-types', [ $this, 'ad_type' ], 100 );
		add_filter( 'advanced-ads-placement-types', [ $this, 'placement_type' ], 25 );
	}

	/**
	 * Add Peepso placement type to Advanced Ads.
	 *
	 * @param array $types placement types.
	 *
	 * @return array
	 */
	public function placement_type( $types ): array {
		if ( class_exists( 'PeepSoAdvancedAdsPlugin' ) ) {
			$types['peepso_stream'] = [
				'title'       => __( 'PeepSo Stream', 'peepso-advanced-ads' ),
				'description' => __( 'Display this ad in PeepSo Stream', 'advanced-ads' ),
				'image'       => ADVADS_BASE_URL . 'assets/img/placement-types/peepso-stream-placement.png',
				'is_premium'  => false,
			];
		}

		return $types;
	}

	/**
	 * Add Peepso ad type to Advanced Ads.
	 *
	 * @param array $types ad types.
	 *
	 * @return array
	 */
	public function ad_type( $types ): array {
		if ( class_exists( 'PeepSoAdvancedAdsPlugin' ) && isset( $types['peepso'] ) && 'Advanced_Ads_Ad_Type_Abstract' === get_parent_class( $types['peepso'] ) ) {
			$this->object = $types['peepso'];
			unset( $types['peepso'] );

			$types['peepso'] = [
				'id'                => 'peepso',
				'title'             => $this->object->title,
				'description'       => $this->object->description,
				'is_upgrade'        => false,
				'classname'         => Peepso_Ad::class,
				'render_parameters' => [ $this, 'render_parameters' ],
			];
		}

		return $types;
	}

	/**
	 * Render Peepso parameters.
	 *
	 * @param AD $ad Ad object.
	 *
	 * @return void
	 */
	public function render_parameters( $ad ) {
		$content        = $ad->get_content();
		$title_override = $ad->get_prop( 'title_override' ) ?? '';
		$image_id       = $ad->get_prop( 'image_id' ) ?? '';
		$avatar_id      = $ad->get_prop( 'avatar_id' ) ?? '';
		$url            = $ad->get_url() ?? '#';
		?>
		<script type="text/javascript">
			jQuery(function( $ ) {
				$('#advanced-ads-ad-parameters-size').prev('span').hide();
			});
		</script>
		<style type="text/css">
			#advads-image-preview img, #advads-avatar-preview img{
				padding:2px;
				border:solid 1px #aaaaaa;
			}

			#advads-image-preview {
				width:500px;
			}

			#advads-avatar-preview img {
				height: auto;
				max-width: 128px;
				max-heigth:128px;
				width: 100%;
			}

			#advanced-ads-ad-parameters-size {
				display:none;
			}

			.description {
				color:#aaaaaa;
			}
		</style>


		<!-- Avatar -->
		<h1>
			<?php esc_html_e( 'Avatar', 'peepso-advanced-ads' ); ?>
			<button href="#" class="advads_avatar_upload button button-secondary" type="button" data-uploader-title="<?php esc_html_e( 'Insert File', 'peepso-advanced-ads' ); ?>" data-uploader-button-text="<?php esc_html_e( 'Insert', 'peepso-advanced-ads' ); ?>" onclick="return false;">
				<?php esc_html_e( 'Change', 'peepso-advanced-ads' ); ?>
			</button>
		</h1>

		<div class="description">
			<?php esc_html_e( 'The uploaded image should be square and at least 128x128 pixels.', 'peepso-advanced-ads' ); ?>
		</div>

		<div id="advads-avatar-preview">
			<?php echo $this->object->image_tag( $avatar_id ); ?>
		</div>

		<input type="hidden" name="advanced_ad[output][avatar_id]" value="<?php echo $avatar_id; ?>" id="advads-avatar-id"/>

		<br class="clear" />

		<hr>

		<!-- Title Override -->
		<h1>
		<?php esc_html_e( 'Title override', 'peepso-advanced-ads' ); ?>
		</h1>
		<div class="description">
		<?php esc_html_e( 'Optional. If nothing is provided, the general ad title will be used.', 'peepso-advanced-ads' ); ?>
		</div>

		<input type="text" size="64" maxlength=="128" id="advanced_ad[output][title_override]" name="advanced_ad[output][title_override]" value="<?php echo ( $title_override ); ?>" />

		<!-- Content -->
		<h1>
			<?php esc_html_e( 'Content', 'peepso-advanced-ads' ); ?>
		</h1>

		<div class="description">
			<?php
			$description = __( 'Supported HTML tags:', 'peepso-advanced-ads' ) . ' <pre style="display:inline-block;margin:0;">' . htmlspecialchars( \PeepSoAdvancedAdsAdTypePeepSo::get_allowed_html() ) . '</pre>';
			if ( \PeepSo::get_option_new( 'advanced_ads_allow_all_tags' ) ) {
				$description = __( 'All HTML tag enabled, proceed with extreme care', 'peepso-advanced-ads' );
			}

			echo $description;
			?>
		</div>

		<textarea id="advads-content-plain" cols="100" rows="10" name="advanced_ad[content]"><?php echo esc_textarea( $content ); ?></textarea>

		<br class="clear" />

		<hr>

		<!-- Image  -->
		<h1>
			<?php esc_html_e( 'Image', 'peepso-advanced-ads' ); ?>
			<button href="#" class="advads_image_upload button button-secondary" type="button" data-uploader-title="<?php esc_html_e( 'Insert File', 'peepso-advanced-ads' ); ?>" data-uploader-button-text="<?php esc_html_e( 'Insert', 'peepso-advanced-ads' ); ?>" onclick="return false;">
				<?php esc_html_e( 'Change', 'peepso-advanced-ads' ); ?>
			</button>
		</h1>

		<div class="description">
			<?php esc_html_e( 'Image will be displayed at full width of your Community stream (depending on theme, layout and screen size). For best results use an image at least 1000 pixels wide.', 'peepso-advanced-ads' ); ?>
		</div>

		<div id="advads-image-preview">
			<?php echo $this->object->image_tag( $image_id ); ?>
		</div>

		<input type="hidden" name="advanced_ad[output][image_id]" value="<?php echo $image_id; ?>" id="advads-image-id"/>

		<br class="clear" />

		<hr>

		<h1>
		<?php esc_html_e( 'URL', 'peepso-advanced-ads' ); ?>
		</h1>
			<div class="description">
			<?php esc_html_e( 'Clicking the image, avatar or title will open the link in a new window/tab. ', 'peepso-advanced-ads' ); ?>
		</div>

		<input type="url" name="advanced_ad[url]" id="advads-url" value="<?php echo $url; ?>"/>

		<br class="clear" />

		<?php
	}
}
