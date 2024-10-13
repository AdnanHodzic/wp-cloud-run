/**
 * actually this method was moved inside the ad network class.
 * however the responsive addon depends on it, so i made it global again.
 * this makes it downward compatible (with an older version of responsive),
 * but you should probably adjust the responsive plugin to make use of
 * the static method (AdvancedAdsNetworkAdsense.gadsenseFormatAdContent)
 *
 * in case you come across a missing method originating from the deleted new-ad.js,
 * please just make the methods static and create a wrapper function like the one below
 */
window.gadsenseFormatAdContent = function () {
	AdvancedAdsNetworkAdsense.gadsenseFormatAdContent();
};

class AdvancedAdsNetworkAdsense extends AdvancedAdsAdNetwork {
	constructor( codes ) {
		super( 'adsense' );
		this.name                   = 'adsense';
		this.codes                  = codes;
		this.parseCodeBtnClicked    = false;
		this.preventCloseAdSelector = false;
		// this.adUnitName = null;
		// the legacy code of gadsense executes a script inside a php template and will may not have been executed
		// at this stage. the AdvancedAdsAdNetwork class already knows the publisher id, so we will overwrite
		// the field in gadsenseData to be up to date at all times.
		// TODO: the use of gadsenseData.pubId could be removed from this class in favor of  this.vars.pubId
		gadsenseData.pubId = this.vars.pubId;
	}

	/**
	 * Legacy method
	 *
	 * Format the post content field
	 */
	static gadsenseFormatAdContent() {
		const slotId      = jQuery( '#ad-parameters-box #unit-code' ).val();
		const unitType    = jQuery( '#ad-parameters-box #unit-type' ).val();
		const publisherId = jQuery( '#advads-adsense-pub-id' ).val() ? jQuery( '#advads-adsense-pub-id' ).val() : gadsenseData.pubId;
		let adContent   = {
			slotId:   slotId,
			unitType: unitType,
			pubId:    publisherId
		};
		if ( unitType === 'responsive' ) {
			let resize = jQuery( '#ad-parameters-box #ad-resize-type' ).val();
			if ( resize === '0' ) {
				resize = 'auto';
			}
			adContent.resize = resize;
		}
		if ( unitType === 'in-feed' ) {
			adContent.layout_key = jQuery( '#ad-parameters-box #ad-layout-key' ).val();
		}
		if ( typeof adContent.resize !== 'undefined' && adContent.resize !== 'auto' ) {
			jQuery( document ).trigger( 'gadsenseFormatAdResponsive', [adContent] );
		}
		jQuery( document ).trigger( 'gadsenseFormatAdContent', [adContent] );

		if ( typeof window.gadsenseAdContent !== 'undefined' ) {
			adContent = window.gadsenseAdContent;
			delete( window.gadsenseAdContent );
		}
		jQuery( '#advads-ad-content-adsense' ).val( JSON.stringify( adContent, false, 2 ) );

	}

	openSelector() {

	}

	closeAdSelector() {
		if ( this.preventCloseAdSelector ) {
			return;
		}
		AdvancedAdsAdmin.AdImporter.closeAdSelector();
	}

	getSelectedId() {
		const pubId  = gadsenseData.pubId || false;
		const slotId = jQuery( '#unit-code' ).val().trim();
		return pubId && slotId ? 'ca-' + pubId + ':' + slotId : null;
	}

	selectAdFromList( slotId ) {
		this.preventCloseAdSelector = true;
		this.onSelectAd( slotId );
		AdvancedAdsAdmin.AdImporter.openExternalAdsList();

		const report = Advanced_Ads_Adsense_Report_Helper.getReportObject();
		if ( report ) {
			report.filter = slotId.split( ':' )[1];
			report.updateHtmlAttr();
			report.refresh();
		}
	}

	updateAdFromList( slotId ) {
		this.getRemoteCode( slotId );
	}

	getRefreshAdsParameters() {
		return {
			nonce:    AdsenseMAPI.nonce,
			action:   'advanced_ads_get_ad_units_adsense',
			account:  gadsenseData.pubId,
			inactive: ! this.hideIdle
		};
	}

	onManualSetup() {
		jQuery( '.advads-adsense-code' ).css( 'display', 'none' );
		jQuery( '#remote-ad-unsupported-ad-type' ).css( 'display', 'none' );
		this.undoReadOnly();
	}

