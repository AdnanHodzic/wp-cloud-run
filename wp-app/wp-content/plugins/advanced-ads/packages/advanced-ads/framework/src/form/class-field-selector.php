<?php
/**
 * Form selector input
 *
 * @package AdvancedAds\Framework\Form
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Form;

defined( 'ABSPATH' ) || exit;

/**
 * Field selector class
 */
class Field_Selector extends Field {

	/**
	 * Render field
	 *
	 * @return void
	 */
	public function render() {
		?>
		<div id="advads-frontend-element-<?php echo esc_attr( $this->get( 'placement_id' ) ); ?>">
			<input type="text" class="advads-frontend-element" name="<?php echo esc_attr( $this->get( 'name' ) ); ?>" value="<?php echo esc_attr( $this->get( 'value' ) ); ?>" />
			<button style="display:none; color: red;" type="button" class="advads-deactivate-frontend-picker button">
				stop selection
			</button>
			<button type="button" class="advads-activate-frontend-picker button" data-placementid="<?php echo esc_attr( $this->get( 'placement_id' ) ); ?>" data-action="edit-placement">
				select position
			</button>
		</div>
		<?php
	}
}
