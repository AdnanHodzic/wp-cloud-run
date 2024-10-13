<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://jeanbaptisteaudras.com
 * @since      1.0.0
 *
 * @package    reading-progress-bar
 * @subpackage reading-progress-bar/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    reading-progress-bar
 * @subpackage reading-progress-bar/admin
 * @author     audrasjb <audrasjb@gmail.com>
 */
	// Enqueue styles
	add_action( 'admin_enqueue_scripts', 'enqueue_styles_reading_progressbar_admin' );
	function enqueue_styles_reading_progressbar_admin() {
		wp_enqueue_style( 'wp-color-picker' );
//		wp_enqueue_style( 'rp-admin-styles', plugin_dir_url( __FILE__ ) . 'css/rp-admin.css', array(), '', 'all' );
	}
	
	// Enqueue scripts
	add_action( 'admin_enqueue_scripts', 'enqueue_scripts_reading_progressbar_admin' );
	function enqueue_scripts_reading_progressbar_admin() {
		wp_enqueue_script( 'rp-admin-scripts', plugin_dir_url( __FILE__ ) . 'js/rp-admin.js', array( 'jquery', 'wp-color-picker' ), '', false );
	}	
	
/**
 *
 * Plugin options in reading section
 *
 */
 
add_action( 'admin_menu', 'rp_add_admin_menu' );
add_action( 'admin_init', 'rp_settings_init' );


function rp_add_admin_menu(  ) { 

	add_options_page( __('Reading progressbar options', 'reading-progress-bar'), __('Reading progressbar', 'reading-progress-bar'), 'manage_options', 'reading-progressbar', 'rp_options_page' );
}


function rp_settings_init(  ) { 

	register_setting( 'pluginPage', 'rp_settings' );

	add_settings_section(
		'rp_pluginPage_section', 
		__( 'Reading progressbar options', 'reading-progress-bar' ), 
		'rp_settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'rp_field_height', 
		__( 'Progressbar height (pixels)', 'reading-progress-bar' ), 
		'rp_field_height_render', 
		'pluginPage', 
		'rp_pluginPage_section' 
	);

	add_settings_field( 
		'rp_field_fg_color', 
		__( 'Foreground color', 'reading-progress-bar' ), 
		'rp_field_fg_color_render', 
		'pluginPage', 
		'rp_pluginPage_section' 
	);

	add_settings_field( 
		'rp_field_bg_color', 
		__( 'Background color', 'reading-progress-bar' ), 
		'rp_field_bg_color_render', 
		'pluginPage', 
		'rp_pluginPage_section' 
	);

	add_settings_field( 
		'rp_field_position', 
		__( 'Progressbar position', 'reading-progress-bar' ), 
		'rp_field_position_render', 
		'pluginPage', 
		'rp_pluginPage_section' 
	);

	add_settings_field( 
		'rp_field_custom_position', 
		__( 'Target fixed HTML element class/id to stick the bar on it’s bottom', 'reading-progress-bar' ), 
		'rp_field_custom_position_render', 
		'pluginPage', 
		'rp_pluginPage_section' 
	);

	add_settings_field( 
		'rp_field_templates', 
		__( 'Select templates to apply progressbar', 'reading-progress-bar' ), 
		'rp_field_templates_render', 
		'pluginPage', 
		'rp_pluginPage_section' 
	);

	add_settings_field( 
		'rp_field_posttypes', 
		__( 'Select post types to apply progressbar', 'reading-progress-bar' ), 
		'rp_field_posttypes_render', 
		'pluginPage', 
		'rp_pluginPage_section' 
	);

/*	add_settings_field( 
		'rp_field_mobile', 
		__( 'Display progressbar on mobile devices?', 'reading-progress-bar' ), 
		'rp_field_mobile_render', 
		'pluginPage', 
		'rp_pluginPage_section' 
	);*/

}


function rp_field_height_render(  ) { 
	$options = get_option( 'rp_settings' );
	if (isset($options['rp_field_height'])) {
		$optionHeight = $options['rp_field_height'];
	} else {
		$optionHeight = '';		
	}
	?>
	<input type='number' name='rp_settings[rp_field_height]' value='<?php echo $optionHeight; ?>'>
	<?php
}


function rp_field_fg_color_render(  ) { 
	$options = get_option( 'rp_settings' );
	if (isset($options['rp_field_fg_color'])) {
		$optionForegroundColor = $options['rp_field_fg_color'];
	} else {
		$optionForegroundColor = '';		
	}
	?>
	<input type='text' class='rp-colorpicker' name='rp_settings[rp_field_fg_color]' value='<?php echo $optionForegroundColor; ?>'>
	<?php
}

function rp_field_bg_color_render(  ) { 
	$options = get_option( 'rp_settings' );
	if (isset($options['rp_field_bg_color'])) {
		$optionBackgroundColor = $options['rp_field_bg_color'];
	} else {
		$optionBackgroundColor = '';		
	}
	?>
	<input type='text' class='rp-colorpicker' name='rp_settings[rp_field_bg_color]' value='<?php echo $optionBackgroundColor; ?>'>
	<?php
}

