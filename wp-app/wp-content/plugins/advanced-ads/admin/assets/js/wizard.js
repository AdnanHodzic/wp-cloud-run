jQuery( document ).ready(function ($) {
   // ad wizard
   advads_wizard.init();
});
var advads_wizard = {
    box_order: [ // selectors of elements in the wizard in the correct order
	'#post-body-content, #ad-main-box', // show title and type together
	'#ad-parameters-box',
	// '#ad-output-box',
	'#ad-targeting-box', // display and visitor conditions
	// '#advanced_ads_groupsdiv',
	// '#submitdiv'
    ],
    current_box: '#post-body-content, #ad-main-box', // current active box
    one_column: false, // whether the edit screen is in one-column mode
    status: false, // what is the current status? true if running, else false
	init: function ( status ) { // status can be "start" to start wizard or nothing to not start it
		var _this = this;
		jQuery( '#advads-wizard-controls-next' ).on( 'click', function () {
			_this.next();
		} );
		jQuery( '#advads-wizard-controls-prev' ).on( 'click', function () {
			_this.prev();
		} );
		jQuery( '#advads-wizard-controls-save' ).on( 'click', function ( e ) {
			e.preventDefault();
			jQuery( '#publish' ).trigger( 'click' );
		} ); // save ad
		jQuery( '#advads-wizard-display-conditions-show' ).on( 'click', function () {
			_this.show_conditions( '#ad-targeting-box' );
		} );
		jQuery( '#advads-wizard-visitor-conditions-show' ).on( 'click', function () {
			_this.show_conditions( '#ad-targeting-box' );
		} );
		jQuery( '.advads-show-in-wizard' ).hide();
		jQuery( '#advads-start-wizard' ).on( 'click', function () {
			_this.start();
		} );
		// end wizard when the button was clicked
		jQuery( '.advads-stop-wizard' ).on( 'click', function () {
			_this.close();
		} );
		// jump to next box when ad type is selected
		jQuery( '#advanced-ad-type input' ).on( 'change', function () {
			_this.next();
		} );
	},
    show_current_box: function(){
	jQuery( this.current_box ).removeClass('advads-hide');
    },
    start: function(){ // do stuff when wizard is started
	if ( ! this.is_form_valid() ) {
	    return;
	}
	// show page in 1-column stype
	this.status = true;
	if( jQuery( '#post-body').hasClass('columns-1') ){
	    this.one_column = true;
	} else {
	    jQuery( '#post-body').addClass( 'columns-1' ).removeClass( 'columns-2' );
	}
	// hide all boxes, messages and the headline by adding a hide css class
	jQuery('#post-body-content, .postbox-container .postbox, h1 ~ div:not(.advads-admin-notice):not(#message.updated), h1').addClass( 'advads-hide' );
	// display first box
	this.show_current_box();
	// display close button and controls
	jQuery('#advads-stop-wizard, #advads-wizard-controls').removeClass('hidden')
	this.update_nav();
	// initially hide some elemente
	jQuery( '#advads-ad-description').addClass('advads-hide'); // ad description
	jQuery( '#advads-ad-info').addClass('advads-hide'); // shortcode and php function info
	// hide all elements with 'advads-hide-for-wizard' class
	jQuery( '.advads-hide-in-wizard').hide();
	jQuery( '.advads-show-in-wizard').show();
	jQuery( '#advads-start-wizard' ).hide();
	// remove close-class from ad type box
	jQuery( '#ad-main-box' ).removeClass('closed');
	this.save_hide_wizard( false );
    },
    close: function(){ // close the wizard by showing all elements again
	this.status = false;
	jQuery('.advads-hide').removeClass('advads-hide');
	jQuery('#advads-stop-wizard, #advads-wizard-controls').addClass('hidden');
	if( this.one_column !== true ){
	    jQuery( '#post-body').addClass( 'columns-2' ).removeClass( 'columns-1' );
	}
	// reset current box
	this.current_box = this.box_order[0];
	jQuery('#advads-wizard-welcome').remove();// close wizard welcome message
	// show all elements with 'advads-hide-for-wizard' class
	jQuery( '.advads-hide-in-wizard').show();
	jQuery( '.advads-show-in-wizard').hide();
	jQuery( '#advads-start-wizard' ).show();
	this.save_hide_wizard( true );
    },
    update_nav: function(){ // update navigation, display only valid buttons
	// display all buttons
	jQuery('#advads-wizard-controls button').removeClass('hidden');
	// hide next button if there is no next widget
	var i = this.box_order.indexOf( this.current_box );
	if( i === this.box_order.length - 1 ){
	    jQuery('#advads-wizard-controls-next').addClass('hidden');
	}
	if( i === 0 ){
	    jQuery('#advads-wizard-controls-prev').addClass('hidden');
	}
	// hide save button for first boxes
	if( i <= 1 ){
	    jQuery('#advads-wizard-controls-save').addClass('hidden');
	} else {
	    jQuery('#advads-wizard-controls-save').removeClass('hidden');
	}
    },

    /**
     * Check the form just before some of its fields become hidden.
     *
     * @param {HTMLFormElement} form Form.
     * @return bool
     */
    is_form_valid: function() {
	var form = jQuery( 'form#post' )[0];

	if ( form.checkValidity && form.reportValidity ) {
	    if ( ! form.checkValidity() ) {
		// Highlight invalid fields.
		form.reportValidity()
		return false;
	    }
	    return true;
	} else {
	    // Disable validation so that hidden invalid fields do not prevent the form from being sent.
	    form.setAttribute( 'novalidate', true );
	    return true;
	}

	},
    next: function(){ // show next box
	if ( ! this.is_form_valid() ) {
	    return;
	}
	if( ! this.status ){ return }
	// get index of current item in box-array
	var i = this.box_order.indexOf( this.current_box );
	// check if there is a next index
	if( this.box_order[ i + 1 ] === undefined ){
	    return;
	}
	// hide current element
	jQuery( this.current_box ).addClass('advads-hide')
	// load next element into current
	this.current_box = this.box_order[ i + 1 ];
	this.show_current_box();
	this.update_nav();
    },
    prev: function(){ // show previous box
	if ( ! this.is_form_valid() ) {
	    return;
	}
	// get index of current item in box-array
	var i = this.box_order.indexOf( this.current_box );
	// check if there is a previous index
	if( this.box_order[ i - 1 ] === undefined ){
	    return;
	}
	// hide current element
	jQuery( this.current_box ).addClass('advads-hide')
	// load next element into current
	this.current_box = this.box_order[ i - 1 ];
	this.show_current_box();
	this.update_nav();
    },
    save_hide_wizard: function( hide_wizard ){ // update wizard state (started by default or not?)

	    jQuery.ajax({
		    type: 'POST',
		    url: ajaxurl,
		    data: {
			    action: 'advads-save-hide-wizard-state',
			    hide_wizard: hide_wizard,
			    nonce: advadsglobal.ajax_nonce,
		    },
	    });
    },
    show_conditions: function( box ){ // show the conditions options in display and visitor conditions
	    jQuery( box ).find('.advads-show-in-wizard').hide();
	    jQuery( box ).find('.advads-hide-in-wizard').show();
    },
};
