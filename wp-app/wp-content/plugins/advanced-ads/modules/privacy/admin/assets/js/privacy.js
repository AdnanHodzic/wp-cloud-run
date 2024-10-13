;(function ($) {
	var $cookieName = $('[name="' + window.advads_privacy.option_key + '[custom-cookie-name]'),
		$method = $('[name="' + window.advads_privacy.option_key + '[consent-method]"]:checked');

	// set required if radios change.
	$('[name="' + window.advads_privacy.option_key + '[consent-method]"]').on('change', function () {
		$method = $('[name="' + window.advads_privacy.option_key + '[consent-method]"]:checked');
		$cookieName.prop('required', $method.val() === 'custom');
	});

	// if enabled status changes, set required.
	$('[name="' + window.advads_privacy.option_key + '[enabled]"]').on('change', function () {
		$cookieName.prop('required', ($(this).is(':checked') ? $method.val() === 'custom' : false));
	});
})(jQuery);
