/**
 * Background plugin activation
 *
 * @param {string}  plugin path to the plugin's main file relative to WP's plugins folder.
 * @param {string}  name   the plugin name.
 * @param {string}  nonce  ajax nonce.
 * @param {Element} button the button that triggered the activation
 */
const activateAddOn = (plugin, name, nonce, button) => {
	button.classList.add('disabled');
	const slug = plugin.substring(plugin.indexOf('/') + 1, plugin.indexOf('.'));
	wp.ajax
		.post('advads_activate_addon', {
			_ajax_nonce: nonce,
			plugin,
			slug,
			name,
		})
		.done(function () {
			button.className = 'button active disabled';
			button.innerText = window.advadstxt.active;
			const icon = document.createElement('i');
			icon.className = 'dashicons';
			icon.style.cssText = 'content:"\\f147"';
			button.insertBefore(icon, button.firstChild);
		})
		.fail(function (response) {
			if ('undefined' !== typeof response.errorMessage) {
				// Error message from `wp_ajax_activate_plugin()`.
				button
					.closest('.cta')
					.parentNode.querySelector('.description').innerText =
					response.errorMessage;
			}
		});
};

export default function () {
	const addonBox = document.getElementById('advads_overview_addons');

	if (!addonBox) {
		return;
	}

	addonBox.addEventListener('click', (ev) => {
		const target = ev.target;

		if (
			'a' === target.tagName.toLowerCase() &&
			target.classList.contains('disabled')
		) {
			ev.preventDefault();
			return;
		}

		// Newsletter subscription.
		if (
			target.classList.contains('button') &&
			target.classList.contains('subscribe')
		) {
			ev.preventDefault();
			target.disabled = true;
			target.classList.add('disabled');

			wp.ajax
				.post('advads_newsletter', {
					nonce: target.dataset.nonce,
				})
				.done(function (response) {
					if (response) {
						target
							.closest('.item-details')
							.querySelector('.description').innerHTML =
							`<p style="font-weight: 600;">${response}</p>`;
					}
				})
				.fail(function (response) {
					try {
						target
							.closest('.item-details')
							.querySelector('.description').innerHTML =
							`<p style="font-weight: 600;">${response.responseJSON.data.message}</p>`;
					} catch (e) {}
				});
		}

		// Background plugin activation
		if (
			target.classList.contains('button') &&
			target.classList.contains('installed')
		) {
			const marker = '#activate-aaplugin_';
			const href = target.href ? target.href : '';

			if (-1 !== href.indexOf(marker)) {
				ev.preventDefault();
				const data = href.split('_');
				activateAddOn(
					data[2],
					decodeURIComponent(data[3]),
					data[1],
					target
				);
			}
		}
	});
}
