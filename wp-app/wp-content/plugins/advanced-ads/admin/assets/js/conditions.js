/**
 * Logic for Display and Visitor Conditions forms
 */

jQuery( document ).ready(function ( $ ) {
		/**
		 * Pressing the button to add a new condition to the list of conditions
		 */
		$( '.advads-conditions-new select' ).on( 'change', function () {
				// get the form fieldset and values.
				var condition_form_container     = $( this ).parents( 'fieldset' )
				var condition_type               = condition_form_container.find( '.advads-conditions-new select' ).val()
				var condition_title              = condition_form_container.find( '.advads-conditions-new select option:selected' ).text()
				var condition_index              = parseInt( condition_form_container.find( '.advads-conditions-index' ).val() )
				var condition_list_target_ID     = $.escapeSelector( condition_form_container.data( 'condition-list-target' ) ) // ID of the container into which the new condition is loaded.
				var condition_list_target        = $( '#' + condition_list_target_ID ) // container into which the new condition is loaded.
				var conditions_form_name         = condition_form_container.data( 'condition-form-name' ) // name prefix for the form.
				var conditions_connector_default = condition_form_container.data( 'condition-connector-default' ) // default connector option.
				var conditions_action            = condition_form_container.data( 'condition-action' ) // action to which to send the AJAX call to.
				if ( ! condition_type || '' == condition_type ) {
					return
				}
				condition_form_container.find( '.advads-loader' ).show() // show loader.
				$.ajax(
					{
						type: 'POST',
						url: ajaxurl,
						data: {
							action: conditions_action,
							type: condition_type,
							index: condition_index,
							form_name: conditions_form_name,
							nonce: advadsglobal.ajax_nonce
						},
						success: function ( r, textStatus, XMLHttpRequest ) {
							// add.
							if ( r ) {
								if ( 'or' === conditions_connector_default ) {
									// as used for display conditions.
									var connector = '<input style="display:none;" type="checkbox" name="' + conditions_form_name + '[' + condition_index + '][connector]" checked="checked" value="or" id="advads-conditions-' + condition_list_target_ID + '-' + condition_index + '-connector"><label for="advads-conditions-' + condition_list_target_ID + '-' + condition_index + '-connector">' + advadstxt.condition_or + '</label>'
									var newline   = '<tr class="advads-conditions-connector advads-conditions-connector-or"><td colspan="3">' + connector + '</td></tr><tr><td class="advads-conditions-type" data-condition-type="' + condition_type + '">' + condition_title + '</td><td>' + r + '</td><td><button type="button" class="advads-conditions-remove button">x</button></td></tr>'
								} else {
									// as used for visitor conditions.
									var connector = '<input type="checkbox" name="' + conditions_form_name + '[' + condition_index + '][connector]" value="or" id="advads-conditions-' + condition_list_target_ID + '-' + condition_index + '-connector"><label for="advads-conditions-' + condition_list_target_ID + '-' + condition_index + '-connector">' + advadstxt.condition_and + '</label>'
									var newline   = '<tr class="advads-conditions-connector advads-conditions-connector-and"><td colspan="3">' + connector + '</td></tr><tr><td class="advads-conditions-type" data-condition-type="' + condition_type + '">' + condition_title + '</td><td>' + r + '</td><td><button type="button" class="advads-conditions-remove button">x</button></td></tr>'
								}
								condition_list_target.find( 'tbody' ).append( newline )
								condition_list_target.find( 'tbody .advads-conditions-single.advads-buttonset' ).advads_buttonset()
								condition_list_target.find( 'tbody .advads-conditions-connector input' ).advads_button()
								// increase count.
								condition_index++
								condition_form_container.find( '.advads-conditions-index' ).val( condition_index )
								// reset select.
								condition_form_container.find( '.advads-conditions-new select' )[ 0 ].selectedIndex = 0;
								advads_display_condition_option_not_selected();
							}
						},
						error: function ( MLHttpRequest, textStatus, errorThrown ) {
							condition_form_container.find( '.advads-conditions-new' ).append( errorThrown )
						},
						complete: function ( MLHttpRequest, textStatus ) {
							condition_form_container.find( '.advads-conditions-new .advads-loader' ).hide() // hide loader.
						}
					}
				)
			}
		)
		// disable term in the term list of the appropriate condition by just clicking on it.
		$( document ).on( 'click', '.advads-conditions-terms-buttons .button', function ( e ) {
				$( this ).remove()
			}
		)
		// display input field to search for terms.
		$( document ).on( 'click', '.advads-conditions-terms-show-search', function ( e ) {
				e.preventDefault()
				// display input field.
				$( this ).siblings( '.advads-conditions-terms-search' ).show().focus()
				// register autocomplete.
				advads_register_terms_autocomplete( $( this ).siblings( '.advads-conditions-terms-search' ) )
				$( this ).next( 'br' ).show()
				$( this ).hide()
			}
		)

		// function for autocomplete.
		function advads_register_terms_autocomplete ( self ) {
			self.autocomplete(
				{
					classes: {
						'ui-autocomplete': 'advads-ui-autocomplete'
					},
					source: function ( request, callback ) {
						// var searchField  = request.term;
						advads_term_search( self, callback )
					},
					minLength: 1,
					select: function ( event, ui ) {
						// append new line with input fields.
						$( '<label class="button advads-button advads-ui-state-active"><span class="advads-button-text">' + ui.item.label + '<input type="hidden" name="' + self.data( 'inputName' ) + '" value="' + ui.item.value + '"></span></label>' ).appendTo( self.siblings( '.advads-conditions-terms-buttons' ) )

						// show / hide other elements
						// $( '.advads-display-conditions-individual-post' ).hide();
						// $( '.advads-conditions-postids-list .show-search a' ).show();
					},
					close: function ( event, ui ) {
						self.val( '' )
					}
				}
			)
		}

	// remove author from list by clicking on it.
	$(document).on(
		'click',
		'.advads-conditions-authors-buttons .button',
		function () {
			$(this).remove();
		}
	);
	// display input field to search for author.
	$(document).on(
		'click',
		'.advads-conditions-authors-show-search',
		function (e) {
			e.preventDefault();
			// display input field.
			$(this)
				.siblings('.advads-conditions-authors-search')
				.show()
				.focus();
			// register autocomplete.
			advadsRegisterAuthorAutocomplete(
				$(this).siblings('.advads-conditions-authors-search')
			);
			$(this).next('br').show();
			$(this).hide();
		}
	);

	// author search box autocomplete.
	function advadsRegisterAuthorAutocomplete(self) {
		self.autocomplete({
			classes: {
				'ui-autocomplete': 'advads-ui-autocomplete',
			},
			source(request, callback) {
				advadsAuthorSearch(self, callback);
			},
			minLength: 1,
			select(event, ui) {
				// append new line with input fields.
				$(
					'<label class="button advads-button advads-ui-state-active"><span class="advads-button-text">' +
						ui.item.label +
						'<input type="hidden" name="' +
						self.data('inputName') +
						'" value="' +
						ui.item.value +
						'"></span></label>'
				).appendTo(self.siblings('.advads-conditions-authors-buttons'));
			},
			close() {
				self.val('');
			},
		});
	}

		// display input field to search for post, page, etc.
		$( document ).on( 'click', '.advads-conditions-postids-show-search', function ( e ) {
				e.preventDefault()
				// display input field.
				$( this ).next().find( '.advads-display-conditions-individual-post' ).show()
				//$( '.advads-conditions-postids-search-line .description' ).hide();
				$( this ).hide()
			}
		)
		// register autocomplete to display condition individual posts.
		$( document ).on( 'focus', '.advads-display-conditions-individual-post', function ( e ) {
				var self = this
				if ( ! $( this ).data( 'autocomplete' ) ) { // If the autocomplete wasn't called yet:
					$( this ).autocomplete(
						{
							classes: {
								'ui-autocomplete': 'advads-ui-autocomplete'
							},
							source: function ( request, callback ) {
								var searchParam = request.term
								advads_post_search( searchParam, callback )
							},
							minLength: 1,
							select: function ( event, ui ) {
								// append new line with input fields
								var newline = $( '<label class="button advads-button advads-ui-state-active"><span class="advads-button-text">' + ui.item.label + '</span></label>' )
								$( '<input type="hidden" name="' + self.dataset.fieldName + '[value][]" value="' + ui.item.value + '"/>' ).appendTo( newline )
								newline.insertBefore( $( self ).parent( '.advads-conditions-postids-search-line' ) )
							},
							close: function ( event, ui ) {
								$( self ).val( '' )
							},
						} ).autocomplete().data( 'ui-autocomplete' )._renderItem = function ( ul, item ) {
						ul.addClass( 'advads-conditions-postids-autocomplete-suggestions' )
						return $( '<li></li>' ).append( '<span class=\'left\'>' + item.label + '</span>&nbsp;<span class=\'right\'>' + item.info + '</span>' ).appendTo( ul )
					}
				}
			}
		)
		// remove individual posts from the display conditions post list.
		$( document ).on(
			'click',
			'.advads-conditions-postid-buttons .button',
			function ( e ) {
				$( this ).remove()
			}
		)
		// display/hide error message if no option was selected
		// is also called on every click.
		function advads_display_condition_option_not_selected () {
			$( '.advads-conditions-not-selected' ).each(
				function () {
					if ( $( this ).siblings( 'input:checked' ).length ) {
						$( this ).hide();
					} else {
						$( this ).show();
					}
				}
			)
		}

		advads_display_condition_option_not_selected()

		// update error messages when an item is clicked.
		$( document ).on( 'click', '.advads-conditions-terms-buttons input[type="checkbox"], .advads-conditions-single input[type="checkbox"]', function () {
				// needs a slight delay until the buttons are updated.
				window.setTimeout( advads_display_condition_option_not_selected, 200 )
			}
		)
		// activate and toggle conditions connector option.
		$( '.advads-conditions-connector input' ).advads_button();

		// dynamically change label.
		jQuery( document ).on( 'click', '.advads-conditions-connector input', function () {
			if ( jQuery( this ).is( ':checked' ) ) {
				jQuery( this ).next( 'label' ).find( 'span' ).html( advadstxt.condition_or );
				jQuery( this ).parents( '.advads-conditions-connector' ).addClass( 'advads-conditions-connector-or' ).removeClass( 'advads-conditions-connector-and' )
			} else {
				jQuery( this ).next( 'label' ).find( 'span' ).html( advadstxt.condition_and );
				jQuery( this ).parents( '.advads-conditions-connector' ).addClass( 'advads-conditions-connector-and' ).removeClass( 'advads-conditions-connector-or' )
			}
		} );
		// remove a line with a display or visitor condition.
		$( document ).on( 'click', '.advads-conditions-remove', function () {
				$( this ).parents( '.advads-conditions-table tr' ).prev( 'tr' ).remove()
				$( this ).parents( '.advads-conditions-table tr' ).remove()
			}
		)
	}
)

