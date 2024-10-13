// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact -- PHPCS can't handle es5 short functions
function Advads_Termination( element ) {
	/**
	 * Function to reset the changed nodes to default values.
	 *
	 * @constructor
	 */
	function FormValues() {
		this.addedNodes   = [];
		this.removedNodes = [];
	};

	let initialFormValues = new FormValues(),
		changedFormValues = new FormValues();

	const blocklist = [
		'active_post_lock'
	];

	this.observers = {
		list: [],

		push: item => {
			this.observers.list.push( item );
		},

		disconnect: () => {
			this.observers.list.forEach( observer => {
				observer.disconnect();
			} );
			this.observers.list = [];
		}
	};

	/**
	 * Set an initial form value.
	 * Can be used e.g. when a field is updated after an AJAX call.
	 *
	 * @param {String} key The key of the value that should be changed in the initial form value array.
	 * @param {Node} input The input field node.
	 *
	 * @returns {void}
	 */
	this.setInitialValue = ( key, input ) => {
		if ( ! input || ! input.value ) {
			return;
		}
		initialFormValues[key] = input.value;
	};

	/**
	 * Collect input values.
	 * Checkboxes are true/false, unless they are part of a group.
	 * Radio buttons have a boolean value on the saved value, only the checked one will be collected.
	 *
	 * @param {FormValues} object
	 * @param {Node} input
	 * @return {FormValues}
	 */
	const collectInputValue = function ( object, input ) {
		/**
		 * Collect checkbox group values.
		 * If there are multiple checkboxes with the same `nome` attribute, collect all values for this group.
		 *
		 * @param {NodeList} group Iterable of inputs with the same `name` attribute.
		 * @return {FormValues}
		 */
		const collectCheckboxGroup = ( group ) => {

			object[group[0].name] = [];
			group.forEach( input => {
				if ( input.checked ) {
					object[input.name].push( input.value );
				}
			} );

			return object;
		};

		if ( input.type === 'checkbox' ) {
			const checkboxGroup = element.querySelectorAll( '[name="' + input.name + '"]' );
			if ( checkboxGroup.length > 1 ) {
				return collectCheckboxGroup( checkboxGroup, input );
			}

			object[input.name] = input.checked;

			return object;
		}

		// if a radio button is not checked, don't collect it.
		if ( input.type === 'radio' && ! input.checked ) {
			return object;
		}

		object[input.name] = input.value;

		return object;
	};

	/**
	 * Setup a mutationobserver to check for added and removed form fields.
	 * This especially applies to conditions.
	 *
	 * @type {MutationObserver}
	 */
	const addedRemovedObserver = new MutationObserver( mutations => {
		for ( const mutation of mutations ) {
			for ( const removedNode of mutation.removedNodes ) {
				const nodes = document.createTreeWalker( removedNode, NodeFilter.SHOW_ELEMENT );
				while ( nodes.nextNode() ) {
					if ( nodes.currentNode.tagName === 'INPUT' || nodes.currentNode.tagName === 'SELECT' ) {
						const index = changedFormValues.addedNodes.indexOf( nodes.currentNode.name );
						if ( index > - 1 ) {
							changedFormValues.addedNodes.splice( index, 1 );
						} else {
							changedFormValues.removedNodes.push( nodes.currentNode.name );
						}
					}
				}
			}
			for ( const addedNode of mutation.addedNodes ) {
				if ( addedNode.nodeType === Node.TEXT_NODE ) {
					continue;
				}

				const nodes = document.createTreeWalker( addedNode, NodeFilter.SHOW_ELEMENT );
				while ( nodes.nextNode() ) {
					if ( nodes.currentNode.tagName === 'INPUT' || nodes.currentNode.tagName === 'SELECT' ) {
						if ( nodes.currentNode.name === '' ) {
							continue;
						}
						changedFormValues.addedNodes.push( nodes.currentNode.name );
					}
				}
			}
		}
	} );

	// attach the mutation observer to the passed element.
	addedRemovedObserver.observe( element, {childList: true, subtree: true} );
	this.observers.push( addedRemovedObserver );

	/**
	 * Check if there are inputs that have been changed and if their value is different.
	 *
	 * @param {Object} reference The initial values when the modal loaded, indexed by name attribute.
	 * @param {Object} changed The input values that were changed, indexed by name.
	 *
	 * @return {boolean}
	 */
	this.hasChanged = ( reference, changed ) => {
		for ( const name in changed ) {
			if ( ! reference.hasOwnProperty( name ) || reference[name].toString() !== changed[name].toString() ) {
				return true;
			}
		}

		return false;
	};

	/**
	 * If the modal is associated with a form and any values have changed, ask for confirmation to navigate away.
	 * Returns true if the user agrees with termination, false otherwise.
	 *
	 * @param {boolean} reload Whether to reload the page on added and removed nodes (needed for the modal). Default false.
	 *
	 * @return {boolean}
	 */
	this.terminationNotice = ( reload = false ) => {
		if ( ! this.hasChanged( initialFormValues, changedFormValues ) ) {
			return true;
		}

		// ask user for confirmation.
		if ( window.confirm( window.advadstxt.confirmation ) ) {
			// if we have added or removed nodes, we might need to reload the page.
			if ( changedFormValues.addedNodes.length || changedFormValues.removedNodes.length ) {
				if ( reload ) {
					window.location.reload();
				}
				return true;
			}

			// otherwise, we'll replace the values with the previous values.
			for ( const name in changedFormValues ) {
				const input = element.querySelector( '[name="' + name + '"]' );
				if ( input === null ) {
					continue;
				}

				if ( input.type === 'checkbox' ) {
					input.checked = initialFormValues[name];
				} else if ( input.type === 'radio' ) {
					let value = (initialFormValues[name] !== null && initialFormValues[name] !== undefined) ? initialFormValues[name] : input.value;
					element.querySelector( '[name="' + name + '"][value="' + value + '"]' ).checked = true;
				} else {
					input.value = initialFormValues[name];
				}
			}

			return true;
		}

		return false;
	};

	/**
	 * Collect inputs in this modal and save their initial and changed values (if any).
	 */
	this.collectValues = () => {
		const isDialog = element.tagName === 'DIALOG';

		element.querySelectorAll( 'input, select, textarea' ).forEach( input => {
			if ( ! input.name.length || blocklist.includes( input.id ) || blocklist.includes( input.name ) ) {
				return;
			}

			// if the element itself is not a dialog but the input is within a dialog, ignore it. This accounts for split forms, e.g. the placements page where some inputs are hidden in a modal dialog.
			if ( ! isDialog && input.closest( 'dialog' ) ) {
				return;
			}

			initialFormValues = collectInputValue( initialFormValues, input );

			// if the input is `hidden` no change event gets triggered. Use MutationObservers to check for changes in the value attribute.
			if ( input.type === 'hidden' ) {
				const hiddenObserver = new MutationObserver( function ( mutations, observer ) {
					mutations.forEach( mutation => {
						if ( mutation.attributeName === 'value' ) {
							mutation.target.dispatchEvent( new Event( 'input' ) );
						}
					} );
				} );
				hiddenObserver.observe( element, {
					attributes: true,
					subtree:    true
				} );
				this.observers.push( hiddenObserver );
			}

			input.addEventListener( 'input', event => {
				changedFormValues = collectInputValue( changedFormValues, input );
			} );
		} );
	};
};
