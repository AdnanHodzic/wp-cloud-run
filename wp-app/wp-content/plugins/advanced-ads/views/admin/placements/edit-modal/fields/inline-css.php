<?php
/**
 * Render inline css option for placements.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 *
 * @var string $placement_slug Slug of the current placement.
 * @var string $placement     Placement with all options.
 */

?>
<input
	type="text"
	value="<?php echo esc_attr( $inline_css ); ?>"
	name="advads[placements][options][inline-css]"/>
