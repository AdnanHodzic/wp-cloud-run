(function($) {
	$( document ).ready( function() {
		var container = $('.lightpress-review-notice');
		if ( container.length ) {
			container.find( '.lightpress-review-actions a' ).click(function() {
				container.remove();
				var rateAction = $( this ).attr( 'data-rate-action' );
				$.post(
					ajaxurl,
					{
						action: 'lightpress-review-action',
						rate_action: rateAction,
						_n: container.find( 'ul:first' ).attr( 'data-nonce' )
					},
					function( result ) {}
				);

				if ( 'do-rate' !== rateAction ) {
					return false;
				}
			});
		}
	});
})( jQuery );
