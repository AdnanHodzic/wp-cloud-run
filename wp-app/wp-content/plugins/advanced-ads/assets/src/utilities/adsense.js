export function authUrl() {
	const { wizard } = advancedAds;
	const params = new URLSearchParams({
		client_id: wizard.clientId,
		redirect_uri: wizard.redirectUri,
		state: wizard.state,
		access_type: 'offline',
		include_granted_scopes: 'true',
		prompt: 'consent',
		response_type: 'code',
	}).toString();
	return `${wizard.authUrl}&${params}`;
}

export function hasAuthCode() {
	const params = new URLSearchParams(document.location.search);
	return (
		params.get('code') &&
		'adsense' === params.get('route') &&
		params.get('nonce')
	);
}

export function submitCode() {
	const params = new URLSearchParams(document.location.search);
	return wp.ajax.post('advads_gadsense_mapi_confirm_code', {
		nonce: params.get('nonce'),
		code: params.get('code'),
	});
}

export function getAccountDetails(tokenData) {
	const params = new URLSearchParams(document.location.search);
	return wp.ajax.post('advads_gadsense_mapi_get_details', {
		nonce: params.get('nonce'),
		token_data: tokenData,
	});
}

export function getErrorMessage(response) {
	let message = response.statusText;
	try {
		message = response.responseJSON.data.error;
	} catch (e) {
		try {
			message = response.responseJSON.data.msg;
		} catch (ee) {
			try {
				message = response.responseJSON.data.raw;
			} catch (eee) {
				try {
					message = response.responseJSON.data.error_msg;
				} catch (eeee) {}
			}
		}
	}

	return message;
}
