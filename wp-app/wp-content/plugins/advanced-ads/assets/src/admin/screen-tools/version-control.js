/* eslint-disable no-console */
import jQuery from 'jquery';

/**
 * Get usable versions. Fetch it from https://api.wordpress.org/plugins/info/1.0/advanced-ads.json if needed
 */
function getUsableVersions() {
	jQuery
		.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'advads_get_usable_versions',
				nonce: jQuery('#version-control-nonce').val(),
			},
		})
		.done((response) => {
			const versions = [];
			const versionSelect = jQuery('#plugin-version');
			for (const index in response.data.order) {
				const number = response.data.order[index];
				const selected = 0 === index ? ' selected' : '';
				versions.push(
					`<option value="${
						number + '|' + response.data.versions[number]
					}"${selected}>${number}</option>`
				);
			}

			versionSelect.prop('disabled', false).html(versions.join('\n'));
			jQuery('#install-version').prop('disabled', false);
		})
		.fail((response) => {
			console.error(response);
		});
}

/**
 * Launch the installation process
 *
 * @param {string} variables the form inputs values serialized.
 */
function installVersion(variables) {
	const inputs = jQuery('#plugin-version,#install-version').prop(
		'disabled',
		true
	);
	const spinner = jQuery('#install-version')
		.siblings('.spinner')
		.css('visibility', 'visible');

	jQuery
		.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'advads_install_alternate_version',
				vars: variables,
			},
		})
		.done((response) => {
			if (response.data.redirect) {
				document.location.href = response.data.redirect;
				return;
			}
			inputs.prop('disabled', false);
			spinner.css('visibility', 'hidden');
		})
		.fail((response) => {
			console.error(response);
			inputs.prop('disabled', false);
			spinner.css('visibility', 'hidden');
		});
}

export default function () {
	jQuery(document).on('submit', '#alternative-version', function (event) {
		event.preventDefault();
		installVersion(jQuery(this).serialize());
	});

	const pluginVersion = jQuery('#plugin-version');
	if (!pluginVersion.length) {
		return;
	}

	if (!pluginVersion.val()) {
		getUsableVersions();
	}
}
