import jQuery from 'jquery';

export default function () {
	const tabs = jQuery('.advads-tab-menu', '.advads-tab-container');
	tabs.on('click', 'a', function (event) {
		event.preventDefault();
		const link = jQuery(this);
		const parent = link.closest('.advads-tab-container');
		const target = jQuery(link.attr('href'));

		parent.find('a.is-active').removeClass('is-active');
		link.addClass('is-active');

		parent.find('.advads-tab-target').hide();
		target.show();
	});

	// Trigger tab
	tabs.each(function () {
		const thisContainer = jQuery(this);
		const { hash = false } = window.location;
		let tab = thisContainer.find('a:first');

		if (hash && thisContainer.find('a[href=' + hash + ']').length > 0) {
			tab = thisContainer.find('a[href=' + hash + ']');
		}
		tab.trigger('click');
	});
}
