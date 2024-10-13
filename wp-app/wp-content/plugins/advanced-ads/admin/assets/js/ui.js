(function ( $ ) {
	/**
	 * Button.
	 */
	$.fn.advads_button = function() {
		var $buttonset = jQuery( this );
		var $ancestor = $buttonset.parent();

		$buttonset.each( function() {
			$this = jQuery( this );
			if ( $this.data( 'advads_button' ) ) {
				return true;
			}
			$this.data( 'advads_button', true );

			var $button = jQuery( this );
			var $label = jQuery( 'label[for="' + $button.attr( 'id' ) + '"]', $ancestor );
			var type = $button.attr( 'type' );

			$button.addClass( 'advads-accessible' );
			$label.addClass( 'advads-button' );
			$label.wrapInner( '<span class="advads-button-text"></span>' );

			if ( $button.is( ':checked' ) ) {
				$label.addClass( 'advads-ui-state-active' );
			}

			$button.on('change', function() {
				var $changed = jQuery( this );
				var $label = jQuery( 'label[for="' + $changed.attr( 'id' ) + '"]', $ancestor );

				if ( type === 'radio' ) {
					$ancestor.find( 'label' ).removeClass( 'advads-ui-state-active' );
				}

				if ( $changed.is( ':checked' ) ) {
					$label.addClass( 'advads-ui-state-active' );
				} else {
					$label.removeClass( 'advads-ui-state-active' );
				}
			} );

		} );
	};
	/**
	 * Buttonset.
	 */
	$.fn.advads_buttonset = function() {
		var $that = jQuery( this );

		$that.each( function() {
			$buttonset = jQuery( this );

			if ( $buttonset.data( 'advads_buttonset' ) ) {
				return true;
			}
			$buttonset.data( 'advads_buttonset', true );

			var items = 'input[type=checkbox], input[type=radio]';
			var $all_buttons = $buttonset.find( items );

			if ( ! $all_buttons.length ) {
				return;
			}

			// Show selected checkboxes first.
			if ( jQuery.escapeSelector ) {
				$items = jQuery();
				$all_buttons.filter( ':checked' ).each( function() {
					$items = $items.add( $buttonset.find( 'label[for="' + jQuery.escapeSelector( this.id ) + '"]' ) );
					$items = $items.add( this );
				} );
				$items.prependTo( $buttonset );
			}

			$buttonset.addClass( 'advads-buttonset' );

			$all_buttons.advads_button();
		} );
	};

	/**
	 * Tooltip.
	 *
	 * @param {Function} options.content A function that returns the content.
	 */
	$.fn.advads_tooltip = function( options ) {
		var tooltip;
		var tooltip_target;

		if ( ! options.content ) {
			return this;
		}

		function open( event ) {
			var $target = jQuery( event.currentTarget );

			// check if the correct tooltip is already open.
			if ( tooltip && $target.is( tooltip_target ) ) {
				return;
			}
			if ( tooltip ) {
				tooltip.remove();
				tooltip = null;
				tooltip_target = null;
			}

			if ( event.type === 'mouseover' ) {
				jQuery( $target ).on( 'mouseleave', close );
			}
			if ( event.type === 'focusin' ) {
				jQuery( $target ).on( 'focusout', close );
			}

			var content = options.content.call( $target )
			if ( content ) {
				tooltip = $( '<div>' ).addClass( 'advads-tooltip' );
				const parent = typeof options.parent === 'function' ? options.parent.call( this, $target ) : 'body';
				$( '<div>' ).addClass( 'advads-tooltip-content' ).appendTo( tooltip );
				tooltip.appendTo( parent );
				tooltip.find( '.advads-tooltip-content' ).html( content );

				position = $target.offset();
				position.top += $target.outerHeight() + 15;
				tooltip.offset( position );
				tooltip_target = $target;

				tooltip.show();
			}

		}
		function close( event ) {
			if ( tooltip ) {
				tooltip.remove();
				tooltip = null;
				tooltip_target = null;
			}
		};

		this.each( function() {
			$this = jQuery( this );
			if ( $this.data( 'advads_tooltip' ) ) {
				return true;
			}
			$this.data( 'advads_tooltip', true );

			$this.on( 'mouseover focusin', open );
		} );
	};
} )( jQuery );
