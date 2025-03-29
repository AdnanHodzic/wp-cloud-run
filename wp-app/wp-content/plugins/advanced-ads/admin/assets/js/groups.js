jQuery(document).ready(function ($) {
	function getCellValue(row, sortby, index) {
		const $td = $(row).find('td');

		if ('weight' === sortby) {
			return parseInt($td.eq(index).find('select').val()) || 0;
		}
		if ('ad' === sortby) {
			return $td.eq(index).find('a').text().trim();
		}

		return $td.eq(index).text().trim();
	}

	function sortAds(sortby, isAscending, table) {
		const colIndex = $(table).find(`th[data-sortby=${sortby}]`).index();
		const tbody = $(table).find('tbody');
		const rows = tbody.find('tr').toArray();

		rows.sort(function (a, b) {
			const aValue = getCellValue(a, sortby, colIndex);
			const bValue = getCellValue(b, sortby, colIndex);

			if (isNaN(aValue) || isNaN(bValue)) {
				return isAscending
					? aValue.localeCompare(bValue)
					: bValue.localeCompare(aValue);
			}

			return isAscending ? aValue - bValue : bValue - aValue;
		});

		tbody.append(rows);

		$(table)
			.find('th.group-sort')
			.removeClass('asc desc')
			.eq(colIndex)
			.addClass(isAscending ? 'asc' : 'desc');
	}

	$('.advads-group-ads').each(function () {
		const $this = $(this);
		// eslint-disable-next-line prefer-const
		let sortStates = {
			ad: true,
			status: true,
			weight: true,
		};

		$this.find('th.group-sort').on('click', function () {
			const sortby = $(this).data('sortby');
			sortAds(sortby, sortStates[sortby], this.closest('table'));
			sortStates[sortby] = !sortStates[sortby];
		});
	});
});
