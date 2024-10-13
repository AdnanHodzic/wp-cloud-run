// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact -- PHPCS can't handle es5 short functions
const modal = element => {
	let termination;
	let targetForm;

	/**
	 * Remove the pound sign from the location hash.
	 *
	 * @return {string}
	 */
	const getId = () => window.location.hash.replace( '#', '' );

	const showModal = () => {
		element.showModal();
		element.dispatchEvent( new CustomEvent( 'advads-modal-opened' ) );

		termination = new Advads_Termination( element );

		if ( targetForm ) {
			termination.collectValues();
		}
	};

	/**
	 * If the current hash matches the modal id attribute, open it.
	 */
	const showIfHashMatches = () => {
		if ( getId() === element.id ) {
			showModal();
		}
	};

	// Check whether to open modal on page load.
	showIfHashMatches();

	/**
	 * Listen to the hashchange event, to check if the current modal needs to be opened.
	 */
	window.addEventListener( 'hashchange', () => {
		showIfHashMatches();

		if ( getId() !== 'close' ) {
			return;
		}

		if ( ! targetForm || termination.terminationNotice( true ) ) {
			element.close();
		}
	} );

	/**
	 * Attach a click listener to all links referencing this modal and prevent their default action.
	 * By changing the hash on every click, we also create a history entry.
	 */
	document.querySelectorAll( 'a[href$="#' + element.id + '"]' ).forEach( link => {
		link.addEventListener( 'click', e => {
			e.preventDefault();
			showModal();
		} );
	} );

	/**
	 * On the cancel event, check for termination notice and fire a custom event.
	 */
	element.addEventListener( 'cancel', event => {
		event.preventDefault();
		if ( ! targetForm ) {
			element.close();
			return;
		}

		if ( termination.terminationNotice( true ) ) {
			element.close();

			termination.observers.disconnect();

			document.dispatchEvent( new CustomEvent( 'advads-modal-canceled', {
				detail: {
					modal_id: element.id
				}
			} ) );
		}
	} );

	/**
	 * On the close event, i.e., a form got submit, empty the hash to prevent form from reopening.
	 */
	element.addEventListener( 'close', event => {
		if ( getId() === element.id ) {
			window.location.hash = '';
		}
	} );

	// try if there is a form inside the modal, otherwise continue in catch.
	targetForm = element.querySelector( 'form' );
	if ( targetForm === null ) {
		try {
			targetForm = element.querySelector( 'button.advads-modal-close-action' ).form;
		} catch ( e ) {
		}
	}

	if ( targetForm ) {
		/**
		 * Listen for the keydown event in all inputs.
		 * If the enter key is pressed and the modal has a form, submit it, else do nothing.
		 */
		element.querySelectorAll( 'input' ).forEach( input => {
			input.addEventListener( 'keydown', e => {
				if ( e.key !== 'Enter' ) {
					return;
				}

				if ( targetForm.reportValidity() ) {
					targetForm.submit();
					return;
				}

				// if there are inputs, but there is no form associated with them, do nothing.
				e.preventDefault();
			} );
		} );
		targetForm.addEventListener( 'submit', () => {
			window.location.hash = '';
		} );
	}

	/**
	 * On the cancel buttons, check termination notice and close the modal.
	 */
	element.querySelectorAll( '.advads-modal-close, .advads-modal-close-background' ).forEach( button => {
		button.addEventListener( 'click', e => {
			e.preventDefault();
			element.dispatchEvent( new Event( 'cancel' ) );
		} );
	} );

	try {
		/**
		 * If the save button is not a `<button>` element. Close the form without changing the hash.
		 */
		element.querySelector( 'a.advads-modal-close-action' ).addEventListener( 'click', e => {
			e.preventDefault();
			element.close();
		} );
	} catch ( e ) {
	}
};

window.addEventListener( 'DOMContentLoaded', () => {
	try {
		if ( typeof document.querySelector( '.advads-modal[id^=modal-]' ).showModal !== 'function' ) {
			return;
		}
	} catch ( e ) {
		return;
	}
	[...document.getElementsByClassName( 'advads-modal' )].forEach( modal );
} );
