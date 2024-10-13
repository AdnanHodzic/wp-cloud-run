<?php

/**
 * Advanced Ads Plain Ad Type
 *
 * @package   Advanced_Ads
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright 2014 Thomas Maier, Advanced Ads GmbH
 *
 * Class containing information about the plain text/code ad type
 *
 * see ad-type-content.php for a better sample on ad type
 */
class Advanced_Ads_Ad_Type_Plain extends Advanced_Ads_Ad_Type_Abstract {

	/**
	 * ID - internal type of the ad type
	 *
	 * @var string $ID ad type id.
	 */
	public $ID = 'plain';

	/**
	 * Set basic attributes
	 */
	public function __construct() {
		$this->title       = __( 'Plain Text and Code', 'advanced-ads' );
		$this->description = __( 'Any ad network, Amazon, customized AdSense codes, shortcodes, and code like JavaScript, HTML or PHP.', 'advanced-ads' );
		$this->parameters  = [
			'content' => '',
		];
	}

	/**
	 * Output for the ad parameters metabox
	 *
	 * This will be loaded using ajax when changing the ad type radio buttons
	 * echo the output right away here
	 * name parameters must be in the "advanced_ads" array
	 *
	 * @param Advanced_Ads_Ad $ad Advanced_Ads_Ad.
	 */
	public function render_parameters( $ad ) {
		// Load content.
		$content = ( isset( $ad->content ) ) ? $ad->content : '';

		?><p class="description"><?php esc_html_e( 'Insert plain text or code into this field.', 'advanced-ads' ); ?></p>
		<?php $this->error_unfiltered_html( $ad ); ?>
		<textarea
			id="advads-ad-content-plain"
			cols="40"
			rows="10"
			name="advanced_ad[content]"
			onkeyup="Advanced_Ads_Admin.check_ad_source()"
		><?php echo esc_textarea( $content ); ?></textarea>
		<?php include ADVADS_ABSPATH . 'admin/views/ad-info-after-textarea.php'; ?>
		<input type="hidden" name="advanced_ad[output][allow_php]" value="0"/>

		<?php

		$this->render_php_allow( $ad );
		$this->render_shortcodes_allow( $ad );
		?>
		<script>jQuery( function () { Advanced_Ads_Admin.check_ad_source() } )</script>
		<?php
	}

	/**
	 * Render php output field
	 *
	 * @param object $ad Advanced_Ads_Ad object.
	 */
	public function render_php_allow( $ad ) {
		$content = ( isset( $ad->content ) ) ? $ad->content : '';

		// Check if php is allowed.
		if ( isset( $ad->output['allow_php'] ) ) {
			$allow_php = absint( $ad->output['allow_php'] );
		} else {
			/**
			 * For compatibility for ads with PHP added prior to 1.3.18
			 *  check if there is php code in the content
			 */
			$allow_php = preg_match( '/<\?php/', $content );
		}
		?>
		<label class="label" for="advads-parameters-php"><?php esc_html_e( 'Allow PHP', 'advanced-ads' ); ?></label>
		<div>
			<input id="advads-parameters-php" type="checkbox" name="advanced_ad[output][allow_php]" value="1" <?php checked( 1, $allow_php ); ?> onChange="Advanced_Ads_Admin.check_ad_source();" <?php disabled( ! $this->is_php_globally_allowed() ); ?>/>
				<span class="advads-help">
					<span class="advads-tooltip">
						<?php
						echo wp_kses(
							__( 'Execute PHP code (wrapped in <code>&lt;?php ?&gt;</code>)', 'advanced-ads' ),
							[
								'code' => [],
							]
						);
						?>
					</span>
				</span>
			<?php if ( ! $this->is_php_globally_allowed() ) : ?>
				<p class="advads-notice-inline advads-error">
					<?php
					printf(
					/* translators: The name of the constant preventing PHP execution */
						esc_html__( 'Executing PHP code has been disallowed by %s', 'advanced-ads' ),
						sprintf( '<code>%s</code>', defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ? 'DISALLOW_FILE_EDIT' : 'ADVANCED_ADS_DISALLOW_PHP' )
					);
					?>
				</p>
			<?php else : ?>
				<p class="advads-notice-inline advads-error" id="advads-allow-php-warning" style="display:none;">
					<?php esc_html_e( 'Using PHP code can be dangerous. Please make sure you know what you are doing.', 'advanced-ads' ); ?>
				</p>
			<?php endif; ?>
			<p class="advads-notice-inline advads-error" id="advads-parameters-php-warning" style="display:none;">
				<?php esc_html_e( 'No PHP tag detected in your code.', 'advanced-ads' ); ?> <?php esc_html_e( 'Uncheck this checkbox for improved performance.', 'advanced-ads' ); ?>
			</p>
		</div>
		<hr/>
		<?php
	}

	/**
	 * Render allow shortcodes field.
	 *
	 * @param object $ad Advanced_Ads_Ad object.
	 */
	public function render_shortcodes_allow( $ad ) {
		$allow_shortcodes = ! empty( $ad->output['allow_shortcodes'] );
		?>
		<label class="label"
				for="advads-parameters-shortcodes"><?php esc_html_e( 'Execute shortcodes', 'advanced-ads' ); ?></label>
		<div>
			<input id="advads-parameters-shortcodes" type="checkbox" name="advanced_ad[output][allow_shortcodes]"
					value="1"
					<?php
					checked( 1, $allow_shortcodes );
					?>
					onChange="Advanced_Ads_Admin.check_ad_source();"/>
			<p class="advads-notice-inline advads-error" id="advads-parameters-shortcodes-warning"
					style="display:none;"><?php esc_html_e( 'No shortcode detected in your code.', 'advanced-ads' ); ?> <?php esc_html_e( 'Uncheck this checkbox for improved performance.', 'advanced-ads' ); ?></p>
		</div>
		<hr/>
		<?php
	}

