<?php

/**
 * The public-specific functionality of the plugin.
 *
 * @link       http://jeanbaptisteaudras.com
 * @since      1.0.0
 *
 * @package    reading-progress-bar
 * @subpackage reading-progress-bar/public
 */

/**
 * The public-specific functionality of the plugin.
 *
 * @package    reading-progress-bar
 * @subpackage reading-progress-bar/admin
 * @author     audrasjb <audrasjb@gmail.com>
 */
	function rp_register_resources_reading_progressbar_public() {
		$enqueue_resources = false;
		if ( get_option( 'rp_settings' ) ) {
			$rpSettings = get_option( 'rp_settings' );
			$rpHeight = $rpSettings['rp_field_height'];
			$rpForegroundColor = $rpSettings['rp_field_fg_color'];
			$rpBackgroundColor = $rpSettings['rp_field_bg_color'];
			$rpPosition = $rpSettings['rp_field_position'];
			$rpCustomPosition = $rpSettings['rp_field_custom_position'];
			if ( isset( $rpSettings['rp_field_templates'] ) ) { 
				$optionTemplates = $rpSettings['rp_field_templates'];
				if ( isset($optionTemplates['home']) && (is_home() && is_front_page() || is_front_page()) ) {
					$enqueue_resources = true;
				} elseif ( isset($optionTemplates['blog']) && (is_home() && !is_front_page()) ) {
					$enqueue_resources = true;
				} elseif ( isset($optionTemplates['archive']) && (is_archive()) ) {
					$enqueue_resources = true;
				} elseif ( isset($optionTemplates['single']) && (is_singular() && !is_front_page()) ) {
					$optionPostTypes = $rpSettings['rp_field_posttypes'];
					$currentPostType = get_post_type();
					if (isset($optionPostTypes[$currentPostType])) {
						$enqueue_resources = true;
					} 
				} 
			}
		}
		if ( true === $enqueue_resources ) {
			wp_enqueue_style( 'rp-public-styles', plugin_dir_url( __FILE__ ) . 'css/rp-public.css', array(), '', 'all' );
			wp_enqueue_script( 'rp-public-scripts', plugin_dir_url( __FILE__ ) . 'js/rp-public.js', array( 'jquery' ), '', false );
		}
	}
 	add_action( 'wp_enqueue_scripts', 'rp_register_resources_reading_progressbar_public' );

	function rp_show_it() {
		if ( get_option( 'rp_settings' ) ) {
			$rpSettings = get_option( 'rp_settings' );
			$rpHeight = $rpSettings['rp_field_height'];
			$rpForegroundColor = $rpSettings['rp_field_fg_color'];
			$rpBackgroundColor = $rpSettings['rp_field_bg_color'];
			$rpPosition = $rpSettings['rp_field_position'];
			$rpCustomPosition = $rpSettings['rp_field_custom_position'];
			if ( isset( $rpSettings['rp_field_templates'] ) ) { 
				$optionTemplates = $rpSettings['rp_field_templates'];
				if ( isset($optionTemplates['home']) && (is_home() && is_front_page() || is_front_page()) ) {
					echo '<progress class="readingProgressbar" 
						data-height="' . $rpHeight . '" 
						data-position="'. $rpPosition .'" 
						data-custom-position="'. $rpCustomPosition .'" 
						data-foreground="' . $rpForegroundColor . '" 
						data-background="' . $rpBackgroundColor . '" 
						value="0"></progress>';
				} elseif ( isset($optionTemplates['blog']) && (is_home() && !is_front_page()) ) {
					echo '<progress class="readingProgressbar" 
						data-height="' . $rpHeight . '" 
						data-position="'. $rpPosition .'" 
						data-custom-position="'. $rpCustomPosition .'" 
						data-foreground="' . $rpForegroundColor . '" 
						data-background="' . $rpBackgroundColor . '" 
						value="0"></progress>';
				} elseif ( isset($optionTemplates['archive']) && (is_archive()) ) {
					echo '<progress class="readingProgressbar" 
						data-height="' . $rpHeight . '" 
						data-position="'. $rpPosition .'" 
						data-custom-position="'. $rpCustomPosition .'" 
						data-foreground="' . $rpForegroundColor . '" 
						data-background="' . $rpBackgroundColor . '" 
						value="0"></progress>';
				} elseif ( isset($optionTemplates['single']) && (is_singular() && !is_front_page()) ) {
					$optionPostTypes = $rpSettings['rp_field_posttypes'];
					$currentPostType = get_post_type();
					if (isset($optionPostTypes[$currentPostType])) {
						echo '<progress class="readingProgressbar" 
							data-height="' . $rpHeight . '" 
							data-position="'. $rpPosition .'" 
							data-custom-position="'. $rpCustomPosition .'" 
							data-foreground="' . $rpForegroundColor . '" 
							data-background="' . $rpBackgroundColor . '" 
							value="0"></progress>';
					} 
				} 
			}
		}
	}
	add_action( 'wp_footer', 'rp_show_it', 100 );
