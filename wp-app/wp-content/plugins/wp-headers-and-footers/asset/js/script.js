(function ($) {
	'use strict';

	$(window).on('load', function () {
		$.ready.then(function () {
			wp.codeEditor.initialize($('[id="wpheaderandfooter_basics[wp_header_textarea]"]'));
			wp.codeEditor.initialize($('[id="wpheaderandfooter_basics[wp_body_textarea]"]'));
			wp.codeEditor.initialize($('[id="wpheaderandfooter_basics[wp_footer_textarea]"]'));
		});

		
		$('#wpheaderandfooter_diagnostic_log-header').on('click', function (event) {
			event.preventDefault();
			$('#wpheaderandfooter_diagnostic_log-tab').trigger('click');
		});
		// Functionality of log file download ajax on click.
		$('.wpheaderandfooter-log-file').on('click', function (event) {
			event.preventDefault();

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpheadersandfooters_log_download',
					security: wpheadersandfooters_log.help_nonce,
				},
				beforeSend: function () {
					$(".log-file-spinner").show();
				},
				success: function (response) {

					$(".log-file-spinner").hide();
					$(".log-file-text").show();

					if (!window.navigator.msSaveOrOpenBlob) { // If msSaveOrOpenBlob() is supported, then so is msSaveBlob().
						$("<a />", {
							"download": "wp-headers-and-footers-log.txt",
							"href": "data:text/plain;charset=utf-8," + encodeURIComponent(response),
						}).appendTo("body").click(function () {
							$(this).remove()
						})[0].click()
					} else {
						var blobObject = new Blob([response]);
						window.navigator.msSaveBlob(blobObject,
							'wp-headers-and-footers.txt');
					}
					setTimeout(function () {
						$(".log-file-text").fadeOut()
					}, 3000);
				}
			});
		});
	});

})(jQuery);
