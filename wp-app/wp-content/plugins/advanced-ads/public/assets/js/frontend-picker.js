// Highlight elements in frontend, if local storage variable is set.
jQuery( document ).ready( function () {

	function is_enabled() {
		if ( ! advads.supports_localstorage() || ! localStorage.getItem( 'advads_frontend_picker' ) ) {
			return false;
		}

		// Check if the frontend picker was started on the current blog.
		if ( window.advads_options.blog_id
			 && localStorage.getItem( 'advads_frontend_blog_id' )
			 && window.advads_options.blog_id !== localStorage.getItem( 'advads_frontend_blog_id' ) ) {
			return false;
		}

		// Deactivate the frontend picker if it was started more than 45 minutes ago.
		if ( localStorage.getItem( 'advads_frontend_starttime' )
			 && parseInt( localStorage.getItem( 'advads_frontend_starttime' ), 10 ) < (
				 new Date().getTime()
			 ) - 45 * 60 * 1000 ) {
			localStorage.removeItem( 'advads_frontend_action' );
			localStorage.removeItem( 'advads_frontend_element' );
			localStorage.removeItem( 'advads_frontend_picker' );
			localStorage.removeItem( 'advads_prev_url' );
			localStorage.removeItem( 'advads_frontend_pathtype' );
			localStorage.removeItem( 'advads_frontend_boundary' );
			localStorage.removeItem( 'advads_frontend_blog_id' );
			localStorage.removeItem( 'advads_frontend_starttime' );
			advads.set_cookie( 'advads_frontend_picker', '', - 1 );
			return false;
		}

		return true;
	}

	// only trigger if local storage is available
	if ( is_enabled() ) {
		var advads_picker_cur, advads_picker_overlay = jQuery( "<div id='advads-picker-overlay'>" ),
			advads_picker_no                         = [document.body, document.documentElement, document];
		advads_picker_overlay.css( {
			position: 'absolute', border: 'solid 2px #428bca',
			backgroundColor: 'rgba(66,139,202,0.5)', boxSizing: 'border-box',
			zIndex: 1000000, pointerEvents: 'none'
		} ).prependTo( 'body' );

		if ( 'true' === localStorage.getItem( 'advads_frontend_boundary' ) ) {
			jQuery( 'body' ).css( 'cursor', 'not-allowed' );
		}

		/**
		 * Check if we can traverse up the dom tree.
		 *
		 * We cannot use event delegation because:
		 * - the content can be loaded via AJAX dynamically
		 * - we cannot wrap the content in a `div` that represents post boundary
		 *   because that may prevent css rules from working
		 *
		 * @param HTMLElement The current element.
		 * return bool
		 */
		window.advads.is_boundary_reached = function ( advads_picker_cur ) {
			if ( 'true' !== localStorage.getItem( 'advads_frontend_boundary' ) ) {
				return false;
			}
			$advads_picker_cur = jQuery( advads_picker_cur );
			// A boundary helper is the `ins` element inside of the post content
			// that is used to determine the post boundary (where the content starts and ends).
			var $boundary_helpers = jQuery( '.advads-frontend-picker-boundary-helper' );

			$boundaries = $boundary_helpers.parent();
			$boundaries.css( 'cursor', 'pointer' );
			return $advads_picker_cur.is( $boundaries ) || ! $advads_picker_cur.closest( $boundaries ).length;
		}

		if ( 'xpath' === localStorage.getItem( 'advads_frontend_pathtype' ) ) {
			var fn = 'getXPath';
		} else {
			var fn = 'getPath';
		}

		jQuery( document ).mousemove( function ( e ) {
			if ( e.target === advads_picker_cur ) {
				return;
			}

			if ( ~ advads_picker_no.indexOf( e.target ) ) {
				advads_picker_cur = null;
				advads_picker_overlay.hide();
				return;
			}

			var target = jQuery( e.target ),
				offset = target.offset(),
				width  = target.outerWidth(),
				height = target.outerHeight();

			advads_picker_cur = e.target;

			var path = jQuery( advads_picker_cur )[fn]();
			if ( ! path ) {
				// A click outside of the boundary.
				// @see `is_boundary_reached`.
				return;
			}
			// log path
			console.log( path );

			advads_picker_overlay.css( {
				top: offset.top,
				left: offset.left,
				width: width,
				height: height
			} ).show();

		} );
		// save on click
		jQuery( document ).click( function ( e ) {
			var path = jQuery( advads_picker_cur )[fn]();

			if ( advads.is_boundary_reached( advads_picker_cur ) ) {
				return;
			}

			localStorage.setItem( 'advads_frontend_element', path );
			window.location = localStorage.getItem( 'advads_prev_url' );
		} );
	};
} );