	/**
	 * Prepare the ads frontend output
	 *
	 * @param Advanced_Ads_Ad $ad ad object.
	 *
	 * @return string $content ad content prepared for frontend output.
	 * @since 1.0.0
	 */
	public function prepare_output( $ad ) {
		$content = $ad->content;

		// Evaluate the code as PHP if setting was never saved or is allowed.
		if ( ( ! isset( $ad->output['allow_php'] ) || $ad->output['allow_php'] ) && $this->is_php_globally_allowed() ) {
			ob_start();
			// This code only runs if the "Allow PHP" option for plain text ads was enabled.
			// phpcs:ignore Squiz.PHP.Eval.Discouraged -- this is specifically eval'd so allow eval here.
			eval( '?>' . $ad->content );
			$content = ob_get_clean();
		}

		if ( ! is_string( $content ) ) {
			return '';
		}

		/**
		 * Apply do_blocks if the content has block code
		 * works with WP 5.0.0 and later
		 */
		if ( function_exists( 'has_blocks' ) && has_blocks( $content ) ) {
			$content = do_blocks( $content );
		}

		if ( ! empty( $ad->output['allow_shortcodes'] ) ) {
			$content = $this->do_shortcode( $content, $ad );
		}

		// Add 'loading' attribute if applicable, available from WP 5.5.
		if (
			function_exists( 'wp_lazy_loading_enabled' )
			&& wp_lazy_loading_enabled( 'img', 'the_content' )
			&& preg_match_all( '/<img\s[^>]+>/', $content, $matches )
		) {
			// iterate images.
			foreach ( $matches[0] as $image ) {
				// skip if it already has the loading attribute.
				if ( strpos( $image, 'loading=' ) !== false ) {
					continue;
				}

				// Optimize image HTML tag with loading attributes based on WordPress filter context.
				$content = str_replace( $image, $this->img_tag_add_loading_attr( $image, 'the_content' ), $content );
			}
		}

		return (
			(
				( defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML ) ||
				! $this->author_can_unfiltered_html( (int) get_post_field( 'post_author', $ad->id ) )
			)
			&& version_compare( $ad->options( 'last_save_version', '0' ), '1.35.0', 'ge' )
		)
			? wp_kses( $content, wp_kses_allowed_html( 'post' ) )
			: $content;
	}

	/**
	 * Check if php execution is globally forbidden.
	 *
	 * @return bool
	 */
	private function is_php_globally_allowed() {
		return ! ( defined( 'ADVANCED_ADS_DISALLOW_PHP' ) && ADVANCED_ADS_DISALLOW_PHP )
			&& ! ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT );
	}

	/**
	 * Check if we're on an ad edit screen, if yes and the user does not have `unfiltered_html` permissions,
	 * show an admin notice.
	 *
	 * @param Advanced_Ads_Ad $ad the current ad object.
	 *
	 * @return void
	 */
	protected function error_unfiltered_html( Advanced_Ads_Ad $ad ) {
		$author_id       = (int) get_post_field( 'post_author', $ad->id );
		$current_user_id = get_current_user_id();

		if ($this->author_can_unfiltered_html($author_id)) {
			return;
		}

		?>
		<p class="advads-notice-inline advads-error">
			<?php
			if ( $author_id === $current_user_id ) {
				esc_html_e( 'You do not have sufficient permissions to include all HTML tags.', 'advanced-ads' );
			} else {
				esc_html_e( 'The creator of the ad does not have sufficient permissions to include all HTML tags.', 'advanced-ads' );
				if ( current_user_can( 'unfiltered_html' ) && $this->user_has_role_on_site() ) {
					printf( '<button type="button" onclick="(()=>Advanced_Ads_Admin.reassign_ad(%d))();" class="button button-primary">%s</button>', $current_user_id, esc_html__( 'Assign ad to me', 'advanced-ads' ) );
				}
			}
			?>
			<a href="https://wpadvancedads.com/manual/ad-types/#Plain_Text_and_Code" target="_blank" rel="noopener">
				<?php esc_html_e( 'Manual', 'advanced-ads' ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * Check if the ad content needs filtering.
	 *
	 * @param string $content The parsed ad content.
	 * @deprecated
	 *
	 * @return string
	 */
	protected function kses_ad( $content ) {
		return $content;
	}

	/**
	 * Check if the author of the ad can use unfiltered_html.
	 *
	 * @param int $author_id User ID of the ad author.
	 *
	 * @return bool
	 */
	private function author_can_unfiltered_html( $author_id ) {
		if ( defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML ) {
			return false;
		}

		$unfiltered_allowed = user_can( $author_id, 'unfiltered_html' );
		if ( $unfiltered_allowed || ! is_multisite() ) {
			return $unfiltered_allowed;
		}

		$options = Advanced_Ads::get_instance()->options();
		if ( ! isset( $options['allow-unfiltered-html'] ) ) {
			$options['allow-unfiltered-html'] = [];
		}
		$allowed_roles = $options['allow-unfiltered-html'];
		$user          = get_user_by( 'id', $author_id );

		return ! empty( array_intersect( $user->roles, $allowed_roles ) );
	}

	/**
	 * Check if the current user has a role on this site.
	 *
	 * @return bool
	 */
	private function user_has_role_on_site() {
		return in_array(
			get_current_blog_id(),
			wp_list_pluck( get_blogs_of_user( get_current_user_id() ), 'userblog_id' ),
			true
		);
	}
}
