<?php
if( file_exists('/opt/bitnami/wordpress/wp-load.php') ){
    $wp_include = '/opt/bitnami/wordpress/wp-load.php';
} else {
    $wp_include = "../../../wp-load.php";
    $i = 0;
    while (!file_exists($wp_include) && $i++ < 10) {
      $wp_include = "../$wp_include";
    }
}

// load WordPress
require($wp_include);
// check for rights
if ( !is_user_logged_in() && !current_user_can('edit_pages') && !current_user_can('edit_posts') ){
    wp_die( __("You are not allowed to be here", "epcl_framework") );
}	
global $wpdb;
$directory_uri = EPCL_PLUGIN_URL.'shortcodes';
require_once('../fontawesome.php');
$fa_icons = epcl_fontawesome_icons();
$version = '1.6';
?>
<html>
<head>
    <title>EstudioPatagon Shortcodes</title>
    <link rel="stylesheet" type="text/css" href="<?php echo $directory_uri; ?>/dist/lightbox.min.css?v=<?php echo $version; ?>" media="all" />
    <link rel="stylesheet" type="text/css" href="<?php echo includes_url(); ?>css/dashicons.min.css" media="all" />
    <link rel='stylesheet' id='csf-fa-css' href='https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css?ver=4.7.1' type='text/css' media='all' />

    <script src="<?php echo includes_url(); ?>js/tinymce/tiny_mce_popup.js"></script>
    <script src="<?php echo includes_url(); ?>js/jquery/jquery.js"></script>
    <script src="<?php echo includes_url(); ?>js/colorpicker.min.js"></script>
    <script src="<?php echo $directory_uri; ?>/js/select2.min.js"></script>
    <script src="<?php echo $directory_uri; ?>/js/lightbox.js?v=<?php echo $version; ?>"></script>
    
