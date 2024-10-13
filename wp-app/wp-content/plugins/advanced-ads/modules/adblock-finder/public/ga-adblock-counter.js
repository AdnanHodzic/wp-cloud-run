function AdvAdsAdBlockCounterGA( UID ) {
	this.UID             = UID;
	this.analyticsObject = typeof gtag === 'function';

	var self = this;

	this.count = function () {
		gtag( 'event', 'AdBlock', {
			'event_category':  'Advanced Ads',
			'event_label':     'Yes',
			'non_interaction': true,
			'send_to':         self.UID
		} );
	};

	// pseudo-constructor
	( function () {
		if ( ! self.analyticsObject ) {
			// No one has requested gtag.js at this point, require it.
			var script   = document.createElement( 'script' );
			script.src   = 'https://www.googletagmanager.com/gtag/js?id=' + UID;
			script.async = true;

			document.body.appendChild( script );

			window.dataLayer     = window.dataLayer || [];
			window.gtag          = function () {
				dataLayer.push( arguments );
			};
			self.analyticsObject = true;
			gtag( 'js', new Date() );
		}

		var config = {'send_page_view': false, 'transport_type': 'beacon'};
		if ( window.advanced_ads_ga_anonymIP ) {
			config.anonymize_ip = true;
		}
		gtag( 'config', UID, config );
	} )();

	return this;
}

advanced_ads_check_adblocker( function ( is_enabled ) {
	// Send data to Google Analytics if an ad blocker was detected.
	if ( is_enabled ) {
		new AdvAdsAdBlockCounterGA( advanced_ads_ga_UID ).count();
	}
} );