	/**
	 * Parse the code of an adsense ad and adjust the GUI
	 * call it, when an ad unit was selected.
	 * returns the parsed obj or false, when the ad code could not be parsed
	 */
	processAdCode( code ) {
		const parsed = this.parseAdContentFailsafe( code );
		if ( parsed ) {
			this.undoReadOnly();
			this.setDetailsFromAdCode( parsed );
			this.makeReadOnly();
			jQuery( '#remote-ad-code-error' ).hide();
			jQuery( '#remote-ad-unsupported-ad-type' ).hide();
			this.closeAdSelector();
		} else {
			jQuery( '#remote-ad-code-error' ).show();
		}
		return parsed;
	}

	/**
	 * Clone of legacy method
	 *
	 * @param slotID
	 */
	onSelectAd( slotID ) {
		if ( typeof this.codes[slotID] !== 'undefined' ) {
			this.getSavedDetails( slotID );
		} else {
			this.getRemoteCode( slotID );
		}
	}

	/**
	 * Legacy method
	 *
	 * @param slotID
	 */
	getSavedDetails( slotID ) {
		if ( typeof this.codes[slotID] !== 'undefined' ) {
			const parsed = this.processAdCode( this.codes[slotID] );
			if ( parsed !== false ) {
				jQuery( '#remote-ad-unsupported-ad-type' ).css( 'display', 'none' );
				this.closeAdSelector();
				this.preventCloseAdSelector = false;
			}
		}
	}

	/**
	 * Legacy method
	 *
	 * @param slotID
	 */
	getRemoteCode( slotID ) {
		if ( slotID === '' ) {
			return;
		}
		jQuery( '#mapi-loading-overlay' ).css( 'display', 'block' );
		const self = this;
		jQuery.ajax( {
			type:    'post',
			url:     ajaxurl,
			data:    {
				nonce:  AdsenseMAPI.nonce,
				action: 'advads_mapi_get_adCode',
				unit:   slotID
			},
			success: function ( response ) {
				jQuery( '#mapi-loading-overlay' ).css( 'display', 'none' );
				if ( typeof response.code !== 'undefined' ) {
					jQuery( '#remote-ad-code-msg' ).empty();
					if ( self.processAdCode( response.code ) !== false ) {
						self.codes[slotID] = response.code;
						AdvancedAdsAdmin.AdImporter.unitIsSupported( slotID );
					}
					AdvancedAdsAdmin.AdImporter.highlightSelectedRowInExternalAdsList();
					jQuery( '[data-slotid="' + slotID + '"]' ).children( '.unittype' ).text( response.type );
					self.closeAdSelector();

				} else {
					if ( typeof response.raw !== 'undefined' ) {
						jQuery( '#remote-ad-code-msg' ).html( response.raw );
					} else if ( typeof response.msg !== 'undefined' ) {
						if ( typeof response.reload !== 'undefined' ) {
							AdvancedAdsAdmin.AdImporter.emptyMapiSelector( response.msg );
						} else {
							if ( response.msg === 'doesNotSupportAdUnitType' ) {
								AdvancedAdsAdmin.AdImporter.unitIsNotSupported( slotID );
							} else {
								jQuery( '#remote-ad-code-msg' ).html( response.msg );
							}
						}
						if ( typeof response.raw !== 'undefined' ) {
							console.log( response.raw );
						}
					}
				}
			},
			error:   function () {
				jQuery( '#mapi-loading-overlay' ).css( 'display', 'none' );

			}
		} );

	}

