(function( $ ) {
	'use strict';
	
	// The DOM needs to be fully loaded (including graphics, iframes, etc)
	$(window).on('load', function() {

		// Maximum value for the progressbar
		var winHeight = $(window).height(),
		docHeight = $(document).height();
		var max = docHeight - winHeight;
		$('.readingProgressbar').attr('max', max);
		
		var progressForeground = $('.readingProgressbar').attr('data-foreground');
		var progressBackground = $('.readingProgressbar').attr('data-background');
		var progressHeight = $('.readingProgressbar').attr('data-height');
		var progressPosition = $('.readingProgressbar').attr('data-position');
		var progressCustomPosition = $('.readingProgressbar').attr('data-custom-position');
		var progressFixedOrAbsolute = 'fixed';

		// Custom position
		if (progressPosition == 'custom') {
			$('.readingProgressbar').appendTo(progressCustomPosition);
			progressPosition = 'bottom';
			progressFixedOrAbsolute = 'absolute';
		}

		// Styles
		if ( progressPosition == 'top' ) {
			var progressTop = '0';
			var progressBottom = 'auto';
		} else {
			var progressTop = 'auto';
			var progressBottom = '0';
		}

		$('.readingProgressbar').css({
			'background-color' : progressBackground,
			'color' :  progressForeground,
			'height' :  progressHeight + 'px',
			'top' : progressTop,
			'bottom' : progressBottom,
			'position' : progressFixedOrAbsolute,
			'display' : 'block'
		});

		$('<style>.readingProgressbar::-webkit-progress-bar { background-color: transparent } .readingProgressbar::-webkit-progress-value { background-color: ' + progressForeground + ' } .readingProgressbar::-moz-progress-bar { background-color: ' + progressForeground + ' }</style>')
		.appendTo('head');

		// Inital value (if the page is loaded within an anchor)
		var value = $(window).scrollTop();
		$('.readingProgressbar').attr('value', value);
		// Maths & live update of progressbar value
		$(document).on('scroll', function() {
			value = $(window).scrollTop();
			$('.readingProgressbar').attr('value', value);
		});
	});
	
})( jQuery );
