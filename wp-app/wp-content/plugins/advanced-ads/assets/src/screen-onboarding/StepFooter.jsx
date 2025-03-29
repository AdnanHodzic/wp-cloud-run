/**
 * External Dependencies
 */
import { wizard } from '@advancedAds/i18n';

/**
 * Internal Dependencies
 */
import { useWizard } from '@components/wizard';
import Divider from '@components/Divider';

export default function StepFooter({
	isEnabled,
	enableText,
	disableText,
	onNext,
}) {
	const { previousStep, nextStep } = useWizard();

	const handleNext = async () => {
		if (onNext) {
			onNext();
		}

		nextStep();
	};

	return (
		<>
			<Divider className="mb-4" />
			<div className="flex items-center justify-between">
				<div>
					<button
						onClick={previousStep}
						className="button-onboarding !bg-white !border-gray-100 !text-gray-600"
					>
						{wizard.btnGoBack}
					</button>
				</div>
				<div>
					{isEnabled ? (
						<button
							className="button-onboarding"
							onClick={handleNext}
						>
							{enableText}
						</button>
					) : (
						<button className="button-onboarding" disabled={true}>
							{disableText}
						</button>
					)}
				</div>
			</div>
		</>
	);
}
