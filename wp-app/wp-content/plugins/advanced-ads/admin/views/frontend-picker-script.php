<script>
jQuery( document ).ready( function(){
	// set element from frontend into placement input field
	if( localStorage.getItem( 'advads_frontend_element' )){
		var placement = localStorage.getItem( 'advads_frontend_picker' );
		var id = 'advads-frontend-element-' + placement;
		jQuery( '[id="' + id + '"]' ).find( '.advads-frontend-element' ).val( localStorage.getItem( 'advads_frontend_element' ) );

		var action = localStorage.getItem( 'advads_frontend_action' );
		if (typeof(action) !== 'undefined'){
			var show_all_link = jQuery( 'a[data-placement="' + placement + '"]');
			var tr = jQuery( show_all_link ).closest( 'tr.advanced-ads-placement-row' )
			if ( tr ) {
				tr.data( 'touched', true )
			}

			// Auto-save the placement after selecting an element in the frontend.
			var param = {
				action: 'advads-update-frontend-element',
				nonce: advadsglobal.ajax_nonce,
			}
			var $form = jQuery( '#advanced-ads-placements-form' );
			var query = $form.find( '[id="single-placement-' + placement + '"]' ).find( 'input, select' )
			.serialize() + '&' + jQuery.param( param );

			$form.find( ':submit' ).attr( 'disabled', true );
			jQuery.post( ajaxurl, query ).always( function() {
				$form.find( ':submit' ).attr( 'disabled', false );
			} );
		}
		localStorage.removeItem( 'advads_frontend_action' );
		localStorage.removeItem( 'advads_frontend_element' );
		localStorage.removeItem( 'advads_frontend_picker' );
		localStorage.removeItem( 'advads_prev_url' );
		localStorage.removeItem( 'advads_frontend_pathtype' );
		localStorage.removeItem( 'advads_frontend_boundary' );
		localStorage.removeItem( 'advads_frontend_blog_id' );
		localStorage.removeItem( 'advads_frontend_starttime' );
		window.Advanced_Ads_Admin.set_cookie( 'advads_frontend_picker', '', -1 );
	}
	jQuery('.advads-activate-frontend-picker').click(function( e ){
		localStorage.setItem( 'advads_frontend_picker', jQuery( this ).data('placementid') );
		localStorage.setItem( 'advads_frontend_action', jQuery( this ).data('action') );
		localStorage.setItem( 'advads_prev_url', window.location );

		localStorage.setItem( 'advads_frontend_pathtype', jQuery( this ).data('pathtype') );
		localStorage.setItem( 'advads_frontend_boundary', jQuery( this ).data('boundary') );
		localStorage.setItem( 'advads_frontend_blog_id', <?php echo get_current_blog_id(); ?> );
		localStorage.setItem( 'advads_frontend_starttime', (new Date).getTime() );
		window.Advanced_Ads_Admin.set_cookie( 'advads_frontend_picker', jQuery( this ).data('placementid'), null );

		if ( jQuery( this ).data( 'boundary' ) ) {
			/**
			* The boundary is set for the "Content" placement.
			* Perhaps ads through `the_content` are disabled on non-singular pages, so use a singular one.
			*/
			window.location = "<?php echo $this->get_url_for_content_placement_picker(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>";
		} else {
			window.location = "<?php echo home_url(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>";
		}
	});
	// allow to deactivate frontend picker
	if ( localStorage.getItem( 'advads_frontend_picker' ) ) {
		var id = 'advads-frontend-element-' + localStorage.getItem( 'advads_frontend_picker' );
		jQuery( '[id="' + id + '"]' ).find( '.advads-deactivate-frontend-picker' ).show();
	}
	jQuery( '.advads-deactivate-frontend-picker' ).click( function( e ) {
		localStorage.removeItem( 'advads_frontend_action' );
		localStorage.removeItem( 'advads_frontend_element' );
		localStorage.removeItem( 'advads_frontend_picker' );
		localStorage.removeItem( 'advads_prev_url' );
		localStorage.removeItem( 'advads_frontend_pathtype' );
		localStorage.removeItem( 'advads_frontend_boundary' );
		localStorage.removeItem( 'advads_frontend_blog_id' );
		localStorage.removeItem( 'advads_frontend_starttime' );
		window.Advanced_Ads_Admin.set_cookie( 'advads_frontend_picker', '', -1 );
		jQuery('.advads-deactivate-frontend-picker').hide();
	});
});
</script>
