export default function initialState() {
	const { oneclick } = advancedAds;
	const { options } = oneclick;

	return {
		count: 0,
		isConnected: oneclick.isConnected,
		showMetabox: oneclick.isConnected,
		currentStep: oneclick.isConnected ? 3 : 1,
		settings: { ...advancedAds.oneclick.options },

		// Methods
		selectedMethod: options.selectedMethod,
		selectedPage: options.selectedPage,
		selectedPageTitle: options.selectedPageTitle,
	};
}
