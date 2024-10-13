/*
 * global js functions for Advanced Ads
 */
jQuery( document ).ready(function () {

	/**
	 * ADMIN NOTICES
	 */
	// close button
	// .advads-notice-dismiss class can be used to add a custom close button (e.g., link)
	jQuery(document).on('click', '.advads-admin-notice .notice-dismiss, .advads-notice-dismiss', function(event){
		event.preventDefault();
	    var messagebox = jQuery(this).parents('.advads-admin-notice');
	    if( messagebox.attr('data-notice') === undefined) return;

	    var query = {
		action: 'advads-close-notice',
		notice: messagebox.attr('data-notice'),
		nonce: advadsglobal.ajax_nonce
	    };
	    // send query
	    jQuery.post(ajaxurl, query, function (r) {
		messagebox.fadeOut();
	    });
	});
	// hide notice for 7 days
	jQuery(document).on('click', '.advads-admin-notice .advads-notice-hide', function(){
	    var messagebox = jQuery(this).parents('.advads-admin-notice');
	    if( messagebox.attr('data-notice') === undefined) return;

	    var query = {
		action: 'advads-hide-notice',
		notice: messagebox.attr('data-notice'),
		nonce: advadsglobal.ajax_nonce
	    };
	    // send query
	    jQuery.post(ajaxurl, query, function (r) {
		messagebox.fadeOut();
	    });
	});

	// autoresponder button
	jQuery('.advads-notices-button-subscribe').on('click', function () {
		if (this.dataset.notice === undefined) {
			return;
		}
		var messageboxes = jQuery(this).parents('.advads-admin-notice');
		if (!messageboxes.length) {
			return;
		}
		var $messagebox = jQuery(messageboxes[0]);
		jQuery('<span class="spinner advads-spinner"></span>').insertAfter(this);

		var query = {
			action: 'advads-subscribe-notice',
			notice: this.dataset.notice,
			nonce: advadsglobal.ajax_nonce
		};
		// send and replace with server message
		jQuery.post(ajaxurl, query)
			.success(function (response) {
				$messagebox.children('p').html(response.data.message);
				$messagebox.addClass('notice-success notice');
			})
			.fail(function (response) {
				$messagebox.children('p').html(response.responseJSON.data.message);
				$messagebox.addClass('notice-error notice');
			})
			.always(function () {
				$messagebox.removeClass('notice-info');
			});
	});

	/**
	 * Functions for Ad Health Notifications in the backend
	 */
	// hide button (adds item to "ignore" list)
	jQuery(document).on('click', '.advads-ad-health-notice-hide', function(){
	    var notice = jQuery(this).parents('li');
	    if( notice.attr('data-notice') === undefined) return;
	    // var list = notice.parent( 'ul' );
	    var remove = jQuery( this ).hasClass( 'remove' );

	    // fix height to prevent the box from going smaller first, then show the "show" link and grow again
        var notice_box = jQuery( '#advads_overview_notices' );
	    notice_box.css( 'height', notice_box.height() + 'px' );

	    var query = {
		action: 'advads-ad-health-notice-hide',
		notice: notice.attr('data-notice'),
		nonce: advadsglobal.ajax_nonce
	    };
	    // fade out first or remove, so users can’t click twice
	    if( remove ){
		notice.remove();
	    } else {
		notice.hide();
	    }
	    // show loader
        notice_box.find('.advads-loader' ).show();
	    advads_ad_health_maybe_remove_list();
	    // send query
	    jQuery.post(ajaxurl, query, function (r) {
		    // update number in menu
		    advads_ad_health_reload_number_in_menu();
		    // update show button
		    advads_ad_health_reload_show_link();
		    // remove the fixed height
		    jQuery( '#advads_overview_notices' ).css( 'height', '' );
		    // remove loader
            notice_box.find('.advads-loader' ).hide();
	    });
	});
	// show all hidden notices
	jQuery(document).on('click', '.adsvads-ad-health-notices-show-hidden', function(){
		advads_ad_health_show_hidden();
	});

	/**
	 * DEACTIVATION FEEDBACK FORM
	 */
	// show overlay when clicked on "deactivate"
	advads_deactivate_link = jQuery('.wp-admin.plugins-php tr[data-slug="advanced-ads"] .row-actions .deactivate a');
	advads_deactivate_link_url = advads_deactivate_link.attr( 'href' );
	advads_deactivate_link.on( 'click', function ( e ) {
		e.preventDefault();
		// only show feedback form once per 30 days
		var c_value = advads_admin_get_cookie( "advanced_ads_hide_deactivate_feedback" );
		if (c_value === undefined){
		    jQuery( '#advanced-ads-feedback-overlay' ).show();
		} else {
		    // click on the link
		    window.location.href = advads_deactivate_link_url;
		}
	});
	// show text fields
	jQuery('#advanced-ads-feedback-content input[type="radio"]').on('click',function () {
		// show text field if there is one
		jQuery(this).parents('li').next('li').children('input[type="text"], textarea').show();
	});
	// handle technical issue feedback in particular
	jQuery('#advanced-ads-feedback-content .advanced_ads_disable_help_text').on('focus',function () {
		// show text field if there is one
		jQuery(this).parents('li').siblings('.advanced_ads_disable_reply').show();
	});
	// send form or close it
	jQuery('#advanced-ads-feedback-content .button').on('click', function ( e ) {
		e.preventDefault();
		var self = jQuery( this );
		// set cookie for 30 days
		advads_store_feedback_cookie();
		// save if plugin should be disabled
		var disable_plugin = self.hasClass('advanced-ads-feedback-not-deactivate') ? false : true;

		// hide the content of the feedback form
		jQuery( '#advanced-ads-feedback-content form' ).hide();
		if ( self.hasClass('advanced-ads-feedback-submit') ) {
			// show feedback message
			jQuery( '#advanced-ads-feedback-after-submit-waiting' ).show();
			if( disable_plugin ){
				jQuery( '#advanced-ads-feedback-after-submit-disabling-plugin' ).show();
			}
			jQuery.ajax({
			    type: 'POST',
			    url: ajaxurl,
			    dataType: 'json',
			    data: {
				action: 'advads_send_feedback',
				feedback: self.hasClass('advanced-ads-feedback-not-deactivate') ? true : false,
				formdata: jQuery( '#advanced-ads-feedback-content form' ).serialize()
			    },
			    complete: function (MLHttpRequest, textStatus, errorThrown) {
				    // deactivate the plugin and close the popup with a timeout
				    setTimeout( function(){
					    jQuery( '#advanced-ads-feedback-overlay' ).remove();
				    }, 2000 )
				    if( disable_plugin ){
					window.location.href = advads_deactivate_link_url;
				    }

			    }
			});
		} else { // currently not reachable
			jQuery( '#advanced-ads-feedback-overlay' ).remove();
			window.location.href = advads_deactivate_link_url;
		}
	});
	// close form and disable the plugin without doing anything
	jQuery('.advanced-ads-feedback-only-deactivate').on('click', function () {
		// hide the content of the feedback form
		jQuery( '#advanced-ads-feedback-content form' ).hide();
		// show feedback message
		jQuery( '#advanced-ads-feedback-after-submit-goodbye' ).show();
		jQuery( '#advanced-ads-feedback-after-submit-disabling-plugin' ).show();
		// set cookie for 30 days
		advads_store_feedback_cookie();
		// wait one second
		setTimeout(function(){
			jQuery( '#advanced-ads-feedback-overlay' ).hide();
			window.location.href = advads_deactivate_link_url;
		}, 1000 );
	});
	// close button for feedback form
	jQuery('#advanced-ads-feedback-overlay-close-button').on('click', function () {
		jQuery( '#advanced-ads-feedback-overlay' ).hide();
	});

	jQuery( '.advads-help' ).on( 'mouseenter', function ( event ) {
		const tooltip = jQuery( event.target ).children( '.advads-tooltip' )[0];
		if ( typeof tooltip === 'undefined' ) {
			return;
		}

		// reset inline styles before getting bounding client rect.
		tooltip.style.position = '';
		tooltip.style.left     = '';
		tooltip.style.top      = '';

		const topParentRect = document.getElementById( 'wpbody' ).getBoundingClientRect(),
			  helpRect      = event.target.getBoundingClientRect(),
			  offsets       = {
				  left: Math.ceil( helpRect.left ) + 20,
				  top:  Math.ceil( helpRect.top ) + 20
			  };
		let tooltipRect     = tooltip.getBoundingClientRect();

		tooltip.style.position = 'fixed';
		tooltip.style.left     = offsets.left + 'px';
		tooltip.style.top      = offsets.top + 'px';

		// check element is not overflowing to the right.
		while ( tooltipRect.right > ( topParentRect.right - 20 ) ) {
			offsets.left -= 10;
			tooltip.style.left = offsets.left + 'px';
			tooltipRect = tooltip.getBoundingClientRect();
		}

		// check element is not overflowing bottom of parent and is within viewport.
		while ( tooltipRect.bottom > ( Math.min( topParentRect.bottom, jQuery( window ).height() ) - 20 ) ) {
			offsets.top -= 10;
			tooltip.style.top = offsets.top + 'px';
			tooltipRect = tooltip.getBoundingClientRect();
		}
	} );
});

