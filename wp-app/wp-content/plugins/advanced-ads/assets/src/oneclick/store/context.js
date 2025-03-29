/**
 * WordPress Dependencies
 */
import { createContext, useReducer } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import initialState from './initial';
import reducers from './reducers';
import actions from './actions';

// Create the context
export const OneClickContext = createContext(initialState());

export default function ({ children }) {
	const [state, dispatch] = useReducer(reducers, { ...initialState() });

	const store = {
		...state,
		dispatch,
		...actions(state, dispatch),
	};

	return (
		<OneClickContext.Provider value={store}>
			{children}
		</OneClickContext.Provider>
	);
}
