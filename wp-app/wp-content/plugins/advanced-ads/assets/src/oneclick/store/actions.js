export default function actions(state, dispatch) {
	function disconnect() {
		dispatch({ type: 'DISCONNECT' });
	}

	function connected() {
		dispatch({ type: 'CONNECTED' });
	}

	function toggleMetabox(mbState) {
		dispatch({ type: 'TOGGLE_METABOX', value: mbState });
	}

	function setStep(step) {
		dispatch({ type: 'SET_STEP', value: step });
	}

	function setMethod(method) {
		dispatch({ type: 'SET_METHOD', value: method });
	}

	function setPage(page) {
		dispatch({ type: 'SET_PAGE', value: page });
	}

	function updateSettings(name, status) {
		dispatch({
			type: 'UPDATE_SETTINGS',
			name,
			value: status,
		});
	}

	return {
		connected,
		disconnect,
		setMethod,
		setPage,
		setStep,
		toggleMetabox,
		updateSettings,
	};
}