/**
 * Callback for term search autocomplete
 *
 * @param {type} search term
 * @param {type} callback
 * @returns {obj} json object with labels and values
 */
function advads_term_search ( field, callback ) {

	// return ['post', 'poster'];
	var query = {
		action: 'advads-terms-search',
		nonce: advadsglobal.ajax_nonce
	}

	query.search = field.val()
	query.tax    = field.data( 'tagName' )

	var querying = true

	var results = {}
	jQuery.post( ajaxurl, query,
		function ( r ) {
			querying    = false
			var results = []
			if ( r ) {
				r.map(
					function ( element, index ) {
						results[ index ] = {
							value: element.term_id,
							label: element.name
						}
					}
				)
			}
			callback( results )
		},
		'json'
	)
}

/**
 * Callback for author search autocomplete
 *
 * @param {string}           search   author
 * @param {HTMLInputElement} field    html input field
 * @param {Function}         callback Callback function
 * @return {Object} JSON object with labels and values
 */

function advadsAuthorSearch(field, callback) {
	const query = {
		action: 'advads-authors-search',
		nonce: advadsglobal.ajax_nonce,
	};

	query.search = field.val();

	var querying = true;

	const results = [];
	// eslint-disable-next-line no-undef
	jQuery.post(
		// eslint-disable-next-line no-undef
		ajaxurl,
		query,
		function (r) {
			querying = false;
			if (r) {
				r.map(function (element, index) {
					results[index] = {
						value: element.data.ID,
						label: element.data.display_name,
					};
				});
			}
			callback(results);
		},
		'json'
	);
}

/**
 * Callback for post search autocomplete
 *
 * @param {str} searchParam
 * @param {type} callback
 * @returns {obj} json object with labels and values
 */
function advads_post_search ( searchParam, callback ) {

	// return ['post', 'poster'];
	var query = {
		action: 'advads-post-search',
		_ajax_linking_nonce: jQuery( '#_ajax_linking_nonce' ).val(),
		'search': searchParam,
		nonce: advadsglobal.ajax_nonce
	}

	var querying = true

	var results = {}
	jQuery.post( ajaxurl, query,
		function ( r ) {
			querying    = false
			var results = []
			if ( r ) {
				r.map(
					function ( element, index ) {
						results[ index ] = {
							label: element.title,
							value: element.ID,
							info: element.info
						}
					}
				)
			}
			callback( results )
		},
		'json'
	)
}
