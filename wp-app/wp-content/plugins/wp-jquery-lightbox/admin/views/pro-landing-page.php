<?php
/**
 * View: Pro Landing Page
 *
 * Renders the markup for the LightPress Pro upgrade page.
 *
 * @package    LightPress Pro
 * @author     LightPress
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

?>
<div class="sale-banner">
	<p><?php esc_html_e( 'LightPress Pro is launched! Take 30% off this week - use code LIGHTPRESS at checkout.', 'wp-jquery-lightbox' ); ?></p>
</div>
<img class="lightpress-logo" src="<?php echo esc_url( LIGHTPRESS_PLUGIN_URL . 'admin/lightpress-logo.png' ); ?>"/>
<div class="hero-section">
	<div class="hero-section-copy">
		<p class="hero-section-copy-tag"><?php esc_html_e( 'LightPress Pro', 'wp-jquery-lightbox' ); ?></p>
		<h1 class="hero-section-copy-title"><?php esc_html_e( 'Make your media a highlight of your website.', 'wp-jquery-lightbox' ); ?></h1>
		<p class="hero-section-copy-text"><?php esc_html_e( 'Add the brilliant new Pro Lightbox. Fast. Perfect on all devices. Supports images, videos, pdfs, maps, modals with custom content, and more.', 'wp-jquery-lightbox' ); ?></p>
		<div class="hero-section-actions">
			<a class="pro-action-button" href="https://lightpress.io/pro-lightbox" target="_blank"><?php esc_html_e( 'Learn More', 'wp-jquery-lightbox' ); ?></a>
			<a class="pro-action-button" href="https://lightpress.io/pro-lightbox" target="_blank"><?php esc_html_e( 'See Demos', 'wp-jquery-lightbox' ); ?></a>
		</div>
		<p class="hero-section-copy-under-button"><?php esc_html_e( 'Or buy directly from the WordPress dashboard below!', 'wp-jquery-lightbox' ); ?></p>
	</div>
	<div class="hero-section-image">
		<figure>
			<picture><img src="<?php echo esc_url( LIGHTPRESS_PLUGIN_URL . 'admin/device-mock.jpg' ); ?>" alt="Lightbox display across devices."></picture>
		</figure>
	</div>
</div>
<div class="pricing-section">
	<div class="sale-notice">
		<h1><?php esc_html_e( '30% Off Launch Sale!', 'wp-jquery-lightbox' ); ?></h1>
		<p><?php esc_html_e( 'Celebrate the launch of LightPress Pro!', 'wp-jquery-lightbox' ); ?></p>
		<p><?php esc_html_e( 'Use code LIGHTPRESS at checkout.', 'wp' ); ?></p>
	</div>
	<div class="pricing-table">
		<div class="plan">
			<h3 class="plan-title">Basic</h3>
			<div class="plan-cost"><span class="plan-price">$49</span></div>
			<ul class="plan-features">
			<li class="strong"><?php esc_html_e( 'Limited Pro Lightbox', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong excluded"><?php esc_html_e( 'PRO SUPPORT', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong included"><?php esc_html_e( 'PLUGIN UPDATES', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong included"><?php esc_html_e( 'PRO LIGHTBOX: IMAGES', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Open Most Image Formats', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'All Customization Options', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Thumbnails in Lightbox', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Toolbar Controls', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Slidesshow & Transitions', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Fullscreen', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Image Zooming', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong excluded"><?php esc_html_e( 'PRO LIGHTBOX: EXTENDED', 'wp-jquery-lightbox' ); ?></li>
			<li class="excluded"><?php esc_html_e( 'Open Vides', 'wp-jquery-lightbox' ); ?></li>
			<li class="excluded"><?php esc_html_e( 'Open PDFs', 'wp-jquery-lightbox' ); ?></li>
			<li class="excluded"><?php esc_html_e( 'Open Maps', 'wp-jquery-lightbox' ); ?></li>
			<li class="excluded"><?php esc_html_e( 'Open Inline Content', 'wp-jquery-lightbox' ); ?></li>
			<li class="excluded"><?php esc_html_e( 'Create Modals/Popups', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'MOBILE OPTIMIZATION', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'SPEED OPTIMIZATION', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong excluded"><?php esc_html_e( 'UNLIMITED SITES', 'wp-jquery-lightbox' ); ?></li>
			</ul>
			<div class="plan-select">
			<div class="plan-select-dropdown">
				<select id="basic-licenses">
				<option value="1"><?php esc_html_e( '1 Site License', 'wp-jquery-lightbox' ); ?> ($39)'</option>
				<option value="5" selected="selected"><?php esc_html_e( '5 Site License', 'wp-jquery-lightbox' ); ?> ($49)</option>
				<option value="25"><?php esc_html_e( '25 Site License', 'wp-jquery-lightbox' ); ?> ($99)</option>
				</select>
			</div>
			<button id="basic-purchase" class="pro-action-button"><?php esc_html_e( 'Buy Now', 'wp-jquery-lightbox' ); ?></button>
			</div>
		</div>
		<div class="plan featured">
			<h3 class="plan-title">Pro<span class="most-popular"><?php esc_html_e( 'Most Popular!', 'wp-jquery-lightbox' ); ?></span></h3>
			<div class="plan-cost"><span class="plan-price">$79</span></div>
			<ul class="plan-features">
			<li class="strong"><?php esc_html_e( 'Pro Lightbox + Support', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong included"><?php esc_html_e( 'PRO SUPPORT', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong included"><?php esc_html_e( 'PLUGIN UPDATES', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong included"><?php esc_html_e( 'PRO LIGHTBOX: IMAGES', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Open Most Image Formats', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'All Customization Options', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Thumbnails in Lightbox', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Toolbar Controls', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Slidesshow & Transitions', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Fullscreen', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Image Zooming', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong included"><?php esc_html_e( 'PRO LIGHTBOX: EXTENDED', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Open Videos', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Open PDFs', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Open Maps', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Open Inline Content', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Create Modals/Popups', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'MOBILE OPTIMIZATION', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'SPEED OPTIMIZATION', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong excluded"><?php esc_html_e( 'UNLIMITED SITES', 'wp-jquery-lightbox' ); ?></li>
			</ul>
			<div class="plan-select">
			<div class="plan-select-dropdown">
				<select id="pro-licenses">
				<option value="1"><?php esc_html_e( '1 Site License', 'wp-jquery-lightbox' ); ?> ($69)</option>
				<option value="5" selected="selected"><?php esc_html_e( '5 Site License', 'wp-jquery-lightbox' ); ?> ($79)</option>
				<option value="25"><?php esc_html_e( '25 Site License', 'wp-jquery-lightbox' ); ?> ($139)</option>
				</select>
			</div>
			<button id="pro-purchase" class="pro-action-button"><?php esc_html_e( 'Buy Now', 'wp-jquery-lightbox' ); ?></button>
			</div>
		</div>
		<div class="plan">
			<h3 class="plan-title">Enterprise</h3>
			<div class="plan-cost"><span class="plan-price">$399</span></div>
			<ul class="plan-features">
			<li class="strong"><?php esc_html_e( 'Unlimited Sites', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong included"><?php esc_html_e( 'PRO SUPPORT', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong included"><?php esc_html_e( 'PLUGIN UPDATES', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong included"><?php esc_html_e( 'PRO LIGHTBOX: IMAGES', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Open Most Image Formats', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'All Customization Options', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Thumbnails in Lightbox', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Toolbar Controls', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Slidesshow & Transitions', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Fullscreen', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Image Zooming', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong included"><?php esc_html_e( 'PRO LIGHTBOX: EXTENDED', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Open Videos', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Open PDFs', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Open Maps', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Open Inline Content', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'Create Modals/Popups', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'MOBILE OPTIMIZATION', 'wp-jquery-lightbox' ); ?></li>
			<li class="included"><?php esc_html_e( 'SPEED OPTIMIZATION', 'wp-jquery-lightbox' ); ?></li>
			<li class="strong included"><?php esc_html_e( 'UNLIMITED SITES', 'wp-jquery-lightbox' ); ?></li>
			</ul>
			<div class="plan-select">
			<div class="plan-select-dropdown">
				<select id="enterprise-licenses">
				<option value="unlimited"><?php esc_html_e( 'Unlimited Sites', 'easy-fancybox' ); ?> ($399)</option>
				</select>
			</div>
			<button id="enterprise-purchase" class="pro-action-button"><?php esc_html_e( 'Buy Now', 'wp-jquery-lightbox' ); ?></button>
			</div>
		</div>
	</div>
	<div style="clear:both;"></div>`
</div>
