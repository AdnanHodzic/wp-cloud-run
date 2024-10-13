( function () {
	window.advanced_ads_ready_queue = window.advanced_ads_ready_queue || [];

	// replace native push method with our advanced_ads_ready function; do this early to prevent race condition between pushing and the loop.
	advanced_ads_ready_queue.push = window.advanced_ads_ready;

	// handle all callbacks that have been added to the queue previously.
	for ( var i = 0, length = advanced_ads_ready_queue.length; i < length; i ++ ) {
		advanced_ads_ready( advanced_ads_ready_queue[i] );
	}
} )();
