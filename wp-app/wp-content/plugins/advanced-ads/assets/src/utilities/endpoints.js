export function adminUrl(url) {
	const {
		endpoints: { adminUrl },
	} = advancedAds;
	return adminUrl + url;
}

export function assetsUrl(file) {
	const {
		endpoints: { assetsUrl },
	} = advancedAds;
	return assetsUrl + file;
}