// remove duplicate close buttons
jQuery(window).on('load', function () {
	jQuery('a.notice-dismiss').next('button.notice-dismiss').remove();
});

function advads_admin_get_cookie (name) {
	var i, x, y, ADVcookies = document.cookie.split( ";" );
	for (i = 0; i < ADVcookies.length; i++)
	{
		x = ADVcookies[i].substr( 0, ADVcookies[i].indexOf( "=" ) );
		y = ADVcookies[i].substr( ADVcookies[i].indexOf( "=" ) + 1 );
		x = x.replace( /^\s+|\s+$/g, "" );
		if (x === name)
		{
			return unescape( y );
		}
	}
}

/**
 * Store a cookie for 30 days
 * The cookie prevents the feedback form from showing multiple times
 */
function advads_store_feedback_cookie() {
	var exdate = new Date();
	exdate.setSeconds( exdate.getSeconds() + 2592000 );
	document.cookie = "advanced_ads_hide_deactivate_feedback=1; expires=" + exdate.toUTCString() + "; path=/";
}

/**
 * load RSS widget on dashboard page using AJAX to not block rendering the rest of the page
 */
function advads_load_dashboard_rss_widget_content(){
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
		    action: 'advads_load_rss_widget_content',
		    nonce: advadsglobal.ajax_nonce
		},
		success: function (data, textStatus, XMLHttpRequest) {
			if (data) {
				jQuery( '#advads-dashboard-widget-placeholder' ).before( data );
			}
		},
		complete: function (MLHttpRequest, textStatus, errorThrown) {
			// remove the placeholder
			jQuery( '#advads-dashboard-widget-placeholder' ).remove();

		}
	});
}