	/**
	 * Legacy method
	 *
	 * Parse ad content.
	 *
	 * @return {!Object}
	 */
	parseAdContent( content ) {
		const rawContent = typeof content !== 'undefined' ? content.trim() : '';
		const theContent = jQuery( '<div />' ).html( rawContent );
		const adByGoogle = theContent.find( 'ins' );
		let theAd        = {};
		theAd.slotId     = adByGoogle.attr( 'data-ad-slot' ) || '';
		if ( typeof adByGoogle.attr( 'data-ad-client' ) !== 'undefined' ) {
			theAd.pubId = adByGoogle.attr( 'data-ad-client' ).substr( 3 );
		}

		if ( theAd.slotId !== undefined && theAd.pubId !== '' ) {
			theAd.display    = adByGoogle.css( 'display' );
			theAd.format     = adByGoogle.attr( 'data-ad-format' );
			theAd.layout     = adByGoogle.attr( 'data-ad-layout' ); // for In-feed and In-article
			theAd.layout_key = adByGoogle.attr( 'data-ad-layout-key' ); // for InFeed
			theAd.style      = adByGoogle.attr( 'style' ) || '';

			// Normal ad.
			if ( typeof theAd.format === 'undefined' && theAd.style.indexOf( 'width' ) !== - 1 ) {
				theAd.type   = 'normal';
				theAd.width  = adByGoogle.css( 'width' ).replace( 'px', '' );
				theAd.height = adByGoogle.css( 'height' ).replace( 'px', '' );
			} else if ( typeof theAd.format !== 'undefined' && theAd.format === 'auto' ) {
				// Responsive ad, auto resize.
				theAd.type = 'responsive';
			} else if ( typeof theAd.format !== 'undefined' && theAd.format === 'link' ) {
				// Older link unit format; for new ads the format type is no longer needed; link units are created through the AdSense panel
				if ( theAd.style.indexOf( 'width' ) !== - 1 ) {
					// Is fixed size.
					theAd.width  = adByGoogle.css( 'width' ).replace( 'px', '' );
					theAd.height = adByGoogle.css( 'height' ).replace( 'px', '' );
					theAd.type   = 'link';
				} else {
					// Is responsive.
					theAd.type = 'link-responsive';
				}
			} else if ( typeof theAd.format !== 'undefined' && theAd.format === 'autorelaxed' ) {
				// Responsive Matched Content
				theAd.type = 'matched-content';
			} else if ( typeof theAd.format !== 'undefined' && theAd.format === 'fluid' ) {
				// In-article & In-feed ads.
				if ( typeof theAd.layout !== 'undefined' && theAd.layout === 'in-article' ) {
					// In-article.
					theAd.type = 'in-article';
				} else {
					// In-feed.
					theAd.type = 'in-feed';
				}
			}
		}

		/**
		 *  Synchronous code
		 */
		if ( rawContent.indexOf( 'google_ad_slot' ) !== - 1 ) {
			const adClient = rawContent.match( /google_ad_client ?= ?["']([^'"]+)/ );
			const adSlot   = rawContent.match( /google_ad_slot ?= ?["']([^'"]+)/ );
			const adFormat = rawContent.match( /google_ad_format ?= ?["']([^'"]+)/ );
			const adWidth  = rawContent.match( /google_ad_width ?= ?([\d]+)/ );
			const adHeight = rawContent.match( /google_ad_height ?= ?([\d]+)/ );

			theAd = {};
			theAd.pubId = adClient[1].substr( 3 );

			if ( adSlot !== null ) {
				theAd.slotId = adSlot[1];
			}
			if ( adFormat !== null ) {
				theAd.format = adFormat[1];
			}
			if ( adWidth !== null ) {
				theAd.width = parseInt( adWidth[1] );
			}
			if ( adHeight !== null ) {
				theAd.height = parseInt( adHeight[1] );
			}
			if ( typeof theAd.format === 'undefined' ) {
				theAd.type = 'normal';
			}

		}

		if ( theAd.slotId === '' && gadsenseData.pubId && gadsenseData.pubId !== '' ) {
			theAd.type = jQuery( '#unit-type' ).val();
		}

		// Page-Level ad.
		if ( rawContent.indexOf( 'enable_page_level_ads' ) !== - 1 || /script[^>]+data-ad-client=/.test( rawContent ) ) {
			theAd = {'parse_message': 'pageLevelAd'};
		} else if ( ! theAd.type ) {
			// Unknown ad.
			theAd = {'parse_message': 'unknownAd'};
		}

		jQuery( document ).trigger( 'gadsenseParseAdContent', [theAd, adByGoogle] );
		return theAd;
	}

	parseAdContentFailsafe( code ) {
		if ( typeof code === 'string' ) {
			try {
				code       = JSON.parse( code );
			} catch ( e ) {
				return this.parseAdContent( code );
			}
		}

		return code;
	}

	/**
	 * Handle result of parsing content.
	 *
	 * Legacy method
	 */
	handleParseResult( parseResult ) {
		jQuery( '#pastecode-msg' ).empty();
		switch ( parseResult.parse_message ) {
			case 'pageLevelAd' :
				advads_show_adsense_auto_ads_warning();
				break;
			case 'unknownAd' :
				// Not recognized ad code.
				if ( this.parseCodeBtnClicked && 'post-new.php' === gadsenseData.pagenow ) {
					// do not show if just after switching to AdSense ad type on ad creation.
					jQuery( '#pastecode-msg' ).append( jQuery( '<p />' ).css( 'color', 'red' ).html( gadsenseData.msg.unknownAd ) );
				}
				break;
			default:
				this.setDetailsFromAdCode( parseResult );
				if ( typeof AdsenseMAPI !== 'undefined' && typeof AdsenseMAPI.hasToken !== 'undefined' && AdsenseMAPI.pubId === parseResult.pubId ) {
					const content = jQuery( '#advanced-ads-ad-parameters input[name="advanced_ad[content]"]' ).val();
					this.mapiSaveAdCode( content, parseResult.slotId );
					this.makeReadOnly();
				}
				jQuery( '.advads-adsense-code' ).hide();
				jQuery( '.advads-adsense-show-code' ).show();
				jQuery( '.mapi-insert-code' ).show();
				const customInputs = this.getCustomInputs();
				customInputs.show();
		}
	}

	/**
	 * Legacy method
	 *
	 * Set ad parameters fields from the result of parsing ad code
	 */
	setDetailsFromAdCode( theAd ) {
		this.undoReadOnly();
		jQuery( '#unit-code' ).val( theAd.slotId );
		jQuery( '#advads-adsense-pub-id' ).val( theAd.pubId );
		if ( theAd.type === 'normal' ) {
			jQuery( '#unit-type' ).val( 'normal' );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( theAd.width );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( theAd.height );
		} else if ( theAd.type === 'responsive' ) {
			jQuery( '#unit-type' ).val( 'responsive' );
			jQuery( '#ad-resize-type' ).val( 'auto' );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( '' );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( '' );
		} else if ( theAd.type === 'link') {
			jQuery( '#unit-type' ).val( 'link' );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( theAd.width );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( theAd.height );
		} else if ( theAd.type === 'link-responsive' ) {
			jQuery( '#unit-type' ).val( 'link-responsive' );
			jQuery( '#ad-resize-type' ).val( 'auto' );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( '' );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( '' );
		} else if ( theAd.type === 'matched-content' ) {
			jQuery( '#unit-type' ).val( 'matched-content' );
			jQuery( '#ad-resize-type' ).val( 'auto' );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( '' );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( '' );
		} else if ( theAd.type === 'in-article' ) {
			jQuery( '#unit-type' ).val( 'in-article' );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( '' );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( '' );
		} else if ( theAd.type === 'in-feed' ) {
			jQuery( '#unit-type' ).val( 'in-feed' );
			jQuery( '#ad-layout-key' ).val( theAd.layout_key );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val( '' );
			jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val( '' );
		}
		const storedPubId = gadsenseData.pubId;

		if ( storedPubId !== '' && theAd.pubId !== storedPubId && theAd.slotId !== '' ) {
			jQuery( '#adsense-ad-param-error' ).text( gadsenseData.msg.pubIdMismatch );
		} else {
			jQuery( '#adsense-ad-param-error' ).empty();
		}
		jQuery( document ).trigger( 'this.setDetailsFromAdCode', [theAd] );
		jQuery( '#unit-type' ).trigger( 'change' );
	}

	/**
	 * Legacy method
	 */
	updateAdsenseType() {
		const $         = jQuery;
		const type      = $( '#unit-type' ).val();
		const layout    = $( '.advads-adsense-layout' );
		const layoutKey = $( '.advads-adsense-layout-key' );
		const size      = $( '#advanced-ads-ad-parameters-size' );
		const width     = $( '[name="advanced_ad\[width\]"]' );
		const height    = $( '[name="advanced_ad\[height\]"]' );

		layout.hide().next( 'div' ).hide();
		layoutKey.hide().next( 'div' ).hide();

		jQuery( '.advads-ad-notice-in-feed-add-on' ).hide();
		if ( type === 'responsive' || type === 'link-responsive' || type === 'matched-content' ) {
			size.hide().prev( '.label' ).hide();
			size.next( '.hr' ).hide();
			$( '.clearfix-before' ).show();
		} else if ( type === 'in-feed' ) {
			layout.hide().next( 'div' ).hide();
			layoutKey.show().next( 'div' ).show();
			size.hide().prev( '.label' ).hide();
			size.next( '.hr' ).hide();

			// show add-on notice
			$( '.advads-ad-notice-in-feed-add-on' ).show();
			$( '.clearfix-before' ).show();
		} else if ( type === 'in-article' ) {
			size.hide().prev( '.label' ).hide();
			size.next( '.hr' ).hide();
			$( '.clearfix-before' ).show();
		} else if ( type === 'normal' || type === 'link' ) {
			size.show().prev( '.label' ).show();
			size.next( '.hr' ).show();
			$( '.clearfix-before' ).hide();

			if ( ! width.val() ) {
				width.val( '300' );
			}
			if ( ! height.val() ) {
				height.val( '250' );
			}
		}
		$( document ).trigger( 'gadsenseUnitChanged' );
		AdvancedAdsNetworkAdsense.gadsenseFormatAdContent();

		this.show_float_warnings( type );
	}

	/**
	 * Legacy method
	 *
	 * Show / hide position warning.
	 */
	show_float_warnings( unit_type ) {
		const resize_type = jQuery( '#ad-resize-type' ).val();
		const position    = jQuery( 'input[name="advanced_ad[output][position]"]:checked' ).val();

		if (
			( ['link-responsive', 'matched-content', 'in-article', 'in-feed'].indexOf( unit_type ) !== - 1
				|| ( unit_type === 'responsive' && resize_type !== 'manual' )
			)
			&& ( position.startsWith( 'left' ) || position.startsWith( 'right' ) )
		) {
			jQuery( '#ad-parameters-box-notices .advads-ad-notice-responsive-position' ).show();
		} else {
			jQuery( '#ad-parameters-box-notices .advads-ad-notice-responsive-position' ).hide();
		}
	}

	/**
	 * Legacy method - adds readonly to relevant inputs
	 */
	makeReadOnly() {
		jQuery( '#unit-type option:not(:selected)' ).prop( 'disabled', true );
	}

	/**
	 * Legacy method - removes readonly from relevant inputs  (original name getSlotAndType_jq)
	 */
	undoReadOnly() {
		jQuery( '#unit-code,#ad-layout,#ad-layout-key,[name="advanced_ad[width]"],[name="advanced_ad[height]"]' ).prop( 'readonly', false );
		jQuery( '#unit-type option:not(:selected)' ).prop( 'disabled', false );
	}

	getCustomInputs() {
		const $div1           = jQuery( '#unit-code' ).closest( 'div' );
		const $label1         = $div1.prev();
		const $hr1            = $div1.next();
		const $label2         = $hr1.next();
		const $div2           = $label2.next();
		const $layoutKey      = jQuery( '#ad-layout-key' ).closest( 'div' );
		const $layoutKeyLabel = $layoutKey.prev( '#advads-adsense-layout-key' );

		return $div1.add( $label1 ).add( $hr1 ).add( $label2 ).add( $div2 ).add( $layoutKey ).add( $layoutKeyLabel );
	}

	onBlur() {

	}

	onSelected() {
		// Handle a click from the "Switch to AdSense ad" button.
		if ( AdvancedAdsAdmin.AdImporter.adsenseCode ) {
			this.parseCodeBtnClicked                = true;
			const parseResult                       = this.parseAdContent( AdvancedAdsAdmin.AdImporter.adsenseCode );
			AdvancedAdsAdmin.AdImporter.adsenseCode = null;
			this.handleParseResult( parseResult );
		} else {
			// When you are not connected to adsense, or if the ad was edited manually open the manual setup view.
			let switchToManualSetup = ! this.vars.connected;
			if ( ! switchToManualSetup ) {
				const parsedAd = this.parseAdContentFailsafe( this.codes[this.getSelectedId()] );
				if ( parsedAd ) {
					// We need to check if the type of the ad is different from the default. this marks a manually setup ad.
					if ( parsedAd.type !== jQuery( '#unit-type' ).val() ) {
						// This ad was manually setup. don't open the selector, but switch to manual select.
						switchToManualSetup = true;

					}
				}
			}
			if ( switchToManualSetup ) {
				AdvancedAdsAdmin.AdImporter.manualSetup();
			} else if ( AdvancedAdsAdmin.AdImporter.highlightSelectedRowInExternalAdsList() || ! this.getSelectedId() ) {
				AdvancedAdsAdmin.AdImporter.openExternalAdsList();
			}
		}

	}

	onDomReady() {
		const self = this;
		jQuery( document ).on( 'click', '.advads-adsense-close-code', function ( ev ) {
			ev.preventDefault();
			self.onSelected();
		} );

		jQuery( document ).on( 'click', '.advads-adsense-submit-code', function ( ev ) {
			ev.preventDefault();
			self.parseCodeBtnClicked = true;
			const rawContent           = jQuery( '.advads-adsense-content' ).val();
			const parseResult          = self.parseAdContent( rawContent );
			self.handleParseResult( parseResult );
			if ( AdvancedAdsAdmin.AdImporter.highlightSelectedRowInExternalAdsList() ) {
				AdvancedAdsAdmin.AdImporter.openExternalAdsList();
				self.preventCloseAdSelector = true;

				// save the manually added ad code to the AdSense settings
				wp.ajax.post( 'advads-mapi-save-manual-code', {
					raw_code:    encodeURIComponent( rawContent ),
					parsed_code: parseResult,
					nonce:       AdsenseMAPI.nonce
				} )
				.fail( function ( r ) {
					const $notice = jQuery( '<div>' ).addClass( 'notice notice-error' ).html( jQuery( '<p>' ).text( r.responseJSON.data.message ) );
					jQuery( '#post' ).before( $notice );
					jQuery( 'body html' ).animate(
						{
							scrollTop: $notice.offset().top
						},
						200
					);
				} );
			} else {
				// No adsense ad with this slot id was found.
				// Switches to manual ad setup view.
				self.preventCloseAdSelector = false;
				AdvancedAdsAdmin.AdImporter.manualSetup();
			}
		} );

		jQuery( document ).on( 'gadsenseUnitChanged', function () {
			const $row = jQuery( 'tr[data-slotid$="' + jQuery( '#unit-code' ).val() + '"]' );
			let type   = window.adsenseAdvancedAdsJS.ad_types.display;

			switch ( jQuery( '#unit-type' ).val() ) {
				case 'matched-content':
					type = window.adsenseAdvancedAdsJS.ad_types.matched_content;
					break;
				case 'link':
				case 'link-responsive':
					type = window.adsenseAdvancedAdsJS.ad_types.link;
					break;
				case 'in-article':
					type = window.adsenseAdvancedAdsJS.ad_types.in_article;
					break;
				case 'in-feed':
					type = window.adsenseAdvancedAdsJS.ad_types.in_feed;
					break;
			}

			$row.children( '.unittype' ).text( type );
		} );

		jQuery( document ).on( 'change', '#unit-type, #unit-code, #ad-layout-key', function () {
			self.checkAdSlotId( this );
		} );

		const inputCode = jQuery( '#unit-code' );
		if ( inputCode ) {
			this.checkAdSlotId( inputCode[0] );
		}

		jQuery( document ).on( 'change', '#ad-resize-type', function () {
			self.show_float_warnings( 'responsive' );
		} );
		this.updateAdsenseType();

		if ( typeof AdsenseMAPI.hasToken !== 'undefined' ) {
			this.mapiMayBeSaveAdCode();
		}

		jQuery( document ).on( 'click', '#mapi-archived-ads', function () {
			self.showArchivedAds( jQuery( this ).hasClass( 'dashicons-visibility' ) );
		} );

		jQuery( '#wpwrap' ).on(
			'advads-mapi-adlist-opened',
			function () {
				// Update ad unit list to v2 data the first time the ad list is opened.
				if ( jQuery( '#mapi-force-v2-list-update' ).length ) {
					jQuery( '#mapi-wrap i[data-mapiaction="updateList"]' ).trigger( 'click' );
					return;
				}
				self.showArchivedAds();
			}
		);

		this.onSelected();
	}

	showArchivedAds( show ) {
		if ( typeof show === 'undefined' ) {
			show = false;
		}
		const icon     = jQuery( '#mapi-archived-ads' );
		const title    = icon.attr( 'title' );
		const altTitle = icon.attr( 'data-alt-title' );
		if ( show ) {
			jQuery( '#mapi-table-wrap tbody tr[data-archived="1"]' ).show();
			icon.removeClass( 'dashicons-visibility' ).addClass( 'dashicons-hidden' ).attr( 'title', altTitle ).attr( 'data-alt-title', title );
		} else {
			jQuery( '#mapi-table-wrap tbody tr[data-archived="1"]' ).not( '.selected' ).hide();
			icon.removeClass( 'dashicons-hidden' ).addClass( 'dashicons-visibility' ).attr( 'title', altTitle ).attr( 'data-alt-title', title );
		}
	}

	checkAdSlotId( elm ) {
		if ( jQuery( elm ).attr( 'id' ) === 'unit-code' ) {
			let val = jQuery( elm ).val();
			if ( val ) {
				val = val.trim();
			}
			if ( val.length > 0 && gadsenseData.pubId && val.indexOf( gadsenseData.pubId.substr( 4 ) ) !== -1 ) {
				jQuery( '#advads-pubid-in-slot' ).css( 'display', 'block' );
				jQuery( elm ).css( 'background-color', 'rgba(255, 235, 59, 0.33)' );
				this.updateAdsenseType();
				return;
			}
		}
		jQuery( '#unit-code' ).css( 'background-color', '#fff' );
		jQuery( '#advads-pubid-in-slot' ).css( 'display', 'none' );
		this.updateAdsenseType();
	}

	mapiSaveAdCode( code, slot ) {
		if ( typeof AdsenseMAPI.hasToken !== 'undefined' && typeof this.codes['ca-' + AdsenseMAPI.pubId + ':' + slot] === 'undefined' ) {
			this.codes['ca-' + AdsenseMAPI.pubId + ':' + slot] = code;
			jQuery( '#mapi-loading-overlay' ).css( 'display', 'block' );
			jQuery.ajax( {
				type:    'post',
				url:     ajaxurl,
				data:    {
					nonce:  AdsenseMAPI.nonce,
					slot:   slot,
					code:   code,
					action: 'advads-mapi-reconstructed-code'
				},
				success: function ( resp, status, XHR ) {
					jQuery( '#mapi-loading-overlay' ).css( 'display', 'none' );
				},
				error:   function ( req, status, err ) {
					jQuery( '#mapi-loading-overlay' ).css( 'display', 'none' );
				}
			} );
		}
	}

	mapiMayBeSaveAdCode() {
		// MAPI not set up
		if ( typeof AdsenseMAPI.hasToken === 'undefined' ) {
			return;
		}
		const slotId = jQuery( '#unit-code' ).val();
		if ( ! slotId ) {
			return;
		}

		const type      = jQuery( '#unit-type' ).val();
		const width     = jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[width]"]' ).val().trim();
		const height    = jQuery( '#advanced-ads-ad-parameters-size input[name="advanced_ad[height]"]' ).val().trim();
		const layout    = jQuery( '#ad-layout' ).val();
		const layoutKey = jQuery( '#ad-layout-key' ).val();

		let code = false;

		switch ( type ) {
			case 'in-feed':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
					'<ins class="adsbygoogle" ' +
					'style="display:block;" ' +
					'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
					'data-ad-slot="' + slotId + '" ' +
					'data-ad-layout-key="' + layoutKey + '" ';
				code += 'data-ad-format="fluid"></ins>' +
					'<script>' +
					'(adsbygoogle = window.adsbygoogle || []).push({});' +
					'</script>';
				break;
			case 'in-article':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
					'<ins class="adsbygoogle" ' +
					'style="display:block;text-align:center;" ' +
					'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
					'data-ad-slot="' + slotId + '" ' +
					'data-ad-layout="in-article" ' +
					'data-ad-format="fluid"></ins>' +
					'<script>' +
					'(adsbygoogle = window.adsbygoogle || []).push({});' +
					'</script>';
				break;
			case 'matched-content':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
					'<ins class="adsbygoogle" ' +
					'style="display:block;" ' +
					'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
					'data-ad-slot="' + slotId + '" ' +
					'data-ad-format="autorelaxed"></ins>' +
					'<script>' +
					'(adsbygoogle = window.adsbygoogle || []).push({});' +
					'</script>';
				break;
			case 'link-responsive':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
					'<ins class="adsbygoogle" ' +
					'style="display:block;" ' +
					'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
					'data-ad-slot="' + slotId + '" ' +
					'data-ad-format="link"></ins>' +
					'<script>' +
					'(adsbygoogle = window.adsbygoogle || []).push({});' +
					'</script>';
				break;
			case 'link':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
					'<ins class="adsbygoogle" ' +
					'style="display:block;width:' + width + 'px;height:' + height + 'px" ' +
					'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
					'data-ad-slot="' + slotId + '" ' +
					'data-ad-format="link"></ins>' +
					'<script>' +
					'(adsbygoogle = window.adsbygoogle || []).push({});' +
					'</script>';
				break;
			case 'responsive':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
					'<ins class="adsbygoogle" ' +
					'style="display:block;" ' +
					'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
					'data-ad-slot="' + slotId + '" ' +
					'data-ad-format="auto"></ins>' +
					'<script>' +
					'(adsbygoogle = window.adsbygoogle || []).push({});' +
					'</script>';
				break;
			case 'normal':
				code = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' +
					'<ins class="adsbygoogle" ' +
					'style="display:inline-block;width:' + width + 'px;height:' + height + 'px" ' +
					'data-ad-client="ca-' + AdsenseMAPI.pubId + '" ' +
					'data-ad-slot="' + slotId + '"></ins>' +
					'<script>' +
					'(adsbygoogle = window.adsbygoogle || []).push({});' +
					'</script>';
				break;
			default:
		}

		if ( code ) {
			this.mapiSaveAdCode( code, slotId );
		}

	}

	getMapiAction( action ) {
		const self = this;
		if ( action === 'toggleidle' ) {
			return function ( ev, el ) {
				self.hideIdle = ! self.hideIdle;
				AdvancedAdsAdmin.AdImporter.refreshAds();
			};
		}
		return null;
	}
}