/*
Derrived from jQuery-GetPath v0.01, by Dave Cardwell. (2007-04-27)
http://davecardwell.co.uk/javascript/jquery/plugins/jquery-getpath/
Usage:
var path = $('#foo').getPath();
*/
jQuery.fn.extend( {
	getPath: function ( path, depth ) {
		// The first time this function is called, path won't be defined.
		if ( typeof path === 'undefined' ) {
			path = '';
		}
		if ( typeof depth === 'undefined' ) {
			depth = 0;
		}

		// If this element is <html> we've reached the end of the path.
		// also end after 2 elements
		if ( this.is( 'html' ) ) {
			return 'html > ' + path;
		} else if ( 3 === depth ) {
			return path;
		}

		// Add the element name.
		var cur = this.get( 0 ).nodeName.toLowerCase();

		// Determine the IDs and path.
		var el_id    = this.attr( 'id' ),
			el_class = this.attr( 'class' );

		depth = depth + 1;

		// Add the #id if there is one. Ignore ID with number.
		if ( typeof el_id !== 'undefined' && ! /\d/.test( el_id ) ) {
			cur += '#' + el_id;
		} else if ( typeof el_class !== 'undefined' ) {
			// Add classes if there is no id.
			el_class = el_class.split( /[\s\n]+/ );
			// Skip classes with numbers.
			el_class = jQuery.grep( el_class, function ( element, index ) {
				return ! /\d/.test( element )
			} );
			// Add 2 classes.
			if ( el_class.length ) {
				cur += '.' + el_class.slice( 0, 2 ).join( '.' );
			}
		}

		// add index if this element is not unique among its siblings
		if ( this.siblings( cur ).length ) {
			cur += ":eq(" + this.siblings( cur ).addBack().not( '#advads-picker-overlay' ).index( this ) + ")";
		}

		// Recurse up the DOM.
		if ( path === '' ) {
			return this.parent().getPath( cur, depth );
		} else {
			return this.parent().getPath( cur + ' > ' + path, depth );
		}
	},

	/**
	 * Get XPath.
	 */
	getXPath: function ( path, depth ) {
		// The first time this function is called, path won't be defined.
		if ( typeof path === 'undefined' ) {
			path = '';
		}
		if ( typeof depth === 'undefined' ) {
			depth = 0;
		}

		// If this element is <html> we've reached the end of the path.
		// also end after 2 elements
		if ( this.is( 'body' ) || 3 === depth ) {
			return path;
		}

		if ( advads.is_boundary_reached( this ) ) {
			return path;
		}

		// Add the element name.
		var tag = this.get( 0 ).nodeName.toLowerCase();
		var cur = tag;

		// Determine the IDs and path.
		var el_id    = this.attr( 'id' ),
			el_class = this.attr( 'class' );
		var classes = [];

		// Add the #id if there is one. Ignore ID with number.
		if ( typeof el_id !== 'undefined' && ! /\d/.test( el_id ) ) {
			return cur + '[@id and id="' + el_id + '"]/' + path;
		} else if ( typeof el_class !== 'undefined' ) {
			// Add classes if there is no id.
			el_class = el_class.split( /[\s\n]+/ );
			// Skip classes with numbers.
			el_class = jQuery.grep( el_class, function ( element, index ) {
				return ! /\d/.test( element )
			} );
			// Add 2 classes.
			if ( el_class.length ) {
				depth = depth + 1;
				var classes = el_class.slice( 0, 2 );

				var xpath_classes = [];
				for ( var i = 0, l = classes.length; i < l; i ++ ) {
					xpath_classes.push( '(@class and contains(concat(" ", normalize-space(@class), " "), " ' + classes[i] + ' "))' );
				}
				cur += '[' + xpath_classes.join( ' and ' ) + ']';
			}
		}

		// Add index if this element is not unique among its siblings.
		if ( classes.length ) {
			var $siblings = this.siblings( tag + '.' + classes.join( '.' ) );
		} else {
			var $siblings = this.siblings( tag );
		}

		if ( $siblings.length ) {
			var index = $siblings.addBack().not( '#advads-picker-overlay' ).index( this );
			cur += '[' + index + ']';
		}

		// Recurse up the DOM.
		if ( path === '' ) {
			return this.parent().getXPath( cur, depth );
		} else {
			return this.parent().getXPath( cur + '/' + path, depth );
		}
	}
} );