/**
 * Ad Health Notices in backend
 */
// display notices list (deprecated because we load it without AJAX now)
function advads_display_ad_health_notices(){

	var query = {
	    action: 'advads-ad-health-notice-display',
	    nonce: advadsglobal.ajax_nonce
	};

	var widget = jQuery( '#advads_overview_notices .main' );

	// add loader icon to the widget
	widget.html( '<span class="advads-loader"></span>' );
	// send query
	jQuery.post(ajaxurl, query, function (r) {
		widget.html( r );

		// update number in menu
		advads_ad_health_reload_number_in_menu();
		// update list headlines
		advads_ad_health_maybe_remove_list();

		// remove widget, if return is empty
		if( r === '' ){
			jQuery( '#advads_overview_notices' ).remove();
		}
	});
}
// push a notice to the queue
function advads_push_notice( key, attr = '' ){

	var query = {
	    action: 'advads-ad-health-notice-push-adminui',
	    key: key,
	    attr: attr,
	    nonce: advadsglobal.ajax_nonce
	};
	// send query
	jQuery.post(ajaxurl, query, function (r) {});
}
// show notices of a given type again
function advads_ad_health_show_hidden(){
    var notice_box = jQuery( '#advads__overview_notices');
    var query = {
        action: 'advads-ad-health-notice-unignore',
        nonce: advadsglobal.ajax_nonce
    };
    // show all hidden
    jQuery( document ).find( '#advads_overview_notices .advads-ad-health-notices > li:hidden' ).show();
    // show loader
    notice_box.find('.advads-loader' ).show();
    // update the button
    advads_ad_health_reload_show_link();
    advads_ad_health_maybe_remove_list();
    // send query
    jQuery.post(ajaxurl, query, function (r) {
        // update issue count
        advads_ad_health_reload_number_in_menu();
        // hide loader
        notice_box.find('.advads-loader' ).hide();
    });
};
// hide list fragments if last item was hidden/removed
function advads_ad_health_maybe_remove_list(){
	// get all lists
    	var lists = jQuery( document ).find( '#advads_overview_notices .advads-ad-health-notices' );

	// check each list separately
	lists.each( function( index ) {
		var list = jQuery( this );
		// check if there are visible items in the list
		if( list.find( 'li:visible' ).length ){
			// show parent headline
			list.prev( 'h3' ).show();
		} else {
			// hide parent headline
			list.prev( 'h3' ).hide();

		}
	});

}
// reload number of notices shown in the sidebar based on element in the problems list
function advads_ad_health_reload_number_in_menu(){
	// get number of notices
	var number = jQuery( document ).find( '#advads_overview_notices .advads-ad-health-notices > li:visible' ).length;
	jQuery( '#toplevel_page_advanced-ads .update-count').html( number );
}
// update show X issues link – number and visibility
function advads_ad_health_reload_show_link(){
	// get number of invisible elements
	var number = jQuery( document ).find( '#advads_overview_notices .advads-ad-health-notices > li:hidden' ).length;
	var show_link = jQuery( '.adsvads-ad-health-notices-show-hidden' );
	// update number in the link
	jQuery( '.adsvads-ad-health-notices-show-hidden span.count' ).html( number );
	// hide of show, depending on number
	if( 0 === number ){
		show_link.hide();
	} else {
		show_link.show();
	}
}

//Radio Toggle visibility
function toggle_visibility ( currentElement, toggleElement ) {
	jQuery( toggleElement ).toggle( jQuery(currentElement).val() === 'on');
}
