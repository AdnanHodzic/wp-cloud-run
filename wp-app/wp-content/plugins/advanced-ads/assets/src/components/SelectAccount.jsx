import { wizard } from '@advancedAds/i18n';

export default function SelectAccount({ accounts, tokenData, done, fail }) {
	const params = new URLSearchParams(document.location.search);
	const options = [
		<option value="" key="select-account">
			{wizard.selectAccount.optionZero}
		</option>,
	];

	for (const id in accounts) {
		options.push(
			<option value={JSON.stringify(accounts[id])} key={id}>
				{accounts[id].name} ({id})
			</option>
		);
	}

	const saveSelection = function (event) {
		if (!event.target.value) {
			return;
		}

		event.target.disabled = true;
		const account = JSON.parse(event.target.value);

		wp.ajax
			.post('advads_gadsense_mapi_select_account', {
				nonce: params.get('nonce'),
				account,
				token_data: tokenData,
			})
			.done(function (response) {
				if ('function' === typeof done) {
					done.call(null, response);
				}
			})
			.fail(function (response) {
				if ('function' === typeof fail) {
					fail.call(null, response);
				}
				event.target.disabled = false;
			});
	};

	return (
		<>
			<h2>{wizard.selectAccount.title}</h2>
			<label htmlFor="g-account">
				<select id="g-account" onChange={(ev) => saveSelection(ev)}>
					{options}
				</select>
			</label>
		</>
	);
}
