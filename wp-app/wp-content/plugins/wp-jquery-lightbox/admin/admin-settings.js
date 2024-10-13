wp.domReady( function () {
	const lightboxVersionSelect = document.getElementById( 'lightpress_active_lightbox' );
	lightboxVersionSelect?.addEventListener( 'change', () => showActiveLightboxSettings() );
	let storedActiveSections = JSON.parse( sessionStorage.getItem( 'lightpressActiveSections' ) ) || [];
	showActiveLightboxSettings();

	/**
	 * Show settings UI for active lightobx.
	 *  - Update subheading to active lighbox.
	 *  - Show settings for active lightbox only.
	 *  - Hide settings for other lightboxes.
	 *  - For active lightbox, reopen specific active sections
	 */
	function showActiveLightboxSettings() {
		const activeLightbox = lightboxVersionSelect?.value.toLowerCase();
		const activeLightboxTitle = lightboxVersionSelect?.options[lightboxVersionSelect.selectedIndex].text;
		const isProPromo = 'lightpress-pro-promo' === activeLightbox;
		const saveButton = document.querySelector( '#submit' );

		// Hide Promo section of not promo
		if ( ! isProPromo ) {
			const promoSection = document.querySelector( '.pro-lightbox-promo' );
			if ( promoSection ) promoSection.remove();
			if ( saveButton ) saveButton.style.display = 'block';
		}

		// Update heading to active lightbox
		const generalSettingsSection = document.querySelector( '.general-settings-section' );
		const oldSubHeading = document.querySelector( '.active-lightbox-heading' );
		if ( oldSubHeading ) oldSubHeading.remove();
		const newSubHeading = document.createElement( 'h2' );
		newSubHeading.classList.add( 'active-lightbox-heading' );
		newSubHeading.innerHTML = activeLightboxTitle + ' Settings';
		generalSettingsSection?.after( newSubHeading );

		// Show settings only for the active lightbox
		const activeLightboxSections = document.querySelectorAll( '.sub-settings-section.' + activeLightbox );
		const inactiveLightboxSections = document.querySelectorAll( '.sub-settings-section:not(.' + activeLightbox + ')' );
		activeLightboxSections.length && activeLightboxSections.forEach( el => el.classList.remove( 'hide' ) );
		inactiveLightboxSections.length && inactiveLightboxSections.forEach( el => el.classList.add( 'hide' ) );
		sessionStorage.removeItem( 'lightpressActiveSections' );

		// Re-open previously open setting sections
		storedActiveSections.forEach( storedActiveSection => {
			const sectionOnPage = document.getElementById( storedActiveSection );
			// Need extra check in case invalid section name
			if ( sectionOnPage ) {
				sectionOnPage.classList.add( 'active' );
			}
		});

		// If no settings sections are open, open the first one
		const activeAndOpenLightboxSections = document.querySelectorAll( '.active.sub-settings-section.' + activeLightbox );
		if ( ! isProPromo && activeAndOpenLightboxSections.length === 0 ) {
			activeLightboxSections[0]?.classList.add( 'active' );
		}

		if ( isProPromo ) {
			renderProLightboxPromo();
		}
	}

	/**
	 * Hide/show setting sub-section on click.
	 */
	const sectionHeadings = document.querySelectorAll( '.sub-settings-section h2' );
	sectionHeadings.forEach( el => el.addEventListener( 'click', ( event ) => {
		currentSection = event.target.parentElement;
		currentSection.classList.toggle( 'active' );
		if ( currentSection.classList.contains( 'active' ) ) {
			storedActiveSections.push( currentSection.id );
		} else {
			storedActiveSections = storedActiveSections.filter( item => item !== currentSection.id );
		}
		sessionStorage.setItem( 'lightpressActiveSections', JSON.stringify( storedActiveSections ) );
	} ) );

	/**
	 * Handle form validation errors
	 * Ensure user can see error by opening relevant panel.
	 */
	const formInputs = document.querySelectorAll( 'input' );
	formInputs.forEach( input => input.addEventListener( 'invalid', function( event ) {
		sectionWithError = event.target.closest( '.sub-settings-section:not(.hide)' );
		sectionWithError.classList.add( 'active' );
	}));

	/**
	 * Render Pro Lightbox Promo
	 */
	function renderProLightboxPromo() {
		const saveButton = document.querySelector( '#submit' );
		saveButton.style.display = 'none';
		const promoSection = document.createElement( 'div' );
		promoSection.classList.add( 'pro-lightbox-promo' );
		promoSection.innerHTML = `
			<p>The brilliant new LightPress Pro Lightbox with more options and better performance. This is a Pro Feature.</p>
			<a class="pro-action-button" href="https://lightpress.io/pro-lightbox" target="_blank">Learn More</a>
			<a class="pro-action-button" href="https://lightpress.io/pro-lightbox" target="_blank">See Demos</a>
			<p style="font-style:italic;font-size:16px;'">See prices and buy directly from your WordPress dashboard <a href="${lightpress.proLandingUrl}">here</a>!</p>
		`;
		document.querySelector( '.active-lightbox-heading' ).after( promoSection );
	}
} );

(function($) {
	$( document ).ready( function() {
		window.onload = () => {
			if ( '1' === lightpress.openModal ) {
				const button = document.querySelector( '#lightpress-open-modal' );
				button.click();
			}
		};

		$( '.lightpress-optin-actions a' ).click( function() {
			const optinAction = $( this ).attr( 'data-optin-action' );
			const nonce = $( '.lightpress-optin-actions' ).attr( 'data-nonce' );
			const closeButton = $( '#TB_closeWindowButton' );
			$.post(
				ajaxurl,
				{
					action: 'lightpress-optin-action',
					optin_action: optinAction,
					_n: nonce
				},
				function( result ) {
					console.log( result );
				}
			);
			closeButton.click();
		});

	});
})( jQuery );
