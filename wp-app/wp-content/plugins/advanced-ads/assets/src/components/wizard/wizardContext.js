/* eslint-disable import/no-extraneous-dependencies */
import { createContext } from '@wordpress/element';

const WizardContext = createContext({
	isLoading: false,
	isFirstStep: true,
	isLastStep: false,
	stepCount: 0,
	activeStep: 0,
});

export default WizardContext;
