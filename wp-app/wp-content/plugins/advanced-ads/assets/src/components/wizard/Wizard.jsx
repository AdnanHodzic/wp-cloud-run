/* eslint-disable import/no-extraneous-dependencies */
import {
	cloneElement,
	memo,
	useMemo,
	useRef,
	useState,
	Children,
} from '@wordpress/element';

/**
 * Internal Dependencies
 */
import WizardContext from './wizardContext';

const Wizard = memo(
	({ header, footer, children, wrapper: Wrapper, startIndex = 1 }) => {
		const [activeStep, setActiveStep] = useState(startIndex - 1);
		const [isLoading, setIsLoading] = useState(false);
		const hasNextStep = useRef(true);
		const hasPreviousStep = useRef(false);
		const nextStepHandler = useRef(() => {});
		const stepCount = Children.toArray(children).length;

		hasNextStep.current = activeStep < stepCount - 1;
		hasPreviousStep.current = activeStep > 0;

		const goToNextStep = useRef(() => {
			if (hasNextStep.current) {
				setActiveStep((newActiveStep) => newActiveStep + 1);
			}
		});

		const goToPreviousStep = useRef(() => {
			if (hasPreviousStep.current) {
				nextStepHandler.current = null;
				setActiveStep((newActiveStep) => newActiveStep - 1);
			}
		});

		const goToStep = useRef((stepIndex) => {
			if (stepIndex >= 0 && stepIndex < stepCount) {
				nextStepHandler.current = null;
				setActiveStep(stepIndex);
			}
		});

		// Callback to attach the step handler
		const handleStep = useRef((handler) => {
			nextStepHandler.current = handler;
		});

		const doNextStep = useRef(async () => {
			if (hasNextStep.current && nextStepHandler.current) {
				try {
					setIsLoading(true);
					await nextStepHandler.current();
					setIsLoading(false);
					nextStepHandler.current = null;
					goToNextStep.current();
				} catch (error) {
					setIsLoading(false);
					throw error;
				}
			} else {
				goToNextStep.current();
			}
		});

		const wizardValue = useMemo(
			() => ({
				nextStep: doNextStep.current,
				previousStep: goToPreviousStep.current,
				handleStep: handleStep.current,
				isLoading,
				activeStep,
				stepCount,
				isFirstStep: !hasPreviousStep.current,
				isLastStep: !hasNextStep.current,
				goToStep: goToStep.current,
			}),
			[activeStep, stepCount, isLoading]
		);

		const activeStepContent = useMemo(() => {
			const reactChildren = Children.toArray(children);
			return reactChildren[activeStep];
		}, [activeStep, children]);

		const enhancedActiveStepContent = useMemo(
			() =>
				Wrapper
					? cloneElement(Wrapper, { children: activeStepContent })
					: activeStepContent,
			[Wrapper, activeStepContent]
		);

		return (
			<WizardContext.Provider value={wizardValue}>
				{header}
				{enhancedActiveStepContent}
				{footer}
			</WizardContext.Provider>
		);
	}
);

export default Wizard;
