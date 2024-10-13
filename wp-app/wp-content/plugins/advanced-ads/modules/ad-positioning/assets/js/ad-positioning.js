( () => {
	const labels           = document.getElementsByClassName( 'advads-ad-positioning-position-wrapper' ),
		  options          = document.getElementsByClassName( 'advads-ad-positioning-position-option' ),
		  marginLeftInput  = document.getElementById( 'advads-ad-positioning-spacing-left' ),
		  marginRightInput = document.getElementById( 'advads-ad-positioning-spacing-right' );

	for ( const option of options ) {
		option.addEventListener( 'change', event => {
			for ( let label of labels ) {
				label.classList.remove( 'is-checked' );
			}
			option.parentElement.classList.add( 'is-checked' );

			const position = event.target.value.split( '_' );

			marginLeftInput.readOnly  = position[0] === 'center';
			marginRightInput.readOnly = position[0] === 'center';
		} );
	}
} )();
