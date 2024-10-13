<?php
/**
 * Render inline css option for placements.
 *
 * @var string $_placement_slug slug of the current placement.
 * @var string $_placement      Placement with all options.
 * @package Advanced_Ads_Admin
 */

$inline_css = isset( $_placement['options']['inline-css'] ) ? $_placement['options']['inline-css'] : '';
?>
<input
	type="text"
	value="<?php echo esc_attr( $inline_css ); ?>"
	name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][inline-css]"/>
