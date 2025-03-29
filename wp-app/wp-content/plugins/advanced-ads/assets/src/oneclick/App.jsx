/**
 * WordPress Dependencies
 */
import { createPortal } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import AddonRow from './AddonRow';
import Metabox from './Metabox';
import Notices from './Notices';
import StoreProvider from './store/context';

function App() {
	return (
		<StoreProvider>
			{createPortal(
				<AddonRow />,
				document.getElementById('advads-oneclick-addon-row')
			)}
			<div className="mb-4">
				<Notices />
			</div>
			<Metabox />
		</StoreProvider>
	);
}

export default App;
