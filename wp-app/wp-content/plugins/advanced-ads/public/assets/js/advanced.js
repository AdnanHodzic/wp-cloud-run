/*
 * advanced ads functions to be used directly within ad codes
 */

/**
 * Polyfills
 */
(function () {
	if ( typeof window.CustomEvent !== "function" ) {
		/**
		 * CustomEvent polyfill for IE11: https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent/CustomEvent
		 *
		 * @param {string} event Event name.
		 * @param {object} params Event parameters.
		 * @returne {object} Custom event.
		 */
		function CustomEvent ( event, params ) {
			params = params || { bubbles: false, cancelable: false, detail: null };
			var evt = document.createEvent( 'CustomEvent' );
			evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
			return evt;
		}

		window.CustomEvent = CustomEvent;
	}

	/**
	 * ReplaceWith polyfill for IE11: https://developer.mozilla.org/en-US/docs/Web/API/ChildNode/replaceWith
	 */
	function ReplaceWithPolyfill() {
		'use-strict'; // For safari, and IE > 10
		var parent = this.parentNode, i = arguments.length, currentNode;
		if ( ! parent) {
			return;
		}
		if ( ! i ) {// if there are no arguments
			parent.removeChild( this );
		}
		while ( i-- ) { // i-- decrements i and returns the value of i before the decrement
			currentNode = arguments[i];
			if ( typeof currentNode !== 'object' ) {
				currentNode = this.ownerDocument.createTextNode( currentNode );
			} else if ( currentNode.parentNode ) {
				currentNode.parentNode.removeChild( currentNode );
			}
			// the value of "i" below is after the decrement
			if ( ! i ) { // if currentNode is the first argument (currentNode === arguments[0])
				parent.replaceChild( currentNode, this );
			} else { // if currentNode isn't the first
				parent.insertBefore( currentNode, this.nextSibling );
			}
		}
	}
	if ( ! Element.prototype.replaceWith ) {
		Element.prototype.replaceWith = ReplaceWithPolyfill;
	}
	if ( ! CharacterData.prototype.replaceWith ) {
		CharacterData.prototype.replaceWith = ReplaceWithPolyfill;
	}
	if ( ! DocumentType.prototype.replaceWith ) {
		DocumentType.prototype.replaceWith = ReplaceWithPolyfill;
	}

	/**
	 * Polyfill for NodeList.foreach() because we need to support IE11: https://developer.mozilla.org/en-US/docs/Web/API/NodeList/forEach
	 */
	if ( window.NodeList && ! NodeList.prototype.forEach ) {
		NodeList.prototype.forEach = function( callback, thisArg ) {
			var i;
			var len = this.length;

			thisArg = thisArg || window;

			for ( i = 0; i < len; i++ ) {
				callback.call( thisArg, this[ i ], i, this );
			}
		};
	}

})();