function rp_field_position_render(  ) { 
	$options = get_option( 'rp_settings' );
	if (isset($options['rp_field_position'])) {
		$optionPosition = $options['rp_field_position'];
	} else {
		$optionPosition = '';		
	}
	?>
	<select name='rp_settings[rp_field_position]'>
		<option value='top' <?php selected( $optionPosition, 'top' ); ?>><?php echo __('Top', 'reading-progress-bar'); ?></option>
		<option value='bottom' <?php selected( $optionPosition, 'bottom' ); ?>><?php echo __('Bottom', 'reading-progress-bar'); ?></option>
		<option value='custom' <?php selected( $optionPosition, 'custom' ); ?>><?php echo __('Custom', 'reading-progress-bar'); ?></option>
	</select>
	<p class="description"><?php echo __('Note: custom position is not ok with all WordPress themes. It needs a fixed element to stick the progressbar on it. <br />You may need some custom CSS to put the progressbar on the right place as it uses absolute positionning.', 'reading-progress-bar'); ?></p>
<?php
}

function rp_field_custom_position_render(  ) { 
	$options = get_option( 'rp_settings' );
	if (isset($options['rp_field_custom_position'])) {
		$optionCustomPosition = $options['rp_field_custom_position'];
	} else {
		$optionCustomPosition = '';		
	}
	?>
	<input type='text' name='rp_settings[rp_field_custom_position]' value='<?php echo $optionCustomPosition; ?>'>
	<p class="description"><?php echo __('Note: use it only if you have selected <b>custom</b> position before, instead of <b>top</b> or <b>bottom</b>', 'reading-progress-bar'); ?></p>
	<?php
}

function rp_field_templates_render( ) {
	$options = get_option( 'rp_settings' );
	if (isset($options['rp_field_templates'])) {
		$optionTemplates = $options['rp_field_templates'];
		if (isset($optionTemplates['home'])) : $optionTemplatesHome = $optionTemplates['home']; else : $optionTemplatesHome = ''; endif;
		if (isset($optionTemplates['blog'])) : $optionTemplatesBlog = $optionTemplates['blog']; else : $optionTemplatesBlog = ''; endif;
		if (isset($optionTemplates['archive'])) : $optionTemplatesArchive = $optionTemplates['archive']; else : $optionTemplatesArchive = ''; endif;
		if (isset($optionTemplates['single'])) : $optionTemplatesSingle = $optionTemplates['single']; else : $optionTemplatesSingle = ''; endif;
	} else {
		$optionTemplates = '';
		$optionTemplatesHome = '';
		$optionTemplatesBlog = '';
		$optionTemplatesArchive = '';
		$optionTemplatesSingle = '';
	}
	?>
	<p><input type='checkbox' name='rp_settings[rp_field_templates][home]' <?php checked( $optionTemplatesHome == '1' ); ?> value='1' /> <?php echo __('Front-page', 'reading-progress-bar' ); ?></p>
	<p class="description"><?php echo __('(as set in Settings &gt; Reading)', 'reading-progress-bar'); ?></p>
	<p><input type='checkbox' name='rp_settings[rp_field_templates][blog]' <?php checked( $optionTemplatesBlog == '1' ); ?> value='1' /> <?php echo __('Blog page', 'reading-progress-bar' ); ?></p>
	<p class="description"><?php echo __('(as set in Settings &gt; Reading)', 'reading-progress-bar'); ?></p>
	<p><input type='checkbox' name='rp_settings[rp_field_templates][archive]' <?php checked( $optionTemplatesArchive == '1' ); ?> value='1' /> <?php echo __('Archives and categories / taxonomies for posts or custom post types', 'reading-progress-bar' ); ?></p>
	<p class="description"><?php echo __('(you need to include concerned post types below)', 'reading-progress-bar' ); ?>
	<p><input type='checkbox' name='rp_settings[rp_field_templates][single]' <?php checked( $optionTemplatesSingle == '1' ); ?> value='1' /> <?php echo __('Single post / page / custom post type', 'reading-progress-bar'); ?></p>
	<p class="description"><?php echo __('(you need to include concerned post types below)', 'reading-progress-bar' ); ?>
	<?php
}

function rp_field_posttypes_render( ) {
	$options = get_option( 'rp_settings' );
	$optionNamePostType = '';
	if (isset($options['rp_field_posttypes'])) {
		$optionPostTypes = $options['rp_field_posttypes'];
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $post_types as $type => $obj ) {
			if (isset($optionPostTypes[$obj->name])) : $optionNamePostType = $optionPostTypes[$obj->name]; else : $optionNamePostType = ''; endif;
			?>
			<p><input type='checkbox' name='rp_settings[rp_field_posttypes][<?php echo $obj->name; ?>]' <?php checked( $optionNamePostType == '1' ); ?> value='1' /> <?php echo $obj->labels->name; ?></p>
			<?php
		}
	} else {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $post_types as $type => $obj ) {
			?>
			<p><input type='checkbox' name='rp_settings[rp_field_posttypes][<?php echo $obj->name; ?>]' value='1' /> <?php echo $obj->labels->name; ?></p>
			<?php
		}
	}
}


/* preparing mobile support
function rp_field_mobile_render(  ) { 
	$options = get_option( 'rp_settings' );
	if (isset($options['rp_field_mobile'])) {
		$optionMobile = $options['rp_field_mobile'];
	} else {
		$optionMobile = '';		
	}
	?>
	<p><input type='checkbox' name='rp_settings[rp_field_mobile]' <?php checked( $optionMobile == '1' ); ?> value='1' /> Yes, please</p>
<?php
}
*/


function rp_settings_section_callback(  ) { 

	echo __( 'Check out the plugin options below.', 'reading-progress-bar' );

}


function rp_options_page(  ) { 

	?>
	<form action='options.php' method='post'>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php

}