</head>
<body>
	<div id="epcl-shortcodes-form">
        <!-- start: .sidebar -->
		<div class="sidebar">
			<span class="form-title"><img src="<?php echo $directory_uri; ?>/images/lightbox-icon.png"><?php esc_html_e('Shortcodes', 'epcl_framework'); ?></span>
			<ul class="tab-selector">
				<li class="button active" data-tab="button"><a href="#"><i class="dashicons dashicons-admin-links"></i> Button</a></li>
				<li class="icon" data-tab="icon"><a href="#"><i class="dashicons dashicons-star-filled"></i> Icon</a></li>
				<li class="columns" data-tab="columns"><a href="#"><i class="dashicons dashicons-screenoptions"></i> Columns</a></li>
				<li class="box" data-tab="box"><a href="#"><i class="dashicons dashicons-info"></i> Boxes/Alerts</a></li>
				<li class="toggle" data-tab="toggle"><a href="#"><i class="dashicons dashicons-image-flip-vertical"></i> Toggle</a></li>
                <li class="accordion" data-tab="accordions"><a href="#"><i class="dashicons dashicons-image-flip-vertical"></i> Accordion</a></li>
                <li class="tabs" data-tab="tabs"><a href="#"><i class="dashicons dashicons-excerpt-view"></i> Tabs</a></li>
			</ul>
        </div>
        <!-- end: .sidebar -->
        <!-- start: .tabs-container -->
		<div class="tabs-container">

            <!-- start: #button -->
			<div id="button" class="tab" style="display: block;">
                <form>
                    <h2>Add Button</h2>
                    <div class="ep-shortcodes-form-fields">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <th><label>Label</label></th>
                                <td><input type="text" name="label" class="inputbox"></td>
                            </tr>
                            <tr>
                                <th><label>URL</label></th>
                                <td><input type="url" name="url" placeholder="http://www.google.com" class="inputbox"><span class="tip">dont forget http://</span></td>
                            </tr>
                            <tr>
                                <th><label>Type</label></th>
                                <td>
                                    <select name="type" class="inputbox">
                                        <option value="outline">Outline</option>
                                        <option value="flat">Flat</option>
                                        <option value="glow">Glow</option>
                                        <option value="gradient">Gradient</option>                                        
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Color</label></th>
                                <td>
                                    <select name="color" class="inputbox">
                                        <option value="red">Red</option>
                                        <option value="orange">Orange</option>
                                        <option value="yellow">Yellow</option>
                                        <option value="green">Green</option>
                                        <option value="light-blue">Light Blue</option>
                                        <option value="blue">Blue</option>
                                        <option value="purple">Purple</option>
                                        <option value="dark">Dark</option>
                                        <option value="gray">Gray</option>
                                        <option value="white">White</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Size</label></th>
                                <td>
                                    <select name="size" class="inputbox">
                                        <option value="extra-small">Extra Small</option>
                                        <option value="small">Small</option>
                                        <option value="regular" selected="selected">Regular</option>
                                        <option value="large">Large</option>
                                        <option value="extra-large">Extra Large</option>
                                        <option value="fluid">Fluid</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Icon</label></th>
                                <td>
                                    <select name="icon" class="select2 icon inputbox">
                                        <option value="">Select icon</option>
                                        <?php foreach($fa_icons as $fa): ?>
                                            <option value="<?php echo $fa; ?>"><i class="fa <?php echo $fa; ?>"></i> <?php echo $fa; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="tip">Font Icons by: <a href="https://fontawesome.com/v4.7.0/icons/" target="_blank">Font Awesome</a></span>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Link to open in</label></th>
                                <td>
                                    <!-- <select fieldname="target">
                                        <option value="_self">Current Tab/Window</option>
                                        <option value="_blank">New Tab/Window</option>
                                    </select> -->
                                    <label class="radio"><input type="radio" name="target" value="_self" checked class="radio"> Current Tab/Window</label>
                                    <label class="radio"><input type="radio" name="target" value="_blank" class="radio"> New Tab/Window</label>
                                    
                                </td>
                            </tr>
                            <tr>
                                <th><label>Relation</label></th>
                                <td>
                                    <label class="radio"><input type="radio" name="rel" value="dofollow" checked class="radio"> Do Follow</label>
                                    <label class="radio"><input type="radio" name="rel" value="nofollow" class="radio"> No follow</label>
                                    
                                </td>
                            </tr>
                        </table>
                    </div>
                    <input type="submit" style="display:none">
                </form>
            </div>
            <!-- end: #button -->
            
            <!-- start: #icon -->
			<div id="icon" class="tab">
                <form>
                    <h2>Add Icon</h2>
                    <div class="ep-shortcodes-form-fields">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <th><label>Icon</label></th>
                            <td>
                                <select name="icon" class="select2 icon inputbox">
                                    <option value="">Select icon</option>
                                    <?php foreach($fa_icons as $fa): ?>
                                        <option value="<?php echo $fa; ?>"><i class="fa <?php echo $fa; ?>"></i> <?php echo $fa; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="tip">Font Icons by: <a href="https://fontawesome.com/v4.7.0/icons/" target="_blank">Font Awesome</a></span>
                            </td>
                        </tr>
                        <tr>
                            <th><label>Size <small>(in pixels)</small></label></th>
                            <td><input type="number" value="16" id="font-size1" name="size" style="width: 80px;" step="1" min="10" max="99" required>
                            <span class="tip">Values between 10px and 99px. Default: 16px</span></td>
                        </tr>
                        <tr>
                            <th><label>Color</label></th>
                            <td>
                            <input type="text" name="color" value="" required>
                            </td>
                        </tr>
                    </table>
                    </div>
                    <input type="submit" style="display:none">
                </form>
            </div>
            <!-- end: #icon -->        

            <!-- start: #columns -->
			<div id="columns" class="tab" data-item="col">
                <form>
                    <h2>Add Columns</h2>
                    <div class="ep-shortcodes-form-fields">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td colspan="2">
                                    <div class="column-structures">
                                        <label>Choose a column structure</label>
            <a href="#" class="active" split="50|50"><span style="width:50%"><i>½</i></span><span style="width:50%"><i>½</i></span></a>
            <div class="clearfix"></div>
            <a href="#" split="33|33|33"><span style="width:33%"><i>&frac13;</i></span><span style="width:33%"><i>&frac13;</i></span><span style="width:33%"><i>&frac13;</i></span></a>
            <a href="#" split="66|33"><span style="width:67%"><i>&frac23;</i></span><span style="width:33%"><i>&frac13;</i></span></a>
            <a href="#" split="33|66"><span style="width:33%"><i>&frac13;</i></span><span style="width:67%"><i>&frac23;</i></span></a>
            <div class="clearfix"></div>
            <a href="#" split="25|25|25|25"><span style="width:25%"><i>¼</i></span><span style="width:25%"><i>¼</i></span><span style="width:25%"><i>¼</i></span><span style="width:25%"><i>¼</i></span></a>
            <a href="#" split="50|25|25"><span style="width:50%"><i>½</i></span><span style="width:25%"><i>¼</i></span><span style="width:25%"><i>¼</i></span></a>
            <a href="#" split="25|25|50"><span style="width:25%"><i>¼</i></span><span style="width:25%"><i>¼</i></span><span style="width:50%"><i>½</i></span></a>
            <a href="#" split="25|50|25"><span style="width:25%"><i>¼</i></span><span style="width:50%"><i>½</i></span><span style="width:25%"><i>¼</i></span></a>
                                        <input style="display:none" type="text" name="structure" value="50|50" />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Column 1</label></th>
                                <td>
                                    <textarea name="item[0][content]"></textarea>
                                    <input type="hidden" name="item[0][width]" value="50">
                                </td>
                            </tr>
                            <tr>
                                <th><label>Column 2</label></th>
                                <td>
                                    <textarea name="item[1][content]"></textarea>
                                    <input type="hidden" name="item[1][width]" value="50">
                                </td>
                            </tr>
                            <tr>
                                <th><label>Column 3</label></th>
                                <td>
                                    <textarea name="item[2][content]" disabled></textarea>
                                    <input type="hidden" name="item[2][width]" value="25">
                                </td>
                            </tr>
                            <tr>
                                <th><label>Column 4</label></th>
                                <td>
                                    <textarea name="item[3][content]" disabled></textarea>
                                    <input type="hidden" name="item[3][width]" value="25">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <input type="submit" style="display:none">
                </form>
            </div>
            <!-- end: #columns -->
            
             <!-- start: #box -->
			<div id="box" class="tab">
                <form>
                    <h2>Add Box/Alert</h2>
                    <div class="ep-shortcodes-form-fields">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <th><label>Type</label></th>
                                <td>
                                    <select name="type">
                                        <option value="error">Error</option>
                                        <option value="success">Success</option>
                                        <option value="notice">Notice</option>
                                        <option value="information">Information</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Text</label></th>
                                <td><textarea name="content"></textarea></td>
                            </tr>
                        </table>
                    </div>
                    <input type="submit" style="display:none">
                </form>
            </div>
            <!-- end: #box -->

            <!-- start: #toggle -->
			<div id="toggle" class="tab">
                <form>
                    <h2>Add Toggle</h2>
                    <div class="ep-shortcodes-form-fields">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <th><label>Title</label></th>
                                <td><input type="text" name="title" /></td>
                            </tr>
                            <tr>
                                <th><label>Text</label></th>
                                <td><textarea name="content"></textarea></td>
                            </tr>
                            <tr>
                                <th><label>Custom Class</label></th>
                                <td><input type="text" name="custom_class" /></td>
                            </tr>
                            <tr>
                                <th><label>On page load</label></th>
                                <td>
                                    <select name="show">
                                        <option value="closed">Show Closed</option>
                                        <option value="opened">Show Opened</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <input type="submit" style="display:none">
                </form>
            </div>
            <!-- end: #toggle -->
            
            <!-- start: #accordions -->
			<div id="accordions" class="tab" data-item="accordion">
                <form>
                    <h2>Add Accordion</h2>
                    <div class="ep-shortcodes-form-fields">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <th><label>Toggle 1</label></th>
                                <td>
                                    <label>Title</label><input type="text" value="" name="item[0][title]">
                                    <label>Text</label><textarea name="item[0][content]"></textarea>                               
                                    <label>Custom Class</label>
                                    <input type="text" name="item[0][custom_class]" />                            
                                </td>
                            </tr>
                            <tr>
                                <th><label>Toggle 2</label></th>
                                <td>
                                    <label>Title</label><input type="text" value="" name="item[1][title]">
                                    <label>Text</label><textarea name="item[1][content]"></textarea>
                                    <label>Custom Class</label>
                                    <input type="text" name="item[1][custom_class]" />  
                                </td>
                            </tr>
                            <tr>
                                <th><label>Toggle 3</label></th>
                                <td>
                                    <label>Title</label><input type="text" value="" name="item[2][title]">
                                    <label>Text</label><textarea name="item[2][content]"></textarea>
                                    <label>Custom Class</label>
                                    <input type="text" name="item[2][custom_class]" />  
                                </td>
                            </tr>
                        </table>
                    </div>
                    <input type="submit" style="display:none">
                </form>
            </div>
            <!-- end: #accordion -->
            
            <!-- start: #tabs -->
			<div id="tabs" class="tab" data-item="tab">
                <form>
                    <h2>Add Tabs</h2>
                    <div class="ep-shortcodes-form-fields">
                        <table cellpadding="0" cellspacing="0">
                            <!-- <tr>
                                <th><label>Mode</label></th>
                                <td>
                                    <select fieldname="mode">
                                        <option value="horizontal">Horizontal</option>
                                        <option value="vertical">Vertical</option>
                                    </select>
                                </td>
                            </tr> -->
                            <tr>
                                <th><label>Tab 1</label></th>
                                <td><label>Label</label><input type="text" value="" fieldname="label" name="item[0][title]" />
                                <label>Text</label><textarea fieldname="content" name="item[0][content]"></textarea></td>
                            </tr>
                            <tr>
                                <th><label>Tab 2</label></th>
                                <td><label>Label</label><input type="text" value="" fieldname="label" name="item[1][title]" />
                                <label>Text</label><textarea fieldname="content" name="item[1][content]"></textarea></td>
                            </tr>
                            <tr>
                                <th><label>Tab 3</label></th>
                                <td><label>Label</label><input type="text" value="" fieldname="label" name="item[2][title]" />
                                <label>Text</label><textarea fieldname="content" name="item[2][content]"></textarea></td>
                            </tr>
                        </table>
                    </div>
                    <input type="submit" style="display:none">
                </form>
            </div>
            <!-- end: #tabs -->

        </div>
        <!-- end: .tabs-container -->
        
        <!-- start: .actions -->
		<div class="actions">
			<input style="display:none" id="ep-shortcodes-form-type" value="button" />
			<textarea style="display:none" id="shortcode-content"></textarea>
            <input type="button" id="cancel-button" value="<?php esc_attr_e('Cancel', 'epcl_framework'); ?>" onClick="tinyMCEPopup.close();">
			<input type="submit" id="insert-button" value="<?php esc_attr_e('Insert Shortcode', 'epcl_framework'); ?>">
        </div>
        <!-- end: .actions -->
    </div>
</body>
</html>