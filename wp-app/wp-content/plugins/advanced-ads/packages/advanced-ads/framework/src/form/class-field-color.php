<?php
/**
 * Form color input
 *
 * @package AdvancedAds\Framework\Form
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Form;

defined( 'ABSPATH' ) || exit;

/**
 * Field color class
 */
class Field_Color extends Field {

	/**
	 * Render field
	 *
	 * @return void
	 */
	public function render() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		?>
		<input class="<?php echo sanitize_html_class( $this->get( 'class' ) ); ?>" name="<?php echo esc_attr( $this->get( 'name' ) ); ?>" type="text" value="<?php echo esc_attr( $this->get( 'value' ) ); ?>" />
		<?php
	}
}
