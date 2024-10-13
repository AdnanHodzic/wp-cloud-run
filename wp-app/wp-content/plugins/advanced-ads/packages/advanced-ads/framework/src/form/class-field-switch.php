<?php
/**
 * Form switch input
 *
 * @package AdvancedAds\Framework\Form
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Form;

defined( 'ABSPATH' ) || exit;

/**
 * Field switch class
 */
class Field_Switch extends Field {

	/**
	 * Render field
	 *
	 * @return void
	 */
	public function render() {
		?>
		<input class="switch" type="checkbox" name="<?php echo esc_attr( $this->get( 'name' ) ); ?>" value="1"<?php checked( $this->get( 'value' ), '1' ); ?> />
		<label for="<?php echo esc_attr( $this->get( 'id' ) ); ?>"><?php echo esc_attr( $this->get( 'switch_label' ) ); ?></label>
		<?php
	}
}
