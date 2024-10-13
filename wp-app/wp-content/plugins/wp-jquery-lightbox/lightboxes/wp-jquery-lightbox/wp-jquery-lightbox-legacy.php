<?php
// phpcs:ignoreFile
/**
 * Legacy version of the WP jQuery Lightbox plugin.
 *
 * This file contains the old, legacy version of the WP jQuery Lightbox plugin.
 * While this code is being replaced by more modern code, we're still including
 * this legacy file for backwards compatibility. Users can run this if they need
 * to use the old version of the plugin.
 *
 * @package LightPress
 * @deprecated This file is deprecated and may be removed in future versions.
 */

add_action( 'plugins_loaded', 'jqlb_init' );
global $jqlb_group;
$jqlb_group = -1;
define( 'JQLB_VERSION', '1.5.5' );
function jqlb_init() {
	if(!defined('ULFBEN_DONATE_URL')){
		define('ULFBEN_DONATE_URL', 'http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x=21&y=17');
	}
	load_plugin_textdomain('wp-jquery-lightbox', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
	add_action('admin_init', 'jqlb_register_settings');
	add_action('admin_menu', 'jqlb_register_menu_item');
	add_action('wp_print_styles', 'jqlb_css');
	add_action('wp_print_scripts', 'jqlb_js');
	add_filter('plugin_row_meta', 	'jqlb_set_plugin_meta', 2, 10);
	add_filter('the_content', 'jqlb_autoexpand_rel_wlightbox', 99);
	add_filter('post_gallery', 'jqlb_filter_groups', 10, 2);
	add_filter('image_send_to_editor', 'jqlb_remove_rel', 10, 2); //Editor in WP4.4 adds rel-attributes to image-links. let's remove them.
	if(get_option('jqlb_comments') == 1){
		remove_filter('pre_comment_content', 'wp_rel_nofollow');
		add_filter('comment_text', 'jqlb_lightbox_comment', 99);
	}
	add_action( 'wp_loaded', 'jqlb_save_date' );
}
function jqlb_set_plugin_meta( $links, $file ) { // Add a link to this plugin's settings page
	static $this_plugin;
	if(!$this_plugin) $this_plugin = plugin_basename(__FILE__);
	if($file == $this_plugin) {
		$settings_link = '<a href="options-general.php?page=jquery-lightbox-options">'.__('Settings', 'wp-jquery-lightbox').'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}
function jqlb_add_admin_footer(){ //shows some plugin info in the footer of the config screen.
	$plugin_data = get_plugin_data(__FILE__);
	printf('%1$s by %2$s <br />', $plugin_data['Title'].' '.$plugin_data['Version'], $plugin_data['Author']);
}
function jqlb_register_settings(){
	register_setting( 'jqlb-settings-group', 'jqlb_automate', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_showTitle', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_useAltForTitle', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_showCaption', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_showNumbers', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_comments', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_resize_on_demand', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_showDownload', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_navbarOnTop', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_margin_size', 'floatval');
	register_setting( 'jqlb-settings-group', 'jqlb_mobile_margin_size', 'floatval');
	register_setting( 'jqlb-settings-group', 'jqlb_resize_speed', 'jqlb_pos_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_slideshow_speed', 'jqlb_pos_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_use_theme_styles', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_enqueue_in_footer', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_pinchzoom', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_borderSize', 'jqlb_pos_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_borderColor', 'sanitize_text_field');
	register_setting( 'jqlb-settings-group', 'jqlb_overlayColor', 'sanitize_text_field');
	register_setting( 'jqlb-settings-group', 'jqlb_overlayOpacity', 'floatval');
	register_setting( 'jqlb-settings-group', 'jqlb_boxShadow', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_newNavStyle', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_fixedNav', 'jqlb_bool_intval');
	register_setting( 'jqlb-settings-group', 'jqlb_navArrowColor', 'sanitize_text_field');
	register_setting( 'jqlb-settings-group', 'jqlb_navBackgroundColor', 'sanitize_text_field');
	register_setting( 'jqlb-settings-group', 'jqlb_showInfoBar', 'jqlb_bool_intval');
	add_option('jqlb_showTitle', 1);
	add_option('jqlb_useAltForTitle', 1);
	add_option('jqlb_showCaption', 1);
	add_option('jqlb_showNumbers', 1);
	add_option('jqlb_automate', 1); //default is to auto-lightbox.
	add_option('jqlb_comments', 1);
	add_option('jqlb_resize_on_demand', 1);
	add_option('jqlb_showDownload', 0);
	add_option('jqlb_navbarOnTop', 0);
	add_option('jqlb_margin_size', 40);
	add_option('jqlb_mobile_margin_size', 10);
	add_option('jqlb_resize_speed', 400);
	add_option('jqlb_slideshow_speed', 4000);
	add_option('jqlb_use_theme_styles', 0);
	add_option('jqlb_enqueue_in_footer', 1);
	add_option('jqlb_pinchzoom', 1);
	add_option('jqlb_borderSize', 6);
	add_option('jqlb_borderColor', '#fff');
	add_option('jqlb_overlayColor', '#fff');
	add_option('jqlb_overlayOpacity', '0.7');
	add_option('jqlb_boxShadow', 1);
	add_option('jqlb_newNavStyle', 1);
	add_option('jqlb_navArrowColor', '#000000');
	add_option('jqlb_navBackgroundColor', 'rgba(255,255,255,.7');
	add_option('jqlb_fixedNav', 1);
	add_option('jqlb_showInfoBar', 0);
}
function jqlb_register_menu_item() {
	add_options_page('jQuery Lightbox Options', 'jQuery Lightbox', 'manage_options', 'jquery-lightbox-options', 'jqlb_options_panel');
}
function jqlb_get_locale(){
	//$lang_locales and ICL_LANGUAGE_CODE are defined in the WPML plugin (http://wpml.org/)
	global $lang_locales;
	if (defined('ICL_LANGUAGE_CODE') && isset($lang_locales[ICL_LANGUAGE_CODE])){
		$locale = $lang_locales[ICL_LANGUAGE_CODE];
	} else {
		$locale = get_locale();
	}
	return $locale;
}
function jqlb_css(){
	if(is_admin() || is_feed()){return;}
	$locale = jqlb_get_locale();
	$fileName = "lightbox.min.{$locale}.css";
	$haveThemeCss = false;
	if(get_option('jqlb_use_theme_styles') == 1){ // courtesy of Vincent Weber
		$pathTheme = get_stylesheet_directory() ."/{$fileName}"; //look for CSS in theme's style folder first
		if(false === ($haveThemeCss = is_readable($pathTheme))) {
			$fileName = 'lightbox.min.css';
			$pathTheme = get_stylesheet_directory() ."/{$fileName}";
			$haveThemeCss = is_readable($pathTheme);
		}
	}
	if($haveThemeCss == false){
		$path = LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/styles/' . $fileName;
		if(!is_readable($path)) {
			$fileName = 'lightbox.min.css';
		}
	}
	$uri = ( $haveThemeCss )
		? get_stylesheet_directory_uri().'/'.$fileName
		: LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/styles/' . $fileName;
	wp_enqueue_style('jquery.lightbox.min.css', $uri, false, JQLB_VERSION );
	wp_enqueue_style(
		'jqlb-overrides',
		LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/styles/overrides.css',
		false, JQLB_VERSION
	);

	// Add inline styles for new nav arrow styling
	// Needed to apply styles to :before pseudo-selectors
	$nav_arrow_color = get_option('jqlb_navArrowColor');
	$nav_background_color = get_option('jqlb_navBackgroundColor');
	$border_width = get_option('jqlb_borderSize');
	$has_box_shadow = get_option('jqlb_boxShadow');
	$has_info_bar = get_option('jqlb_showInfoBar');
	$image_box_shadow = $has_box_shadow ? '0 0 4px 2px rgba(0,0,0,.2)' : '';
	$infobar_box_shadow = ($has_box_shadow && $has_info_bar)
		? '0 -4px 0 0 #fff, 0 0 4px 2px rgba(0,0,0,.1);'
		: '';
	$custom_css = "
		#outerImageContainer {
			box-shadow: {$image_box_shadow};
		}
		#imageContainer{
			padding: {$border_width}px;
		}
		#imageDataContainer {
			box-shadow: {$infobar_box_shadow};
		}
		#prevArrow,
		#nextArrow{
			background-color: {$nav_background_color};
			color: {$nav_arrow_color};
		}";
	wp_add_inline_style( 'jqlb-overrides', $custom_css );
}

function jqlb_js() {
	if(is_admin() || is_feed()){return;}
	$script_version = defined( 'WP_DEBUG' ) && WP_DEBUG === true ? time() : JQLB_VERSION;
	$enqueInFooter = get_option('jqlb_enqueue_in_footer') ? true : false;

	wp_enqueue_script('jquery', '', array(), false, $enqueInFooter);
	wp_enqueue_script(
		'wp-jquery-lightbox-swipe',
		LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/vendor/jquery.touchwipe.min.js',
		array('jquery'),
		$script_version,
		$enqueInFooter
	);
	wp_enqueue_script(
		'wp-jquery-lightbox-purify',
		LIGHTPRESS_PLUGIN_URL . 'inc/purify.min.js',
		array(),
		$script_version,
		$enqueInFooter
	);

	if ( get_option( 'jqlb_pinchzoom' ) === '1' ) {
		wp_enqueue_script(
			'wp-jquery-lightbox-panzoom',
			LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/vendor/panzoom.min.js',
			array('jquery'),
			$script_version,
			$enqueInFooter
		);
		wp_enqueue_script(
			'wp-jquery-lightbox',
			LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/jquery.lightbox.js',
			array('jquery', 'wp-jquery-lightbox-purify'),
			$script_version,
			$enqueInFooter
		);
	} else {
		wp_enqueue_script(
			'wp-jquery-lightbox',
			LIGHTPRESS_PLUGIN_URL . 'lightboxes/wp-jquery-lightbox/jquery.lightbox.js',
			array('jquery'),
			$script_version,
			$enqueInFooter
		);
	}

	wp_localize_script('wp-jquery-lightbox', 'JQLBSettings', array(
		'showTitle'	=> get_option('jqlb_showTitle'),
		'useAltForTitle'	=> get_option('jqlb_useAltForTitle'),
		'showCaption'	=> get_option('jqlb_showCaption'),
		'showNumbers' => get_option('jqlb_showNumbers'),
		'fitToScreen' => get_option('jqlb_resize_on_demand'),
		'resizeSpeed' => get_option('jqlb_resize_speed'),
		'showDownload' => get_option('jqlb_showDownload'),
		'navbarOnTop' => get_option('jqlb_navbarOnTop'),
		'marginSize' => get_option('jqlb_margin_size'),
		'mobileMarginSize' => get_option('jqlb_mobile_margin_size'),
		'slideshowSpeed' => get_option('jqlb_slideshow_speed'),
		'allowPinchZoom' => get_option('jqlb_pinchzoom'),
		'borderSize' => get_option('jqlb_borderSize'),
		'borderColor' => get_option('jqlb_borderColor'),
		'overlayColor' => get_option('jqlb_overlayColor'),
		'overlayOpacity' => get_option('jqlb_overlayOpacity'),
		'newNavStyle' => get_option('jqlb_newNavStyle'),
		'fixedNav' => get_option('jqlb_fixedNav'),
		'showInfoBar' => get_option('jqlb_showInfoBar'),
		/* translation */
		'prevLinkTitle' => __('previous image', 'wp-jquery-lightbox'),
		'nextLinkTitle' => __('next image', 'wp-jquery-lightbox'),
		'closeTitle' => __('close image gallery', 'wp-jquery-lightbox'),
		'image' => __('Image ', 'wp-jquery-lightbox'),
		'of' => __(' of ', 'wp-jquery-lightbox'),
		'download' => __('Download', 'wp-jquery-lightbox'),
		'pause' => __('(Pause Slideshow)', 'wp-jquery-lightbox'),
		'play' => __('(Play Slideshow)', 'wp-jquery-lightbox')
	));
}

function jqlb_lightbox_comment($comment){
	$comment = str_replace('rel=\'external nofollow\'','', $comment);
	$comment = str_replace('rel=\'nofollow\'','', $comment);
	$comment = str_replace('rel="external nofollow"','', $comment);
	$comment = str_replace('rel="nofollow"','', $comment);
	return jqlb_autoexpand_rel_wlightbox($comment);
}

function jqlb_autoexpand_rel_wlightbox($content) {
	if(get_option('jqlb_automate') == 1){
		global $post;
		$id = isset($post->ID) ? $post->ID : -1;
		$content = jqlb_do_regexp($content, $id);
	}
	return $content;
}
function jqlb_apply_lightbox($content, $id = -1){
	if(!isset($id) || $id === -1){
		$id = time().rand(0, 32768);
	}
	return jqlb_do_regexp($content, $id);
}

//Matt's version to support multiple rel values
//https://wordpress.org/support/topic/fix-for-auto-lightboxing-links-that-contain-rel-attributes-already?replies=12
function jqlb_do_regexp_multirel($content, $id){
	$id = esc_attr($id);
	$a_tag_img_regex = "/(<a[^>]+href=['\"][^>]+\\.(?:bmp|gif|jpg|jpeg|png|webp)[^>]+)>/i";
	if (preg_match_all($a_tag_img_regex, $content, $a_tag_matches, PREG_SET_ORDER)) {
		foreach ($a_tag_matches as $a_tag) {
			$new_a_tag = $a_tag[0];
			$rel_regex = "/(rel=['\"])(?![^>]*?(?:lightbox|nolb|nobox))([^'\"]+)(['\"])/i";
			$new_a_tag = preg_replace($rel_regex, '$1lightbox['.$id.'] $2$3', $new_a_tag);

			$no_rel_regex = "/(<a(?![^>]*?rel=['\"].+)[^>]+href=['\"][^>]+\\.(?:bmp|gif|jpg|jpeg|png|webp)[^>]+)>/i";
			$new_a_tag = preg_replace($no_rel_regex, '$1 rel="lightbox['.$id.']">', $new_a_tag);

			if ($new_a_tag != $a_tag[0]) $content = str_replace($a_tag[0], $new_a_tag, $content);
		}
	}
	return $content;
}


/* automatically insert rel="lightbox[nameofpost]" to every image with no manual work.
	if there are already rel="lightbox[something]" attributes, they are not clobbered.
	Michael Tyson, you are a regular expressions god! - http://atastypixel.com */
function jqlb_do_regexp($content, $id){
	$id = esc_attr($id);
	$content = preg_replace('/\s+rel="attachment wp-att-[0-9]+"/i', '', $content); //remove WP 4.4 garbage
	$pattern = "/(<a(?![^>]*?rel=['\"]lightbox.*)[^>]*?href=['\"][^'\"]+?\.(?:bmp|gif|jpg|jpeg|png|webp)(\?\S{0,}){0,1}['\"][^\>]*)>/i";
	$replacement = '$1 rel="lightbox['.$id.']">';
	return preg_replace($pattern, $replacement, $content);
}

//this filter runs when adding images to a post in the WP 4.4 editor
//removing the garbage rel before they're saved to the database.
function jqlb_remove_rel($content, $id){
	return preg_replace('/\s+rel="attachment wp-att-[0-9]+"/i', '', $content);
}

function jqlb_filter_groups($html, $attr) {//runs on the post_gallery filter.
	global $jqlb_group;
	if(empty($attr['group'])){
		$jqlb_group = -1;
		remove_filter('wp_get_attachment_link','jqlb_lightbox_gallery_links',10,1);
	}else{
		$jqlb_group = $attr['group'];
		add_filter('wp_get_attachment_link','jqlb_lightbox_gallery_links',10,1);
	}
	return '';
}
function jqlb_lightbox_gallery_links($html){ //honors our custom group-attribute of the gallery shortcode.
	global $jqlb_group;
	if(!isset($jqlb_group) || $jqlb_group == -1){return $html;}
    return str_replace('<a','<a rel="lightbox['.$jqlb_group.']"', $html);
}

function jqlb_bool_intval($v){
	return $v == 1 ? '1' : '0';
}

function jqlb_pos_intval($v){
	return abs(intval($v));
}

function jqlb_save_date() {
	if ( ! is_admin() ) {
		return;
	}

	$date = get_option( 'jqlb_date' );
	if ( $date ) {
		return;
	}

	$now = new DateTimeImmutable( date( 'Y-m-d' ) );
	$now_as_string = $now->format( 'Y-m-d' );
	update_option( 'jqlb_date', $now_as_string );
}

function jqlb_options_panel(){
	if(!function_exists('current_user_can') || !current_user_can('manage_options')){
			die(__('Cheatin&#8217; uh?', 'wp-jquery-lightbox'));
	}
	add_action('in_admin_footer', 'jqlb_add_admin_footer');
	?>

	<div class="wrap">
	<h2>jQuery Lightbox</h2>
	<form method="post" action="options.php">
		<table>
		<?php settings_fields('jqlb-settings-group'); ?>
			<tr valign="baseline" colspan="2">
				<td colspan="">
					<?php $check = get_option('jqlb_automate') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_automate" name="jqlb_automate" value="1" <?php echo $check; ?>/>
					<label for="jqlb_automate" title="<?php _e('Let the plugin add necessary html to image links', 'wp-jquery-lightbox') ?>"> <?php _e('Auto-lightbox image links', 'wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td>
					<input type="text" id="jqlb_borderSize" name="jqlb_borderSize" value="<?php echo floatval(get_option('jqlb_borderSize')) ?>" size="3" />
					<label for="jqlb_borderSize"><?php _e('Width of border around image (default: 8)', 'wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td colspan="2">
					<input type="text" id="jqlb_borderColor" name="jqlb_borderColor" value="<?php echo esc_html( get_option('jqlb_borderColor', '#fff')) ?>" size="3" />
					<label for="jqlb_borderColor"><?php _e('Border color (default #fff).','wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td colspan="2">
					<input type="text" id="jqlb_overlayColor" name="jqlb_overlayColor" value="<?php echo esc_html( get_option('jqlb_overlayColor', '#000000')) ?>" size="3" />
					<label for="jqlb_overlayColor"><?php _e('Overlay color (default #fff).','wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td colspan="2">
					<input type="text" id="jqlb_overlayOpacity" name="jqlb_overlayOpacity" value="<?php echo esc_html( get_option('jqlb_overlayOpacity', '.6')) ?>" size="3" />
					<label for="jqlb_overlayOpacity"><?php _e('Overlay opacity (0-1, default .6).','wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr>
				<td>
					<?php $check = get_option('jqlb_boxShadow') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_boxShadow" name="jqlb_boxShadow" value="1" <?php echo $check; ?> />
					<label for="jqlb_boxShadow"> <?php _e('Add box shadow around image', 'wp-jquery-lightbox') ?> </label>
				</td>
			</tr>
			<tr>
				<td>
					<?php $check = get_option('jqlb_newNavStyle') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_newNavStyle" name="jqlb_newNavStyle" value="1" <?php echo $check; ?> />
					<label for="jqlb_newNavStyle"> <?php _e('New nav arrow style (recommended)', 'wp-jquery-lightbox') ?> </label>
				</td>
			</tr>
			<tr>
				<td>
					<?php $check = get_option('jqlb_fixedNav') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_fixedNav" name="jqlb_fixedNav" value="1" <?php echo $check; ?> />
					<label for="jqlb_fixedNav"> <?php _e('Fixed nav position (reqires new nav style)', 'wp-jquery-lightbox') ?> </label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td colspan="2">
					<input type="text" id="jqlb_navArrowColor" name="jqlb_navArrowColor" value="<?php echo esc_html( get_option('jqlb_navArrowColor', '#000000')) ?>" size="3" />
					<label for="jqlb_navArrowColor"><?php _e('Nav arrow color (ie #000 or rgba(0,0,0,.5) for transparency, requires new nav style).','wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td colspan="2">
					<input type="text" id="jqlb_navBackgroundColor" name="jqlb_navBackgroundColor" value="<?php echo esc_html( get_option('jqlb_navBackgroundColor', '#ffffff')) ?>" size="3" />
					<label for="jqlb_navBackgroundColor"><?php _e('Nav background color (ie #fff or rgba(255,255,255,.5) for transparency, requires new nav style)','wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr>
				<td>
					<?php $check = get_option('jqlb_showInfoBar') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_showInfoBar" name="jqlb_showInfoBar" value="1" <?php echo $check; ?> />
					<label for="jqlb_showInfoBar"> <?php _e('Show info bar', 'wp-jquery-lightbox') ?> </label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td colspan="2">
				<?php $check = get_option('jqlb_navbarOnTop') ? ' checked="yes" ' : ''; ?>
				<input type="checkbox" id="jqlb_navbarOnTop" name="jqlb_navbarOnTop" value="1" <?php echo $check; ?> />
				<label for="jqlb_navbarOnTop">
					<?php _e('Show info bar on top', 'wp-jquery-lightbox') ?>
				</label>
				</td>
			</tr>
			<tr>
				<td>
					<?php $check = get_option('jqlb_showTitle') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_showTitle" name="jqlb_showTitle" value="1" <?php echo $check; ?> />
					<label for="jqlb_showTitle"> <?php _e('Show title', 'wp-jquery-lightbox') ?> </label>
				</td>
			</tr>
			<tr>
				<td>
					<?php $check = get_option('jqlb_useAltForTitle') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_useAltForTitle" name="jqlb_useAltForTitle" value="1" <?php echo $check; ?> />
					<label for="jqlb_useAltForTitle"> <?php _e('Use alt tag for title if it exists', 'wp-jquery-lightbox') ?> </label>
				</td>
			</tr>
			<tr>
				<td>
					<?php $check = get_option('jqlb_showCaption') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_showCaption" name="jqlb_showCaption" value="1" <?php echo $check; ?> />
					<label for="jqlb_showCaption"> <?php _e('Show caption', 'wp-jquery-lightbox') ?> </label>
				</td>
			</tr>
			<tr valign="baseline">
				<td>
					<?php $check = get_option('jqlb_showNumbers') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_showNumbers" name="jqlb_showNumbers" value="1" <?php echo $check; ?> />
					<label for="jqlb_showNumbers"> <?php _e('Show image numbers:', 'wp-jquery-lightbox');  printf(' <code>"%s # %s #"</code>', __('Image ', 'wp-jquery-lightbox'), __(' of ', 'wp-jquery-lightbox')); ?> </label>
				</td>
			</tr>
			<tr valign="baseline">
				<td>
					<?php $check = get_option('jqlb_showDownload') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_showDownload" name="jqlb_showDownload" value="1" <?php echo $check; ?> />
					<label for="jqlb_showDownload"> <?php _e('Show download link', 'wp-jquery-lightbox') ?> </label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td>
					<?php $check = get_option('jqlb_resize_on_demand') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_resize_on_demand" name="jqlb_resize_on_demand" value="1" <?php echo $check; ?> />
					<label for="jqlb_resize_on_demand"><?php _e('Shrink large images to fit smaller screens', 'wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td>
					<?php $check = get_option('jqlb_pinchzoom') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_pinchzoom" name="jqlb_pinchzoom" value="1" <?php echo $check; ?> />
					<label for="jqlb_pinchzoom"><?php _e('Allow pinch zoom on mobile', 'wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td colspan="2">
					<?php $check = get_option('jqlb_comments') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_comments" name="jqlb_comments" value="1" <?php echo $check; ?>/>
					<label for="jqlb_comments" title="<?php _e('Note: this will disable the nofollow-attribute of comment links, that otherwise interfere with the lightbox.', 'wp-jquery-lightbox') ?>"> <?php _e('Enable lightbox in comments (disables <a href="http://codex.wordpress.org/Nofollow">the nofollow attribute!</a>)', 'wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td>
					<input type="text" id="jqlb_margin_size" name="jqlb_margin_size" value="<?php echo floatval(get_option('jqlb_margin_size')) ?>" size="3" />
					<label for="jqlb_margin_size" title="<?php _e('Distance between image and screen edge on desktop/laptop.', 'wp-jquery-lightbox') ?>"><?php _e('Minimum margin to screen edge (default 40)', 'wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td>
					<input type="text" id="jqlb_mobile_margin_size" name="jqlb_mobile_margin_size" value="<?php echo floatval(get_option('jqlb_mobile_margin_size')) ?>" size="3" />
					<label for="jqlb_mobile_margin_size" title="<?php _e('Distance between image and screen edge on mobile.', 'wp-jquery-lightbox') ?>"><?php _e('Minimum margin to screen edge on mobile (default 10)', 'wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td>
					<?php $check = get_option('jqlb_use_theme_styles') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_use_theme_styles" name="jqlb_use_theme_styles" value="1" <?php echo $check; ?> />
					<label for="jqlb_use_theme_styles" title="You must put lightbox.min.css or lightbox.min.[locale].css in your theme's style-folder. This is good to keep your CSS edits when updating the plugin."><?php _e('Use custom stylesheet', 'wp-jquery-lightbox'); ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td>
					<?php $check = get_option('jqlb_enqueue_in_footer') ? ' checked="yes" ' : ''; ?>
					<input type="checkbox" id="jqlb_enqueue_in_footer" name="jqlb_enqueue_in_footer" value="1" <?php echo $check; ?> />
					<label for="jqlb_enqueue_in_footer" title="<?php _e('Uncheck this box to load jQuery and lightbox in the header of your page.', 'wp-jquery-lightbox'); ?>"><?php _e('Load JavaScripts in page footer', 'wp-jquery-lightbox'); ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td colspan="2">
					<input type="text" id="jqlb_resize_speed" name="jqlb_resize_speed" value="<?php echo intval(get_option('jqlb_resize_speed')) ?>" size="3" />
					<label for="jqlb_resize_speed"><?php _e('Animation duration (in milliseconds) ', 'wp-jquery-lightbox') ?></label>
				</td>
			</tr>
			<tr valign="baseline" colspan="2">
				<td colspan="2">
					<input type="text" id="jqlb_slideshow_speed" name="jqlb_slideshow_speed" value="<?php echo intval(get_option('jqlb_slideshow_speed')) ?>" size="3" />
					<label for="jqlb_slideshow_speed"><?php _e('Slideshow speed (in milliseconds). 0 to disable.','wp-jquery-lightbox') ?></label>
				</td>
			</tr>
		 </table>
		<p class="submit">
		  <input type="submit" name="Submit" value="<?php _e('Save Changes', 'wp-jquery-lightbox') ?>" />
		</p>
	</form>
	<?php
		$locale = jqlb_get_locale();
		$diskfile = plugin_dir_path(__FILE__)."languages/howtouse-{$locale}.html";
		if (!file_exists($diskfile)){
			$diskfile = plugin_dir_path(__FILE__).'languages/howtouse.html';
		}
		$text = false;
		if(function_exists('file_get_contents')){
			$text = @file_get_contents($diskfile);
		} else {
			$text = @file($diskfile);
			if($text !== false){
				$text = implode("", $text);
			}
		}
		if($text === false){
			$text = '<p>The documentation files are missing! Try <a href="http://wordpress.org/extend/plugins/wp-jquery-lightbox/">downloading</a> and <a href="http://wordpress.org/extend/plugins/wp-jquery-lightbox/installation/">re-installing</a> this plugin.</p>';
		}
		echo $text;
	?>
	</div>
<?php }?>
