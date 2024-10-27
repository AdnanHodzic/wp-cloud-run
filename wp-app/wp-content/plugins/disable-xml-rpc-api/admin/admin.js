(function ($) {
			//shorthand for ready event.
			$(
				function () {
					$( 'div[data-dismissible] a.dismiss-wpsg' ).click(
						function (event) {
							event.preventDefault();
							var $this = $( this );

							var attr_value, option_name, dismissible_length, data;

							attr_value = $this.parent().attr( 'data-dismissible' ).split( '-' );
							console.log(attr_value);
							// remove the dismissible length from the attribute value and rejoin the array.
							dismissible_length = attr_value.pop();

							option_name = attr_value.join( '-' );

							data = {
								'action': 'dismiss_admin_notice',
								'option_name': option_name,
								'dismissible_length': dismissible_length,
								'nonce': dismissible_notice.nonce
							};

							// We can also pass the url value separately from ajaxurl for front end AJAX implementations
							$.post( ajaxurl, data );
							$this.parent().addClass('hide');
						}
					);
				}
				

				
			)
			$(
				function () {
					$( 'div[data-dismissible] a.remind-wpsg' ).click(
						function (event) {
							event.preventDefault();
							var $this = $( this );

							var attr_value, option_name, data;

							attr_value = $this.parent().attr( 'data-dismissible' ).split( '-' );
							// remove the dismissible length from the attribute value and rejoin the array.
							

							option_name = attr_value.join( '-' );

							data = {
								'action': 'dismiss_admin_notice',
								'option_name': option_name,
								'dismissible_length': 10,
								'nonce': dismissible_notice.nonce
							};

							// We can also pass the url value separately from ajaxurl for front end AJAX implementations
							$.post( ajaxurl, data );
							$this.parent().addClass('hide');
						}
					);
				}
			)

}(jQuery));

jQuery( document ).ready( function( $ ) {
				var ratings = $( '.rating-stars' );
				var selectedClass = 'dashicons-star-filled';

				function dxtoggleStyles( currentInput ) {
					var thisInput = $( currentInput );
					var index = parseInt( thisInput.val() );

					stars.removeClass( selectedClass );
					stars.slice( 0, index ).addClass( selectedClass );
				}

				// If the ratings exist on the page
				if ( ratings.length !== 0 ) {
					var inputs = ratings.find( 'input[type="radio"]' );
					var labels = ratings.find( 'label' );
					var stars = inputs.next();

					inputs.on( 'change', function( event ) {
						dxtoggleStyles( event.target )
					} );
					inputs.on( 'click', function( event ) {
						 window.open("https://wordpress.org/support/plugin/disable-xml-rpc-api/reviews/#new-post");
					} );
					labels.hover( function( event ) {
						$curInput = $( event.currentTarget ).find( 'input' );
						dxtoggleStyles( $curInput );
					}, function () {
						$currentSelected = ratings.find( 'input[type="radio"]:checked' );
						dxtoggleStyles( $currentSelected )
					} );
				}
});