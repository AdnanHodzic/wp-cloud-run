<?php
if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

function dsxmlrpc_action_links($links) {
    $plugin_shortcuts = array(
        '<a style="color:green;" rel="noopener" href="https://wordpress.org/support/plugin/disable-xml-rpc-api/reviews/#new-post" target="_blank">' . __('Rate Plugin', 'dsxmlrpc') . '</a>',
        '<a style="color:#f44336;" rel="noopener" href="https://neatma.com/wpsg-plugin/" target="_blank">' . __('More Protection', 'dsxmlrpc') . '</a>',

    );
    return array_merge($links, $plugin_shortcuts);
}

function dsxmlrpc_admin_notice_wpsg() {
	if (   ! PAnD::is_admin_notice_active( 'wpsg-notice-forever' )  ) {
		return;
	}
	
	?>
	<div data-dismissible="wpsg-notice-forever" id="dsxmlrpc-wpsg-notice" class="notice notice-warning is-dismissible">
	<label class="gftp-plugin-name">WP Security Guard</label>
	<h1>Did you know?</h1>
	<div class="dsxmlrpc-wpsg-notice-innner">
		<p>You can improve your website security by using <strong>  WP Security Guard!</strong> </p>
	</div>
	<span class="dashicons dashicons-external" style="color: #2196f3;vertical-align:bottom;"></span><a href="https://neatma.com/wpsg-plugin/" target="_blank">Learn more</a>
	<span class="dashicons dashicons-calendar" style="margin-left: 15px;color: #009688;vertical-align:-webkit-baseline-middle;"></span><a  style="color:#009688;" class="remind-wpsg" href="#">Remind Me Later</a>
	<span class="dashicons dashicons-dismiss" style="margin-left: 15px;color: #ff5722;vertical-align:-webkit-baseline-middle;"></span><a  style="color:#ff5722;" class="dismiss-wpsg" href="#">Not Intrested!</a>

	</div>
	<style>
	#dsxmlrpc-wpsg-notice.hide,#dsxmlrpc-wpsg-notice .notice-dismiss {
	display:none;
	}
	#dsxmlrpc-wpsg-notice a{
	color: #2196f3;
    vertical-align: sub;
	}
	#dsxmlrpc-wpsg-notice label.gftp-plugin-name {
    background: #4caf50;
    color: #fff;
    padding: 2px 10px;
    position: absolute;
    top: auto;
    bottom: 100%;
    right: 15px;
    -moz-border-radius: 0 0 3px 3px;
    -webkit-border-radius: 0 0 3px 3px;
    border-radius: 4px 4px 0px 0px;
    left: auto;
    font-size: 12px;
    font-weight: bold;
    cursor: auto;
	}
	div#dsxmlrpc-wpsg-notice {
    padding: 10px 15px;
	}
	</style>
	<?php
}

function dsxmlrpc_admin_notice_review() {
		 global $current_screen;
    if ( $current_screen->id !== 'toplevel_page_Security Settings') {
		return;
	}
	
	if (   ! PAnD::is_admin_notice_active( 'dsxmlrpc-notice-15' ) ||  get_option('dsxmlrpc-notice-forever')  ) {
		return;
	}

	?>
	<div data-dismissible="dsxmlrpc-notice-15" id="dsxmlrpc-notice" class="updated notice notice-success is-dismissible">
	<h2>Your website is protected from XML-RPC Brute-force and DDOS attacks!</h2>
	<div class="dsxmlrpc-notice-innner">

		<p>You can help us make this plugin better by reviewing and giving it 5 stars</p>
		<div class="dsxmlrpc-rate">
		<fieldset class="dsxmlrpc-ratings rating-stars"><label for="rating_1"><input class="hidden dsxmlrpc-hidden" id="rating_1" type="radio" name="rating" value="1"><span class="dashicons dashicons-star-empty dashicons-star-filled" style="color:#ffb900 !important;" title="Poor"></span><span class="screen-reader-text">Poor</span></label><label for="rating_2"><input class="hidden dsxmlrpc-hidden" id="rating_2" type="radio" name="rating" value="2"><span class="dashicons dashicons-star-empty dashicons-star-filled" style="color:#ffb900 !important;" title="Works"></span><span class="screen-reader-text">Works</span></label><label for="rating_3"><input class="hidden dsxmlrpc-hidden" id="rating_3" type="radio" name="rating" value="3"><span class="dashicons dashicons-star-empty dashicons-star-filled" style="color:#ffb900 !important;" title="Good"></span><span class="screen-reader-text">Good</span></label><label for="rating_4"><input class="hidden dsxmlrpc-hidden" id="rating_4" type="radio" name="rating" value="4"><span class="dashicons dashicons-star-empty dashicons-star-filled" style="color:#ffb900 !important;" title="Great"></span><span class="screen-reader-text">Great</span></label><label for="rating_5"><input class="hidden dsxmlrpc-hidden" id="rating_5" type="radio" name="rating" checked="checked" value="5"><span class="dashicons dashicons-star-empty dashicons-star-filled" style="color:#ffb900 !important;" title="Fantastic!"></span><span class="screen-reader-text">Fantastic!</span></label></fieldset><input type="hidden" name="rating" id="rating" value="5">
		</div>
		<form action="" method="post" >
		<input  class="button button-primary dsxmlrpc_button" type="submit" name="dsxmlrpc-notice-forever" value="Already Rated" />
		</form>

	</div>
	
	</div>
	<style>
	a.dsxmlrpc-ratings span:hover {
    color: #FF9800 !important;
    }
	@media screen and (min-width: 782px) {
	.dsxmlrpc-notice-innner {
	display: flex;
	}}
	.dsxmlrpc-hidden {
    height: 0;
    width: 0;
    overflow: hidden;
    overflow-x: hidden;
    overflow-y: hidden;
    position: absolute;
    background: none;
    left: -999em;
	}
	.dsxmlrpc-rate {
    top: 5px;
    padding-left: 10px;
    position: relative;
	}
	.dsxmlrpc_button {
    margin: 3px 0 15px 15px !important;
    transition: 500ms;
	}
	</style>
	<?php
}

//add_action( 'admin_notices', 'dsxmlrpc_admin_notice_review' );
//add_action( 'admin_notices', 'dsxmlrpc_admin_notice_wpsg' );