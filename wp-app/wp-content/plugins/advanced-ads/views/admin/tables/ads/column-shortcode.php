<?php
/**
 * Render ad shortcode column in the ad overview list
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var int $ad_id Ad id.
 */

?>
<input class="advads-ad-injection-shortcode" onclick="this.select();" value="[the_ad id=&quot;<?php echo esc_attr( $ad_id ); ?>&quot;]"/>
