<?php
/**
 * Placement Edit Modal.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Modal;
use AdvancedAds\Abstracts\Placement;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Utilities\Content_Injection;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Placement Edit Modal.
 */
class Placement_Edit_Modal implements Integration_Interface {

	/**
	 * Hold placement
	 *
	 * @var Placement
	 */
	private $placement = null;

	/**
	 * View path
	 *
	 * @var string
	 */
	private $view_path = null;

	/**
	 * The constructor
	 *
	 * @param Placement $placement Placement instance.
	 */
	public function __construct( $placement ) {
		$this->placement = $placement;
		$this->view_path = ADVADS_ABSPATH . 'views/admin/placements/edit-modal';
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_footer', [ $this, 'render_modal' ] );
	}

	/**
	 * Load the modal for creating a new Placement.
	 *
	 * @return void
	 */
	public function render_modal(): void {
		Modal::create(
			[
				'modal_slug'    => 'placement-edit-' . $this->placement->get_id(),
				'modal_content' => $this->render_modal_content(),
				/* translators: 1: "Options", 2: the name of a placement. */
				'modal_title'   => sprintf( '%1$s: %2$s', __( 'Options', 'advanced-ads' ), $this->placement->get_title() ),
			]
		);
	}

	/**
	 * Get form id
	 *
	 * @return string
	 */
	private function get_form_id(): string {
		return 'advanced-ads-placement-form-' . $this->placement->get_id();
	}

	/**
	 * Render the placement modal content as if it was the single edit page for a post.
	 *
	 * @return string
	 */
	private function render_modal_content(): string {
		ob_start();
		$placement = $this->placement;
		$user_id   = wp_get_current_user()->ID;
		$author_id = get_post_field( 'post_author', $this->placement->get_id() );

		include $this->view_path . '/edit-modal-content.php';

		return ob_get_clean();
	}

	/**
	 * Render settings.
	 *
	 * @return void
	 */
	private function render_settings(): void {
		$basic_settings = [
			'placement-name'   => [
				'label'       => __( 'Name', 'advanced-ads' ),
				'callback'    => [ $this, 'render_placement_name' ],
				'description' => '',
				'order'       => 5,
			],
			'placement-item'   => [
				'label'       => __( 'Item', 'advanced-ads' ),
				'callback'    => [ $this, 'render_placement_item' ],
				'description' => '',
				'order'       => 5,
			],
			'placement-status' => [
				'label'       => __( 'Status', 'advanced-ads' ),
				'callback'    => [ $this, 'render_placement_status' ],
				'description' => '',
				'order'       => 5,
			],
		];

		if ( $this->placement->is_type( 'post_content' ) ) {
			$basic_settings['placement-content-injection-index'] = [
				'label'       => __( 'position', 'advanced-ads' ),
				'callback'    => [ $this, 'render_placement_position' ],
				'description' => '',
				'order'       => 6,
			];
		}

		self::render_settings_group( $basic_settings );

		$slug = $this->placement->get_slug();

		/**
		 * Hook before advanced placement options
		 *
		 * @param string    $slug      the placement slug.
		 * @param Placement $placement the placement.
		 */
		do_action( 'advanced-ads-placement-options-before-advanced', $slug, $this->placement );

		$type_options      = $this->placement->get_type_object()->get_options();
		$advanced_settings = [];

		if ( ! $this->placement->is_type( 'header' ) ) {
			if ( $type_options['placement-ad-label'] ?? true ) {
				$advanced_settings['placement-ad-label'] = [
					'label'       => __( 'ad label', 'advanced-ads' ),
					'callback'    => [ $this, 'render_placement_ad_label' ],
					'description' => '',
					'order'       => 7,
				];
			}

			if ( isset( $type_options['show_position'] ) && $type_options['show_position'] ) {
				$advanced_settings['placement-position'] = [
					'label'       => __( 'Position', 'advanced-ads' ),
					'callback'    => [ $this, 'render_placement_label_position' ],
					'description' => '',
					'order'       => 7,
				];
			}

			$advanced_settings['placement-inline-css'] = [
				'label'       => __( 'Inline CSS', 'advanced-ads' ),
				'callback'    => [ $this, 'render_placement_inline_css' ],
				'description' => '',
				'order'       => 9,
			];

			if ( ! defined( 'AAP_VERSION' ) ) {
				// Minimum Content Length.
				$advanced_settings['placement-content-minimum-length'] = [
					'label'       => __( 'Minimum Content Length', 'advanced-ads' ),
					'callback'    => [ $this, 'render_pro_pitch' ],
					'description' => __( 'Minimum length of content before automatically injected ads are allowed in them.', 'advanced-ads' ),
					'order'       => 10,
				];

				// Words Between Ads.
				$advanced_settings['placement-skip-paragraph'] = [
					'label'       => __( 'Words Between Ads', 'advanced-ads' ),
					'callback'    => [ $this, 'render_pro_pitch' ],
					'description' => __( 'A minimum amount of words between automatically injected ads.', 'advanced-ads' ),
					'order'       => 10,
				];
			}
		}

		// show the conditions pitch on the `head` placement as well.
		if ( ! defined( 'AAP_VERSION' ) ) {
			// Display Conditions for placements.
			$advanced_settings['placement-display-conditions'] = [
				'label'       => __( 'Display Conditions', 'advanced-ads' ),
				'callback'    => [ $this, 'render_pro_pitch' ],
				'description' => __( 'Use display conditions for placements.', 'advanced-ads' ) . ' ' . __( 'The free version provides conditions on the ad edit page.', 'advanced-ads' ),
				'order'       => 10,
			];

			// Visitor Condition for placements.
			$advanced_settings['placement-visitor-conditions'] = [
				'label'       => __( 'Visitor Conditions', 'advanced-ads' ),
				'callback'    => [ $this, 'render_pro_pitch' ],
				'description' => __( 'Use visitor conditions for placements.', 'advanced-ads' ) . ' ' . __( 'The free version provides conditions on the ad edit page.', 'advanced-ads' ),
				'order'       => 10,
			];
		}

		if ( $advanced_settings ) {
			self::render_settings_group( $advanced_settings );
		}

		/**
		 * Hook after advanced placement options
		 *
		 * @param string    $slug      the placement slug.
		 * @param Placement $placement the placement.
		 */
		do_action( 'advanced-ads-placement-options-after-advanced', $slug, $this->placement );
	}

