<?php
/**
 * This class represents the "Image" ad type.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads\Types;

use AdvancedAds\Ads\Ad_Image;
use AdvancedAds\Interfaces\Ad_Type;
use AdvancedAds\Utilities\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Type Image.
 */
class Image implements Ad_Type {

	/**
	 * Get the unique identifier (ID) of the ad type.
	 *
	 * @return string The unique ID of the ad type.
	 */
	public function get_id(): string {
		return 'image';
	}

	/**
	 * Get the class name of the object as a string.
	 *
	 * @return string
	 */
	public function get_classname(): string {
		return Ad_Image::class;
	}

	/**
	 * Get the title or name of the ad type.
	 *
	 * @return string The title of the ad type.
	 */
	public function get_title(): string {
		return __( 'Image Ad', 'advanced-ads' );
	}

	/**
	 * Get a description of the ad type.
	 *
	 * @return string The description of the ad type.
	 */
	public function get_description(): string {
		return __( 'Ads in various image formats.', 'advanced-ads' );
	}

	/**
	 * Check if this ad type requires premium.
	 *
	 * @return bool True if premium is required; otherwise, false.
	 */
	public function is_premium(): bool {
		return false;
	}

	/**
	 * Get the URL for upgrading to this ad type.
	 *
	 * @return string The upgrade URL for the ad type.
	 */
	public function get_upgrade_url(): string {
		return '';
	}

	/**
	 * Get the URL for upgrading to this ad type.
	 *
	 * @return string The upgrade URL for the ad type.
	 */
	public function get_image(): string {
		return ADVADS_BASE_URL . 'assets/img/ad-types/image.svg';
	}

	/**
	 * Check if this ad type has size parameters.
	 *
	 * @return bool True if has size parameters; otherwise, false.
	 */
	public function has_size(): bool {
		return true;
	}

	/**
	 * Render preview on the ad overview list
	 *
	 * @param Ad_Image $ad Ad instance.
	 *
	 * @TODO: refactor and test
	 *
	 * @return void
	 */
	public function render_preview( Ad_Image $ad ): void {
		if ( empty( $ad->get_image_id() ) ) {
			return;
		}

		list( $src, $width, $height ) = wp_get_attachment_image_src( $ad->get_image_id(), 'medium', true );
		$preview_size_small           = 50;
		$preview_size_large           = 200;

		// Scale down width or height for the preview.
		if ( $width > $height ) {
			$preview_height = ceil( $height / ( $width / $preview_size_small ) );
			$preview_width  = $preview_size_small;
			$tooltip_height = ceil( $height / ( $width / $preview_size_large ) );
			$tooltip_width  = $preview_size_large;
		} else {
			$preview_width  = ceil( $width / ( $height / $preview_size_small ) );
			$preview_height = $preview_size_small;
			$tooltip_width  = ceil( $width / ( $height / $preview_size_large ) );
			$tooltip_height = $preview_size_large;
		}

		$preview_hwstring = image_hwstring( $preview_width, $preview_height );
		$tooltip_hwstring = image_hwstring( $tooltip_width, $tooltip_height );
		$alt              = wp_strip_all_tags( get_post_meta( $ad->get_image_id(), '_wp_attachment_image_alt', true ) );

		include ADVADS_ABSPATH . 'admin/views/ad-list/preview-image.php';
	}

	/**
	 * Output for the ad parameters metabox
	 *
	 * @param Ad_Image $ad Ad instance.
	 *
	 * @return void
	 */
	public function render_parameters( $ad ): void {
		$id        = $ad->get_image_id() ?? '';
		$url       = $ad->get_url() ?? '';
		$edit_link = $id ? get_edit_post_link( $id ) : '';

		?><span class="label">
			<button href="#" class="advads_image_upload button advads-button-secondary" type="button"
				data-uploader-title="<?php esc_attr_e( 'Insert File', 'advanced-ads' ); ?>"
				data-uploader-button-text="<?php esc_attr_e( 'Insert', 'advanced-ads' ); ?>"
				onclick="return false;">
				<?php esc_html_e( 'Select image', 'advanced-ads' ); ?>
			</button>
		</span>
		<div>
			<input type="hidden" name="advanced_ad[output][image_id]" value="<?php echo absint( $id ); ?>" id="advads-image-id"/>
			<div id="advads-image-preview">
				<?php $this->create_image_tag( $id, $ad ); ?>
			</div>
			<a id="advads-image-edit-link" class="<?php echo ! $edit_link ? 'hidden' : ''; ?>" href="<?php echo esc_url( $edit_link ); ?>"><span class="dashicons dashicons-edit"></span></a>
		</div>
		<hr/>
		<?php
		if ( ! defined( 'AAT_VERSION' ) ) :
			?>
			<label for="advads-url" class="label"><?php esc_html_e( 'URL', 'advanced-ads' ); ?></label>
			<div>
				<input type="url" name="advanced_ad[url]" id="advads-url" class="advads-ad-url" value="<?php echo esc_url( $url ); ?>" placeholder="https://www.example.com/"/>
				<p class="description">
					<?php esc_html_e( 'Link to target site including http(s)', 'advanced-ads' ); ?>
				</p>
			</div>
			<hr/>
			<?php
		endif;
	}

