export default function () {
	document
		.querySelectorAll('.advads-multiple-subscribe_button')
		.forEach((button) => {
			button.addEventListener('click', function () {
				const parent = button.closest('.advads-multiple-subscribe');
				const groups = Array.from(
					parent.querySelectorAll(
						'input[name="advads-multiple-subscribe"]:checked'
					)
				).map(function (input) {
					return input.value;
				});

				if (groups.length === 0) {
					return;
				}

				const spinner = document.createElement('span');
				spinner.className = 'spinner advads-spinner';
				button.insertAdjacentElement('afterend', spinner);

				const formData = new FormData();
				formData.append('action', 'advads-multiple-subscribe');
				formData.append('groups', JSON.stringify(groups));
				formData.append('nonce', advadsglobal.ajax_nonce);

				fetch(ajaxurl, {
					method: 'POST',
					body: formData,
				})
					.then((response) => response.json())
					.then((response) => {
						button.style.display = 'none';
						const message = document.createElement('p');
						message.innerHTML = response.data.message;
						parent.innerHTML = '';
						parent.appendChild(message);
						parent.classList.add('notice-success', 'notice');
					})
					.catch((error) => {
						const message = document.createElement('p');
						message.innerHTML =
							error.responseJSON?.data?.message ||
							'An error occurred';
						parent.innerHTML = '';
						parent.appendChild(message);
						parent.classList.add('notice-error', 'notice');
					});
			});
		});
}
