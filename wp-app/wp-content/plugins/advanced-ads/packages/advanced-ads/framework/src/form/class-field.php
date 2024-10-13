<?php
/**
 * Form base input
 *
 * @package AdvancedAds\Framework\Form
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Form;

use Advanced_Ads_Admin_Upgrades;
use AdvancedAds\Framework\Utilities\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Field class
 */
abstract class Field {

	/**
	 * Hold field data.
	 *
	 * @var array
	 */
	protected $field = null;

	/**
	 * The constructor
	 *
	 * @param array $field Field data.
	 */
	public function __construct( $field ) {
		$defaults = [
			'label'         => '',
			'placeholder'   => '',
			'class'         => '',
			'style'         => '',
			'wrapper_class' => '',
			'value'         => '',
			'name'          => $field['name'] ?? $field['id'],
			'type'          => 'text',
			'desc'          => '',
			'is_pro_pitch'  => false,
			'cols'          => 30,
			'rows'          => 10,
		];

		$this->field = wp_parse_args( $field, $defaults );
	}

	/**
	 * Get from field data by id.
	 *
	 * @param string $id Id of the data to get.
	 * @param mixed  $default Default value if not set.
	 *
	 * @return mixed
	 */
	public function get( $id, $default = false ) {
		return $this->field[ $id ] ?? $default;
	}

	/**
	 * Render field
	 *
	 * @return void
	 */
	public function render_field() {
		/**
		 * This filter allows to extend the class dynamically by add-ons
		 * this would allow add-ons to dynamically hide/show only attributes belonging to them, practically not used now
		 */
		$class = apply_filters( 'advanced-ads-option-class', $this->get( 'id' ) );

		$classnames = Str::classnames(
			'advads-option',
			'advads-field',
			'advads-field-' . sanitize_html_class( $this->get( 'type' ) ),
			'advads-option-' . sanitize_html_class( $class ),
			sanitize_html_class( $this->get( 'wrapper_class' ) )
		);

		$this->wrap_before();
		?>
		<div id="<?php echo esc_attr( $this->get( 'id' ) ); ?>" class="<?php echo $classnames; // phpcs:ignore ?>">
			<span class="advads-field-label"><?php echo esc_html( $this->get( 'label' ) ); ?></span>
			<div class="advads-field-input">
				<?php

				$this->render_callback( 'before' );
				$this->render();
				$this->render_callback( 'after' );

				if ( $this->get( 'desc' ) ) {
					echo '<p class="description">' . wp_kses_post( $this->get( 'desc' ) ) . '</p>';
				}

				// Place an upgrade link below the description if there is one.
				if ( $this->get( 'is_pro_pitch' ) ) {
					Advanced_Ads_Admin_Upgrades::upgrade_link( 'upgrade-pro-' . $this->get( 'id' ) );
				}
				?>
			</div>
		</div>
		<?php
		$this->wrap_after();
	}

	/**
	 * HTML before wrap
	 *
	 * @return void
	 */
	public function wrap_before() {}

	/**
	 * HTML after wrap
	 *
	 * @return void
	 */
	public function wrap_after() {}

	/**
	 * Render
	 *
	 * @return void
	 */
	abstract public function render();

	/**
	 * Render callback
	 *
	 * @param string $name Id of callback.
	 *
	 * @return void
	 */
	private function render_callback( $name ): void {
		$callback = $this->get( $name );
		// Early bail!!
		if ( ! $callback || ! is_callable( $callback ) ) {
			return;
		}

		call_user_func( $callback, $this );
	}
}
