<?php
defined( 'ABSPATH' ) || exit;

/**
 * Logic to render options for ads, groups and placements
 */
class Advanced_Ads_Admin_Options {
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Advanced_Ads_Admin_Options constructor.
	 */
	private function __construct() {
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Create a wrapper for a single option line
	 *
	 * @param   string $id     internal id of the option wrapper.
	 * @param   string $title  label of the option.
	 * @param   string $content    content of the option or full path to template file or custom flag to show a pre-defined information.
	 * @param   string $description  description of the option.
	 */
	public static function render_option( $id, $title, $content, $description = '' ) {

		/**
		 * This filter allows to extend the class dynamically by add-ons
		 * this would allow add-ons to dynamically hide/show only attributes belonging to them, practically not used now
		 */
		$class = apply_filters( 'advanced-ads-option-class', $id );
		?>
		<div class="advads-option advads-option-<?php echo esc_attr( $class ); ?>">
			<span><?php echo esc_html( $title ); ?></span>
			<div>
			<?php
			if ( 'is_pro_pitch' === $content ) {
				// Skip this step and place an upgrade link below the description if there is one.
			} elseif ( strlen( $content ) < 500 && file_exists( $content ) ) { // Check length of the string because too long content can break `file_exists`.
				include $content;
			} else {
				// phpcs:ignore
				echo $content; // could include various HTML elements.
			}
			?>
			<?php
			if ( $description ) :
				// phpcs:ignore
				echo '<p class="description">' . $description . '</p>'; // could include various HTML elements.
			endif;

			// place an upgrade link below the description if there is one.
			if ( 'is_pro_pitch' === $content ) {
				Advanced_Ads_Admin_Upgrades::pro_feature_link( 'upgrade-pro-' . $id );
			}
			?>
			</div>
		</div>
		<?php
	}

}
