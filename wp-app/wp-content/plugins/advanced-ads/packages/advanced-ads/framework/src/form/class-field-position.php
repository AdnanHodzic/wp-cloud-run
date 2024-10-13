<?php
/**
 * Form position input
 *
 * @package AdvancedAds\Framework\Form
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Form;

defined( 'ABSPATH' ) || exit;

/**
 * Field position class
 */
class Field_Position extends Field {

	/**
	 * Render field
	 *
	 * @return void
	 */
	public function render() {
		?>
		<table class="<?php echo sanitize_html_class( $this->get( 'class' ) ); ?>">
			<?php foreach ( [ 'top', 'center', 'bottom' ] as $parent ) : ?>
			<tr>
				<?php
				foreach ( [ 'left', 'center', 'right' ] as $child ) :
					$key = $parent . $child;
					if ( 'centercenter' === $key ) {
						$key = $child;
					}
					?>
				<td>
					<input type="radio" name="<?php echo esc_attr( $this->get( 'name' ) ); ?>" value="<?php echo esc_attr( $key ); ?>"<?php checked( $key, $this->get( 'value' ) ); ?><?php disabled( in_array( $key, $this->get( 'disabled', [] ), true ) ); ?> />
				</td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</table>
		<?php
	}
}
