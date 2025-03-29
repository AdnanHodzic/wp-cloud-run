(function() {
	tinymce.create( 'tinymce.plugins.advads_shortcode', {
		/**
		 * Initializes the plugin
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function( ed, url ) {
			ed.addButton( 'advads_shortcode_button', {
				title: ed.getLang( 'advads_shortcode.title', 'Advanced ads shortcodes' ),
				image: ed.getLang( 'advads_shortcode.image' ),
				cmd: 'advads_shortcode_command'
			});

			ed.addCommand( 'advads_shortcode_command', function() {
					ed.windowManager.open({
						title: ed.getLang( 'advads_shortcode.title', 'Advanced Ads shortcodes' ),
						inline: 1,
						body: [{
							id: 'advads-shortcode-modal-container',
							type: 'container',
							minWidth: 320,
							html: '<span class="spinner advads-ad-parameters-spinner advads-spinner"></span>',
						}],
						buttons: [{
							text: ed.getLang( 'advads_shortcode.ok', 'Add shortcode' ),
							id: 'advads-shortcode-button-insert-wrap',

							onclick: function( e ) {
								if ( jQuery( '#advads-shortcode-modal-container-body #advads-select-for-shortcode' ).length > 0 ) {
									var item = jQuery( '#advads-select-for-shortcode option:selected' ).val();
									if ( item ) {
										item = item.split( '_' );
										if ( item.length !== 2 ) {
											return;
										}
										if ( item[0] === "ad" ) {
											ed.insertContent( '[the_ad id="' + item[1] + '"]' );
										} else if ( item[0] === "group" ) {
											ed.insertContent( '[the_ad_group id="' + item[1] + '"]' );
										} else if ( item[0] === "placement" ) {
											ed.insertContent( '[the_ad_placement id="' + item[1] + '"]' );
										}
									}
								}
								ed.windowManager.close();
							},
						},
						{
							text: ed.getLang( 'advads_shortcode.cancel', 'Cancel' ),
							onclick: 'close'
						}],

					});

				append_select_field();

			});
		},
	});

	// Register the plugin
	tinymce.PluginManager.add( 'advads_shortcode', tinymce.plugins.advads_shortcode );

	function append_select_field() {
		var insert_button_wrap = jQuery( '#advads-shortcode-button-insert-wrap' ),
			insert_button      = jQuery( '#advads-shortcode-button-insert-wrap button' ),
			container_body     = jQuery( '#advads-shortcode-modal-container-body' );

		insert_button_wrap.addClass( 'mce-disabled' );
		insert_button.prop( 'disabled', true );

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				'action': 'advads_content_for_shortcode_creator'
			}
		})
		.done( function( data, textStatus, jqXHR ) {
			container_body.html( data );

			jQuery( '#advads-select-for-shortcode' ).on( 'change', function() {
				if ( jQuery( this ).prop( 'selectedIndex' ) === 0 ) {
					insert_button_wrap.addClass( 'mce-disabled' );
					insert_button.prop( 'disabled', true );
				} else {
					insert_button_wrap.removeClass( 'mce-disabled' );
					insert_button.prop( 'disabled', false );
				}
			});

		})
		.fail( function( jqXHR, textStatus, errorThrown ) {
			container_body.html( errorThrown );
		});
	}
})();
