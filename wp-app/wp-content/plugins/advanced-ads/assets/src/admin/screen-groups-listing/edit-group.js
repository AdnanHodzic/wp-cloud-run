import jQuery from 'jquery';

const $ = jQuery;

function handleAddAdToGroup() {
	const groupForm = $(this).closest('.advads-ad-group-form'),
		$ad = groupForm.find('.advads-group-add-ad-list-ads option:selected'),
		weightSelector = groupForm
			.find('.advads-group-add-ad-list-weights')
			.last(),
		adTable = groupForm.find('.advads-group-ads tbody');

	const adVal = $ad.val().match(/\d+/g);
	let adUrl = '';

	if (adVal) {
		adUrl = advancedAds.endpoints.editAd + adVal.pop();
	}

	const statusType = $ad.data('status');
	const statusString = $ad.data('status-string');

	// add new row if does not already exist
	if (
		$ad.length &&
		weightSelector.length &&
		!adTable.find('[name="' + $ad.val() + '"]').length
	) {
		adTable.append(
			$('<tr></tr>').append(
				$('<td></td>').html(
					`<a target="_blank" href="${adUrl}">${$ad.text()}</a>`
				),
				$('<td></td>').html(
					`<span class="advads-help advads-help-no-icon advads-ad-status-icon advads-ad-status-icon-${statusType}">
						<span class="advads-tooltip">${statusString}</span>
					</span>`
				),
				$('<td></td>').append(
					weightSelector
						.clone()
						.removeClass()
						.val(weightSelector.val())
						.prop('name', $ad.val())
				),
				'<td><button type="button" class="advads-remove-ad-from-group button">x</button></td>'
			)
		);
	}
}

function handleRemoveAdFromGroupClick() {
	const adRow = $(this).closest('tr');
	adRow.remove();
}

function showTypeOptions(el) {
	el.each(function () {
		const _this = jQuery(this);
		// first, hide all options except title and type
		_this
			.closest('.advads-ad-group-form')
			.find('.advads-option:not(.static)')
			.hide();
		const currentType = _this.val();

		// now, show only the ones corresponding with the group type
		_this
			.parents('.advads-ad-group-form')
			.find('.advads-group-type-' + currentType)
			.show();
	});
}

export default function () {
	// add ad to group
	$('.advads-group-add-ad button').on('click', handleAddAdToGroup);

	// remove ad from group
	$('#advads-ad-group-list').on(
		'click',
		'.advads-remove-ad-from-group',
		handleRemoveAdFromGroupClick
	);

	// handle switching of group types based on a class derrived from that type
	$('.advads-ad-group-type input').on('click', function () {
		showTypeOptions($(this));
	});

	// set default group options for each group
	showTypeOptions($('.advads-ad-group-type input:checked'));
}