advads = {
	/**
	 * check if localstorage is supported/enabled by client
	 */
	supports_localstorage: function () {
		"use strict";
		try {
			if ( ! window || window.localStorage === undefined ) {
				return false;
			}
			// storage might be full or disabled
			window.localStorage.setItem( "x", "x" );
			window.localStorage.removeItem( "x" );
			return true;
		} catch ( e ) {
			return false;
		}
	},
	/**
	 * check if the ad is displayed more than {max} times per session
	 * every check increases the counter
	 *
	 * @param {string} name (no id needed, just any id-formated string)
	 * @param {type} max number of maximum times the ad can be displayed within the period
	 * @returns {bool} true if limit is reached
	 */
	max_per_session: function ( name, max ) {
		var num = 1;
		if ( max === undefined || parseInt( max ) === 0 ) {
			max = 1;
		}

		// check if cookie exists and get the value
		if ( this.cookie_exists( name ) ) {
			if ( this.get_cookie( name ) >= max ) {
				return true;
			}
			num = num + parseInt( this.get_cookie( name ) );
		}
		this.set_cookie( name, num );
		return false;
	},
	/**
	 * increase a cookie with an integer value by 1
	 *
	 * @param {str} name of the cookie
	 * @param {int} exdays days until cookie expires
	 */
	count_up: function ( name, exdays ) {
		var num = 1;

		// check if cookie exists and get the value
		if ( this.cookie_exists( name ) ) {
			num = num + parseInt( this.get_cookie( name ) );
		}
		this.set_cookie( name, num );
	},
	/**
	 * return true, if cookie exists
	 * return false, if not
	 * if not exists, create it
	 * use case: to check if something already happened in this page impression
	 *
	 * @param {type} name
	 * @returns {unresolved}
	 */
	set_cookie_exists: function ( name ) {
		if ( get_cookie( name ) ) {
			return true;
		}
		set_cookie( name, '', 0 );
		return false;
	},
	/**
	 * get a cookie value
	 *
	 * @param {string}    name of the cookie
	 * @return {string} decoded cookie value
	 */
	get_cookie: function ( name ) {
		var i, x, y, ADVcookies = document.cookie.split( ';' );
		for ( i = 0; i < ADVcookies.length; i ++ ) {
			x = ADVcookies[i].substr( 0, ADVcookies[i].indexOf( '=' ) );
			y = ADVcookies[i].substr( ADVcookies[i].indexOf( '=' ) + 1 );
			x = x.replace( /^\s+|\s+$/g, '' );
			if ( x === name ) {
				return decodeURIComponent( y );
			}
		}
	},
	/**
	 * set a cookie value
	 *
	 * @param {str} name of the cookie
	 * @param {str} value of the cookie
	 * @param {int} exdays days until cookie expires
	 *  set 0 to expire cookie immidiatelly
	 *  set null to expire cookie in the current session
	 */
	set_cookie: function ( name, value, exdays, path, domain, secure ) {
		// days in seconds
		var expiry = (
			exdays == null
		) ? null : exdays * 24 * 60 * 60;
		this.set_cookie_sec( name, value, expiry, path, domain, secure );
	},
	/**
	 * set a cookie with expiry given in seconds
	 *
	 * @param {str} name of the cookie
	 * @param {str} value of the cookie
	 * @param {int} expiry seconds until cookie expires
	 *  set 0 to expire cookie immidiatelly
	 *  set null to expire cookie in the current session
	 */
	set_cookie_sec: function ( name, value, expiry, path, domain, secure ) {
		var exdate = new Date();
		exdate.setSeconds( exdate.getSeconds() + parseInt( expiry ) );
		document.cookie = name + "=" + encodeURIComponent( value ) +
						  (
							  (
								  expiry == null
							  ) ? "" : "; expires=" + exdate.toUTCString()
						  ) +
						  (
							  (
								  path == null
							  ) ? "; path=/" : "; path=" + path
						  ) +
						  (
							  (
								  domain == null
							  ) ? "" : "; domain=" + domain
						  ) +
						  (
							  (
								  secure == null
							  ) ? "" : "; secure"
						  );
	},
	/**
	 * check if a cookie is set and contains a value
	 *
	 * @param {str} name of the cookie
	 * @returns {bool} true, if cookie is set
	 */
	cookie_exists: function ( name ) {
		var c_value = this.get_cookie( name );
		if ( c_value !== null && c_value !== "" && c_value !== undefined ) {
			return true;
		}
		return false;
	},
	/**
	 * move one element into another
	 *
	 * @param {str} element selector of the element that should be moved
	 * @param {str} target selector of the element where to move
	 * @param {arr} options
	 */
	move: function ( element, target, options ) {

		var el = jQuery( element );
		var target_string = target;

		if ( typeof options === 'undefined' ) {
			options = {};
		}
		if ( typeof options.css === 'undefined' ) {
			options.css = {};
		}
		if ( typeof options.method === 'undefined' ) {
			options.method = 'prependTo';
		}

		// search for abstract target element
		if ( target === '' && typeof options.target !== 'undefined' ) {
			switch ( options.target ) {
				case 'wrapper' : // wrapper
					var offset = 'left';
					if ( typeof options.offset !== 'undefined' ) {
						offset = options.offset;
					}
					target = this.find_wrapper( element, offset );
					break;
			}
		}

		// use only visible elements
		if ( typeof options.moveintohidden === 'undefined' ) {
			target = jQuery( target ).filter( ':visible' );
		} else {
			target = jQuery( target );
		}

		// print warning in console if the element appears multiple times
		if ( target.length > 1 ) {
			console.log( "Advanced Ads: element '" + target_string + "' found " + target.length + " times." );
		}

		// switch insert method
		switch ( options.method ) {
			case 'insertBefore' :
				el.insertBefore( target );
				break;
			case 'insertAfter' :
				el.insertAfter( target );
				break;
			case 'appendTo' :
				el.appendTo( target );
				break;
			case 'prependTo' :
				el.prependTo( target );
				break;
			default :
				el.prependTo( target );
		}
	},

	/**
	 * Set 'relative' position for a parent element.
	 *
	 * @param {str} element selector
	 */
	set_parent_relative: function ( element, options ) {
		var options = typeof options !== 'undefined' ? options : {};
		var el = jQuery( element );
		// give "position" style to parent element, if missing
		var parent = el.parent();

		if ( options.use_grandparent ) {
			parent = parent.parent();
		}

		if ( parent.css( 'position' ) === 'static' || parent.css( 'position' ) === '' ) {
			parent.css( 'position', 'relative' );
		}
	},

	/**
	 * make an absolute position element fixed at the current position
	 * hint: use only after DOM is fully loaded in order to fix a wrong position
	 *
	 * @param {str} element selector
	 * @param {obj} options
	 */
	fix_element: function ( element, options ) {
		var options = typeof options !== 'undefined' ? options : {};

		var el = jQuery( element );

		if ( options.use_grandparent ) {
			this.set_parent_relative( el.parent() );
		} else {
			this.set_parent_relative( el );
		}

		// fix element at current position
		// get position for hidden elements by showing them for a very short time
		if ( options.is_invisible ) {
			el.show();
		}
		var topoffset = parseInt( el.offset().top );
		var leftoffset = parseInt( el.offset().left );
		if ( options.is_invisible ) {
			el.hide();
		}
		if ( 'left' === options.offset ) {
			// Allow to scale the nested image down when it has `max-width: 100%` and touches the left edge of the viewport.
			var rightoffset = jQuery( window ).width() - leftoffset - el.outerWidth();
			el.css( 'position', 'fixed' ).css( 'top', topoffset + 'px' ).css( 'right', rightoffset + 'px' ).css( 'left', '' );
		} else {
			// reset "right" to prevent conflicts
			el.css( 'position', 'fixed' ).css( 'top', topoffset + 'px' ).css( 'left', leftoffset + 'px' ).css( 'right', '' );
		}

	},

	/**
	 * find the main wrapper
	 *  either id or first of its class
	 *
	 *  @param {str} element selector
	 *  @param {str} offset which position of the offset to check (left or right)
	 *  @return {str} selector
	 */
	find_wrapper: function ( element, offset ) {
		// first margin: auto element after body
		var returnValue;
		jQuery( 'body' ).children().each( function ( key, value ) {
			// exclude current element
			// TODO exclude <script>
			if ( value.id !== element.substring( 1 ) ) {
				// check offset value
				var checkedelement = jQuery( value );
				// check if there is space left or right of the element
				if ( (
						 offset === 'right' && (
							 checkedelement.offset().left + jQuery( checkedelement ).width() < jQuery( window ).width()
						 )
					 ) ||
					 (
						 offset === 'left' && checkedelement.offset().left > 0
					 ) ) {
					// fix element
					if ( checkedelement.css( 'position' ) === 'static' || checkedelement.css( 'position' ) === '' ) {
						checkedelement.css( 'position', 'relative' );
					}
					// set return value
					returnValue = value;
					return false;
				}
			}
		} );
		return returnValue;
	},
	/**
	 * center fixed element on the screen
	 *
	 * @param {str} element selector
	 */
	center_fixed_element: function ( element ) {
		var el = jQuery( element );
		// half window width minus half element width
		var left = (
					   jQuery( window ).width() / 2
				   ) - (
					   parseInt( el.css( 'width' ) ) / 2
				   );
		el.css( 'left', left + 'px' );
	},
	/**
	 * center element vertically on the screen
	 *
	 * @param {str} element selector
	 */
	center_vertically: function ( element ) {
		var el = jQuery( element );
		// half window height minus half element height
		var left = (
					   jQuery( window ).height() / 2
				   ) - (
					   parseInt( el.css( 'height' ) ) / 2
				   );

		// Center correctly when the ad is attached to the element that begins lower.
		if ( el.css( 'position' ) !== 'fixed' ) {
			left -= topoffset = parseInt( el.offset().top );
		}
		el.css( 'top', left + 'px' );
	},
	/**
	 * close an ad and add a cookie
	 *
	 * @param {str} element selector
	 */
	close: function ( element ) {
		var wrapper = jQuery( element );
		// remove the ad
		wrapper.remove();
	},
	/**
	 * Wait until images are ready.
	 *
	 * @param {obj} $el jQuery object.
	 * @param {function} ready_callback Ready callback.
	 * derrived from https://github.com/alexanderdickson/waitForImages/blob/master/dist/jquery.waitforimages.js
	 */
	wait_for_images: function ( $el, ready_callback ) {
		var loaded_count = 0;
		var srcs = [];

		$el.find( 'img[src][src!=""]' ).each( function () {
			srcs.push( this.src );
		} );

		if ( srcs.length === 0 ) {
			ready_callback.call( $el );
		}

		jQuery.each( srcs, function ( i, src ) {
			var image = new Image();
			image.src = src;
			var events = 'load error';

			jQuery( image ).one( events, function me( event ) {
				// Remove remaining handler (either 'load' or 'error').
				jQuery( this ).off( events, me );
				loaded_count ++;

				if ( loaded_count == srcs.length ) {
					ready_callback.call( $el[0] );
					return false;
				}
			} );
		} );
	},

	privacy: {
		state: 'unknown',
		state_executed: false,
		/**
		 * Get consent state.
		 * IIFE so the events fire only once per event.
		 *
		 * @return string
		 *     'not_needed' - consent is not needed.
		 *     'accepted' - consent was given.
		 *     'unknown' - consent was not given yet.
		 */
		get_state: (
			function () {
				return function () {
					// if we already have a state, return that.
					if ( window.advads_options.privacy.state !== 'unknown' ) {
						// make sure this only gets executed once.
						if ( ! advads.privacy.state_executed ) {
							advads.privacy.state_executed = true;
							advads.privacy.dispatch_event( window.advads_options.privacy.state, false );
						}
						return advads.privacy.state;
					}

					// If using the cookie method, fire an initial event, regardless if cookie set or not.
					if ( window.advads_options.privacy['consent-method'] === 'custom' ) {
						var cookie_regex = new RegExp( '.*?' + window.advads_options.privacy['custom-cookie-value'] + '[^;]*?' );
						let cookie       = advads.get_cookie( window.advads_options.privacy['custom-cookie-name'] ) || '';

						// Force the event, if we haven't yet fired one.
						if ( ! advads.privacy.state_executed ) {
							advads.privacy.state_executed = true;
							advads.privacy.dispatch_event( cookie.match( cookie_regex ) ? 'accepted' : 'unknown', true );
						}
					}

					// make sure this only gets executed once.
					advads.privacy.state_executed = true;

					// Run this in an interval (every 0.1s) just in case we are still waiting for consent
					var cnt                = 0,
						consentSetInterval = setInterval( function () {
							// Bail if we have not gotten a consent response after 60 seconds.
							if ( ++ cnt === 600 ) {
								clearInterval( consentSetInterval );
							}
							switch ( window.advads_options.privacy['consent-method'] ) {
								case 'custom' :
									let cookie = advads.get_cookie( window.advads_options.privacy['custom-cookie-name'] ) || '';

									// check if custom cookie is set and matches value.
									if ( cookie.match( cookie_regex ) ) {
										clearInterval( consentSetInterval );
										if ( advads.privacy.state !== 'accepted' ) {
											advads.privacy.dispatch_event( 'accepted', true );
										}
									}
									break;

								case 'iab_tcf_20' :
									// Check if window.__tcfapi has been set.
									if ( typeof window.__tcfapi === 'undefined' ) {
										return;
									}
									clearInterval( consentSetInterval );

									window.__tcfapi( 'addEventListener', 2, function ( TCData, listenerSuccess ) {
											if ( ! listenerSuccess ) {
												return;
											}
											if (
												TCData.eventStatus === 'tcloaded'
												|| TCData.eventStatus === 'useractioncomplete'
												// if this is google funding choices, eventStatus is not set. Check if either gdpr doesn't apply or if there is a purpose object.
												|| ( TCData.eventStatus === null && typeof window.googlefc !== 'undefined' && (
													typeof TCData.purpose !== 'undefined' || ! TCData.gdprApplies
												) )
											) {
												var userAction = TCData.eventStatus === 'useractioncomplete';
												if ( ! TCData.gdprApplies ) {
													if ( advads.privacy.state !== 'not_needed' ) {
														advads.privacy.dispatch_event( 'not_needed', userAction );
													}
													return;
												}

												if ( TCData.purpose.consents[1] ) {
													if ( advads.privacy.state !== 'accepted' ) {
														advads.privacy.dispatch_event( 'accepted', userAction );
													}
													return;
												}

												// fire another event, in case the user revokes the previous consent.
												if ( advads.privacy.state !== 'rejected' ) {
													advads.privacy.dispatch_event( 'rejected', userAction );
												}
											}
										}
									);
									break;
							}
						}, 100 );

					return advads.privacy.state;
				}
			}
		)(),
		/**
		 * Check if the privacy_method is custom cookie, and non personalized ads are allowed.
		 *
		 * @returns {boolean}
		 */
		is_adsense_npa_enabled: function () {
			if ( ! window.advads_options || ! window.advads_options.privacy ) {
				return true;
			}
			return !! (
				window.advads_options.privacy['show-non-personalized-adsense'] && window.advads_options.privacy['consent-method'] === 'custom'
			);
		},
		/**
		 * Dispatch a custom event whenever the state changes.
		 *
		 * @param {String} state The current privacy state.
		 * @param {boolean} userAction This is result of action by user.
		 */
		dispatch_event: function ( state, userAction ) {
			var previousState = advads.privacy.state,
				fire_event    = function () {
					document.dispatchEvent( new CustomEvent( 'advanced_ads_privacy', {
						detail: {
							state: state,
							previousState: previousState,
							userAction: userAction
						}
					} ) );
				};

			advads.privacy.state = state;

			console.log( {
				state: state,
				previousState: previousState,
				userAction: userAction
			} );

			window.advanced_ads_ready_queue.push( fire_event );
		},
		/**
		 * Check if ad is decoded.
		 *
		 * @param {integer} id
		 *
		 * @returns {boolean}
		 */
		is_ad_decoded: function ( id ) {
			return document.querySelector( 'script[data-tcf="waiting-for-consent"][data-id="' + id + '"]' ) === null;
		},
		/**
		 * Decode ad content.
		 *
		 * @param {Element} el
		 * @param {boolean} [inject=true]
		 */
		decode_ad: function ( el, inject ) {
			// this can also be a number if used in a foreach.
			inject     = typeof inject === 'boolean' ? inject : true;
			var string = decodeURIComponent( Array.prototype.map.call( atob( el.textContent ), function ( c ) {
				return '%' + ( '00' + c.charCodeAt( 0 ).toString( 16 ) ).slice( - 2 );
			} ).join( '' ) );

			if ( ! inject ) {
				return string;
			}

			el.replaceWith( document.createRange().createContextualFragment( string ) );
		}
	}
};

window.advanced_ads_ready_queue.push( advads.privacy.get_state );

document.addEventListener( 'advanced_ads_privacy', function ( event ) {
	if (
		(
			event.detail.state !== 'accepted' && event.detail.state !== 'not_needed'
		)
		|| event.detail.userAction
		|| document.readyState === 'loading'
	) {
		return;
	}

	// Find all scripts waiting for consent and decode them.
	document.querySelectorAll( 'script[type="text/plain"][data-tcf="waiting-for-consent"]' ).forEach( advads.privacy.decode_ad );
} );
