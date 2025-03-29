/* eslint-disable import/no-extraneous-dependencies */
import { useContext } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import WizardContext from './wizardContext';

export default function useWizard() {
	const context = useContext(WizardContext);

	if (!context) {
		throw Error('Wrap your step with `Wizard`');
	}

	return context;
}