// Creates a Advanced_Ads_Adsense_Report_UI instance on the fly
window.Advanced_Ads_Adsense_Report_Helper = window.Advanced_Ads_Adsense_Report_Helper || {};
window.Advanced_Ads_Adsense_Report_Helper.init = function ( element ) {
	if ( jQuery( element ).attr( 'data-arguments' ) ) {
		try {
			const reportArgs = JSON.parse( jQuery( element ).attr( 'data-arguments' ) );
			jQuery( element ).data( 'advadsAdsenseReport', new Advanced_Ads_Adsense_Report_UI( element, reportArgs ) );
		} catch ( ex ) {
			console.error( 'Cannot parse report arguments' );
		}
	}
};

window.Advanced_Ads_Adsense_Report_Helper.getReportObject = function () {
	const reportElem = jQuery( '.advanced-ads-adsense-dashboard' );
	if ( reportElem.length ) {
		let report = reportElem.data( 'advadsAdsenseReport' );
		if ( typeof report.refresh === 'function' ) {
			return report;
		}
	}
	return false;
};

class Advanced_Ads_Adsense_Report_UI {
	constructor( el, args ) {
		this.$el    = jQuery( el );
		this.type   = args.type;
		this.filter = args.filter;
		this.init();
		this.refreshing = false;
	}

