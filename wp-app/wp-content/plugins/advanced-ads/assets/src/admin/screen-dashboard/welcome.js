import jQuery from 'jquery';

export default function () {
	jQuery(document).on('click', '#dismiss-welcome i', function () {
		jQuery.ajax(window.ajaxurl, {
			method: 'POST',
			data: {
				action: 'advads_dismiss_welcome',
			},
			success() {
				jQuery('#welcome').remove();
			},
		});
	});
}
