export default function reducers(state, action) {
	switch (action.type) {
		case 'SET_COUNT':
			return {
				...state,
				count: action.value,
			};
		case 'TOGGLE_METABOX':
			return {
				...state,
				showMetabox: action.value,
			};
		case 'SET_STEP':
			return {
				...state,
				currentStep: action.value,
			};
		case 'UPDATE_SETTINGS':
			return {
				...state,
				settings: {
					...state.settings,
					[action.name]: action.value,
				},
			};
		case 'SET_METHOD':
			return {
				...state,
				selectedMethod: action.value,
			};
		case 'SET_PAGE':
			return {
				...state,
				selectedPage: action.value,
			};
		case 'DISCONNECT':
			return {
				...state,
				isConnected: false,
				showMetabox: false,
				currentStep: 1,
			};
		case 'CONNECTED':
			return {
				...state,
				isConnected: true,
				showMetabox: true,
				currentStep: 3,
			};
		default:
			return state;
	}
}
