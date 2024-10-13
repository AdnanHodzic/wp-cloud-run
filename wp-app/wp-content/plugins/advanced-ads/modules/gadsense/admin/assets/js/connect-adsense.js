;(function($){

    // Unique instance of "advadsMapiConnectClass"
    var INSTANCE = null;

    var advadsMapiConnectClass = function( content, options ) {
        this.options = {};
        this.modal = $( '#gadsense-modal' );
        this.frame = null;
        if ( 'undefined' == typeof content || ! content ) {
            content = 'confirm-code';
        }
        this.setOptions( options );
        this.init();


        this.show( content );
        return this;
    };

    advadsMapiConnectClass.prototype = {

        constructor: advadsMapiConnectClass,

        // Set options, for re-use of existing instance for a different purpose.
        setOptions: function( options ) {
            var defaultOptions = {
                onSuccess: false,
                onError: false,
            };
            if ( 'undefined' == typeof options ) {
                options = defaultOptions;
            }
            this.options = $.extend( {}, defaultOptions, options);
        },

        // Tasks to do after a successful connection.
        exit: function(){
            if ( this.options.onSuccess ) {
                if ( 'function' == typeof this.options.onSuccess ) {
                    this.options.onSuccess( this );
                }
            } else {
                this.hide();
            }
        },

		// Submit OAuth2 code for account connection.
		submitOAuthCode: function( code ) {

			var that = this;
			if ( '' == code ) return;
			$( '.gadsense-overlay' ).css( 'display', 'block' );
			$( '#gadsense-modal-error' ).hide();
			var data = {
				action: 'advads_gadsense_mapi_confirm_code',
				code: code,
				nonce: AdsenseMAPI.nonce,
			};

			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: data,
				success:function(response, status, XHR){
					$( '#mapi-code' ).val( '' );
					if ( response.status && true === response.status && response['token_data'] ) {
						that.getAccountDetails( response['token_data'] );
					} else {
						// Connection error handling.
						console.log( response );
						$( '.gadsense-overlay' ).css( 'display', 'none' );
						$( '#mapi-code' ).val( '' );
						$( '#gadsense-modal-content-inner .dashicons-dismiss' ).trigger( 'click' );
					}
				},
				error:function(request, status, error){
					$( '#gadsense-loading-overlay' ).css( 'display', 'none' );
				},
			});

		},

        // Initialization - mostly binding events.
        init: function(){

            var that = this;

            // Close the modal and hide errors.
            $( document ).on( 'click', '#gadsense-modal .dashicons-dismiss', function(){
				that.hide();
            } );

            // Account selection
            $( document ).on( 'click', '.gadsense-modal-content-inner[data-content="account-selector"] button', function( ev ) {
                var adsenseID = $( '#mapi-select-account' ).val();
                var tokenData = false;
                var tokenString = $( '.gadsense-modal-content-inner[data-content="account-selector"] input.token-data' ).val();
                var details = false;
                var detailsString = $( '.gadsense-modal-content-inner[data-content="account-selector"] input.accounts-details' ).val();

                try {
                    tokenData = JSON.parse( tokenString );
                } catch ( Ex ) {
                    console.error( 'Bad token data : ' + tokenString );
                }
                try {
                    details = JSON.parse( detailsString );
                } catch ( Ex ) {
					console.error( 'Bad account details : ' + detailsString );
				}
				if ( details ) {
					$( '.gadsense-overlay' ).css( 'display', 'block' );
					var data = {
						action: 'advads_gadsense_mapi_select_account',
						nonce:  AdsenseMAPI.nonce,
                        account : details[ adsenseID ],
                        'token_data': tokenData,
                    };

                    $.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: data,
                        success:function(response, status, XHR){
                            if ( response.status && true === response.status ) {
                                INSTANCE.exit();
                            } else {
                                console.log( response );
                            }
                        },
                        error:function(request, status, error){
                            $( '#gadsense-loading-overlay' ).css( 'display', 'none' );
                        },
                    });
                }

            } );

        },

        // Get account info based on a newly obtained token.
        getAccountDetails: function( tokenData ){
			var data        = {
				action: 'advads_gadsense_mapi_get_details',
				nonce:  AdsenseMAPI.nonce
			};
			data.token_data = tokenData;

			$.ajax( {
				url:     ajaxurl,
				type:    'post',
				data:    data,
				success: function ( response ) {
					if ( response.success && true === response.success ) {
						if ( response.data && response.data.reload ) {
							INSTANCE.exit();
						} else if ( response.data && response.data.token_data ) {
							INSTANCE.switchContent( 'account-selector' );
							INSTANCE.frame.find( 'select' ).html( response.data.html );
							INSTANCE.frame.find( 'input.token-data' ).val( JSON.stringify( response.data.token_data ) );
							INSTANCE.frame.find( 'input.accounts-details' ).val( JSON.stringify( response.data.details ) );
						} else {
							INSTANCE.switchContent( 'error' );
							INSTANCE.frame.find( '.error-message' ).text( JSON.stringify( response ) );
						}
					}
				},
				error:   function ( request, status, error ) {
					if ( request.responseJSON ) {
						if ( request.responseJSON.data.error ) {
							INSTANCE.switchContent( 'error' );
							INSTANCE.frame.find( '.error-message' ).text( request.responseJSON.data.error );
							if ( typeof AdsenseMAPI.connectErrorMsg[request.responseJSON.data.error] !== 'undefined' ) {
								INSTANCE.frame.find( '.error-description' ).html( AdsenseMAPI.connectErrorMsg[request.responseJSON.data.error] );
							}
						} else if ( request.responseJSON.data.message ) {
							INSTANCE.frame.find( '.error-message' ).text( request.responseJSON.data.message );
						}
						return;
					}
					$( '#gadsense-loading-overlay' ).css( 'display', 'none' );
				}
			} );

		},

        // Switch between frames in the modal container.
        switchContent: function( content ) {
            if ( this.modal.find( '.gadsense-modal-content-inner[data-content="' + content + '"]' ).length ) {
                this.modal.find( '.gadsense-modal-content-inner' ).css( 'display', 'none' );
                this.frame = this.modal.find( '.gadsense-modal-content-inner[data-content="' + content + '"]' );
                this.frame.css( 'display', 'block' );
                $( '.gadsense-overlay' ).css( 'display', 'none' );
            }
        },

        // Show the modal frame with a given content.
        show: function( content ) {
            if ( 'undefined' == typeof content ) {
                content = 'confirm-code';
            }
            this.switchContent( content );

            if ( 'open-google' == content ) {
				window.location.href = AdsenseMAPI.oAuth2;
            } else {
				this.modal.css( 'display', 'block' );
			}
        },

        // Hide the modal frame
        hide: function(){
			window.location.href = this.modal.attr( 'data-return' );
        },

    };

    window.advadsMapiConnectClass = advadsMapiConnectClass;

    // Shortcut function.
    window.advadsMapiConnect = function( content, options ) {
        if ( 'undefined' == typeof content || ! content ) {
            content = 'confirm-code';
        }
        if ( 'undefined' == typeof options ) {
            options = {};
        }
        if ( null === INSTANCE ) {
            INSTANCE = new advadsMapiConnectClass( content, options );
        } else {
            INSTANCE.show( content, options );
        }
    }

    $(function(){
        // Move the the pop-up outside of any form.
        $( '#wpwrap' ).append( $( '#gadsense-modal' ) );

		if ( $( '#advads-adsense-oauth-code' ).length ) {
			INSTANCE = new advadsMapiConnectClass( 'confirm-code', {} );
			INSTANCE.submitOAuthCode( $( '#advads-adsense-oauth-code' ).val() );
		}
    });

})(window.jQuery);