	/**
	 * Render a group of placements options
	 *
	 * @param array $settings placement option fields.
	 *
	 * @return void
	 */
	private static function render_settings_group( $settings ) {
		array_multisort( array_column( $settings, 'order' ), SORT_ASC, $settings );

		foreach ( $settings as $setting_id => $setting ) {
			WordPress::render_option(
				$setting_id,
				$setting['label'],
				$setting['callback']( $setting_id ),
				$setting['description']
			);
		}
	}

	/**
	 * Render the placement name option.
	 *
	 * @return string
	 */
	private function render_placement_name(): string {
		$placement = $this->placement;

		ob_start();
		include $this->view_path . '/fields/name.php';

		return ob_get_clean();
	}

	/**
	 * Render the placement item option.
	 *
	 * @return string
	 */
	private function render_placement_item(): string {
		$placement = $this->placement;

		ob_start();
		include $this->view_path . '/fields/item.php';

		return ob_get_clean();
	}

	/**
	 * Render the placement status option.
	 *
	 * @return string
	 */
	private function render_placement_status(): string {
		$placement = $this->placement;

		ob_start();
		include $this->view_path . '/fields/status.php';

		return ob_get_clean();
	}

	/**
	 * Render placement ad label option.
	 *
	 * @return string
	 */
	private function render_placement_ad_label(): string {
		$label = $this->placement->get_prop( 'ad_label' ) ?? 'default';

		ob_start();
		include $this->view_path . '/fields/ad-label.php';

		return ob_get_clean();
	}

	/**
	 * Render the placement label position option.
	 *
	 * @return string
	 */
	private function render_placement_label_position(): string {
		$position = $this->placement->get_prop( 'placement_position' ) ?? 'default';
		$clearfix = ! empty( $this->placement->get_prop( 'placement_clearfix' ) );

		ob_start();
		include $this->view_path . '/fields/ad-label-position.php';

		return ob_get_clean();
	}

	/**
	 * Render the placement inline CSS option.
	 *
	 * @return string
	 */
	private function render_placement_inline_css(): string {
		$placement      = $this->placement;
		$placement_slug = $this->placement->get_slug();
		$inline_css     = $this->placement->get_prop( 'inline-css' ) ?? '';

		ob_start();
		include $this->view_path . '/fields/inline-css.php';

		return ob_get_clean();
	}

	/**
	 * Return    `is_pro_pitch` for the options that are pitching the pro version.
	 * WordPress::render_option() handles this string as a special case.
	 *
	 * @return string
	 */
	private function render_pro_pitch(): string {
		return 'is_pro_pitch';
	}

	/**
	 * Render the placement position
	 *
	 * @return string
	 */
	public function render_placement_position() {
		$data           = $this->placement->get_data();
		$placement_slug = $this->placement->get_slug();
		$index          = max( 1, (int) ( $data['index'] ?? 1 ) );
		$tags           = Content_Injection::get_tags();
		$selected_tag   = $data['tag'] ?? 'p';

		// Automatically select the 'custom' option.
		if ( Params::cookie( 'advads_frontend_picker', '' ) === $placement_slug ) {
			$selected_tag = 'custom';
		}
		$xpath             = stripslashes( $data['xpath'] ?? '' );
		$positions         = [
			'after'  => __( 'after', 'advanced-ads' ),
			'before' => __( 'before', 'advanced-ads' ),
		];
		$selected_position = $data['position'] ?? 'after';
		$start_from_bottom = isset( $data['start_from_bottom'] );

		ob_start();
		include $this->view_path . '/fields/content-index.php';
		if ( ! defined( 'AAP_VERSION' ) ) {
			include ADVADS_ABSPATH . 'admin/views/upgrades/repeat-the-position.php';
		}

		do_action( 'advanced-ads-placement-post-content-position', $placement_slug, $this->placement );

		if ( ! extension_loaded( 'dom' ) ) :
			?>
			<p>
				<span class="advads-notice-inline advads-error">
					<?php esc_html_e( 'Important Notice', 'advanced-ads' ); ?>:
				</span>
				<?php
				printf(
					/* translators: %s is a name of a module. */
					esc_html__( 'Missing PHP extension could cause issues. Please ask your hosting provider to enable it: %s', 'advanced-ads' ),
					'dom (php_xml)'
				);
				?>
			</p>
			<?php
		endif;

		return ob_get_clean();
	}
}
