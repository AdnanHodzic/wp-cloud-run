import jQuery from 'jquery';

import connectButton from './connect-button';
import connectSettings from './connect-settings';

export default function () {
	connectButton();
	connectSettings();

	jQuery('#advads-overview').on('click', '.notice-dismiss', function (event) {
		event.preventDefault();
		const button = jQuery(this);
		const notice = button.parent();
		notice.fadeOut(500, function () {
			notice.remove();
		});
	});
}