	/**
	 * Generate a string with the original image size for output in the backend
	 * Only show, if different from entered image sizes
	 *
	 * @param Ad_Image $ad Ad instance.
	 *
	 * @return string empty, if the entered size is the same as the original size
	 */
	public function show_original_image_size( Ad_Image $ad ) {
		$attachment_id = $ad->get_image_id() ?? '';
		$attachment    = wp_get_attachment_image_src( $attachment_id, 'full' );

		if ( $attachment ) {
			list( $src, $width, $height ) = $attachment;
			?>
			<p class="description">
			<?php
			if ( $ad->get_width() !== $width || $ad->get_height() !== $height ) :
				printf(
					/* translators: $s is a size string like "728 x 90". */
					esc_attr__( 'Original size: %s', 'advanced-ads' ),
					esc_html( $width ) . '&nbsp;x&nbsp;' . esc_html( $height )
				);
				?>
				</p>
				<?php
			endif;
		}

		return '';
	}

	/**
	 * Render image tag
	 *
	 * @param int      $attachment_id Attachment id.
	 * @param Ad_Image $ad            Ad instance.
	 *
	 * @return void
	 */
	public function create_image_tag( $attachment_id, $ad ): void {
		global $wp_current_filter;

		$style = '';
		$image = wp_get_attachment_image_src( $attachment_id, 'full' );

		// Early bail!!
		if ( ! $image ) {
			return;
		}

		list( $src, $width, $height ) = $image;

		// Override image size with the size given in ad options, but in frontend only.
		if ( ! is_admin() || wp_doing_ajax() ) {
			$width  = $ad->get_width();
			$height = $ad->get_height();
		}

		$hwstring = image_hwstring( $width, $height );
		$alt      = trim( esc_textarea( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );

		// TODO: use an array for attributes so they are simpler to extend.
		$sizes           = '';
		$srcset          = '';
		$more_attributes = $srcset;
		// Create srcset and sizes attributes if we are in the the_content filter and in WordPress 4.4.
		if (
			isset( $wp_current_filter )
			&& in_array( 'the_content', $wp_current_filter, true )
			&& ! defined( 'ADVADS_DISABLE_RESPONSIVE_IMAGES' )
		) {
			if ( function_exists( 'wp_get_attachment_image_srcset' ) ) {
				$srcset = wp_get_attachment_image_srcset( $attachment_id, 'full' );
			}
			if ( function_exists( 'wp_get_attachment_image_sizes' ) ) {
				$sizes = wp_get_attachment_image_sizes( $attachment_id, 'full' );
			}
			if ( $srcset && $sizes ) {
				$more_attributes .= ' srcset="' . $srcset . '" sizes="' . $sizes . '"';
			}
		}

		// TODO: move to classes/compabtility.php when we have a simpler filter for additional attributes
		// Compabitility with WP Smush.
		// Disables their lazy load for image ads because it caused them to not show up in certain positions at all.
		$wp_smush_settings = get_option( 'wp-smush-settings' );
		if ( isset( $wp_smush_settings['lazy_load'] ) && $wp_smush_settings['lazy_load'] ) {
			$more_attributes .= ' class="no-lazyload"';
		}

		// Add css rule to be able to center the ad.
		if ( strpos( $ad->get_position(), 'center' ) === 0 ) {
			$style .= 'display: inline-block;';
		}

		$style = apply_filters( 'advanced-ads-ad-image-tag-style', $style );
		$style = '' !== $style ? 'style="' . $style . '"' : '';

		$more_attributes  = apply_filters( 'advanced-ads-ad-image-tag-attributes', $more_attributes );
		$more_attributes .= ' ' . $hwstring . ' ' . $style;
		$img              = sprintf( '<img src="%s" alt="%s" %s />', esc_url( $src ), esc_attr( $alt ), $more_attributes );

		// Add 'loading' attribute if applicable, available from WP 5.5.
		if (
			$wp_current_filter
			&& function_exists( 'wp_lazy_loading_enabled' )
			&& wp_lazy_loading_enabled( 'img', current_filter() )
			&& ! strpos( $more_attributes, 'loading=' )
		) {
			// Optimize image HTML tag with loading attributes based on WordPress filter context.
			$img = WordPress::img_tag_add_loading_attr( $img, current_filter() );
		}

		echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