	// Update arguments attributes before refreshing.
	updateHtmlAttr() {
		this.$el.attr( 'data-arguments', JSON.stringify( {type: 'domain', filter: self.filter} ) );
	}

	// Get markup for the current arguments.
	refresh() {
		const self = this;
		this.$el.html( '<p style="text-align:center;"><span class="report-need-refresh spinner advads-ad-parameters-spinner advads-spinner"></span></p>' );
		jQuery.ajax( {
			type:     'POST',
			url:      ajaxurl,
			data:     {
				nonce:  window.Advanced_Ads_Adsense_Report_Helper.nonce,
				type:   this.type,
				filter: this.filter,
				action: 'advads_adsense_report_refresh'
			},
			success:  function ( response ) {
				if ( response.success && response.data && response.data.html ) {
					self.$el.html( response.data.html );
				}
			}, error: function ( request, status, error ) {
				console.log( 'Refreshing rerpot error: ' + error );
			}
		} );
	}

	// Initialization - events binding.
	init() {
		if ( this.$el.find( '.report-need-refresh' ).length ) {
			this.refresh();
		}
		const self = this;

		// Hide dropdown on click on everything but the dropdown and its children.
		jQuery( document ).on( 'click', '#wpwrap', function () {
			const dd = jQuery( '#advads_overview_adsense_stats .advads-stats-dd-button .advads-stats-dd-items' );
			if ( dd.is( ':visible' ) ) {
				dd.hide();
			}
		} );

		// Show the dropdown.
		jQuery( document ).on( 'click', '#advads_overview_adsense_stats .advads-stats-dd-button', function ( ev ) {
			// Stop bubbling. Prevents hiding the dropdown.
			ev.stopPropagation();
			const dd = jQuery( this ).find( '.advads-stats-dd-items' );
			if ( ! dd.is( ':visible' ) ) {
				dd.show();
			}
		} );

		// Dropdown item clicked.
		jQuery( document ).on( 'click', '.advads-stats-dd-button .advads-stats-dd-item', function () {
			self.filter = jQuery( this ).attr( 'data-domain' );
			self.updateHtmlAttr();
			self.refresh();
		} );
	}
}
