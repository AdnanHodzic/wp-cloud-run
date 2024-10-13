<?php
/**
 * Form checkbox input
 *
 * @package AdvancedAds\Framework\Form
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Form;

defined( 'ABSPATH' ) || exit;

/**
 * Field checkbox class
 */
class Field_Checkbox extends Field {

	/**
	 * Render field
	 *
	 * @return void
	 */
	public function render() {
		// Early bail!!
		if ( ! $this->get( 'options' ) ) {
			return;
		}

		echo '<div class="advads-checkbox-list ' . sanitize_html_class( $this->get( 'class' ) ) . '">';
		foreach ( $this->get( 'options' ) as $key => $label ) :
			?>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( $this->get( 'name' ) ); ?>[]" value="<?php echo esc_attr( $key ); ?>"<?php checked( $this->get( 'value' ), $key ); ?> /><?php echo esc_html( $label ); ?>
			</label>
			<?php
		endforeach;

		echo '</div>';
	}
}
