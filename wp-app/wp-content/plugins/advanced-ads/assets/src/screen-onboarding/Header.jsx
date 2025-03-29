/**
 * External Dependencies
 */
import clx from 'classnames';

/**
 * Internal Dependencies
 */
import { useWizard } from '@components/wizard';
import { assetsUrl } from '@utilities';

export default function Header() {
	const { stepCount, activeStep } = useWizard();
	const steps = Array.apply(null, { length: stepCount });

	return (
		<div className="text-center">
			<img
				className="w-8/12 m-auto"
				src={assetsUrl('assets/img/advancedads-full-logo.svg')}
				alt=""
			/>
			<div className="advads-wizard-progress">
				{steps.map((step, index) => {
					const isActive = activeStep === index;
					const stepClassName = clx(
						'advads-wizard-progress--item',
						activeStep > index ? 'is-done' : '',
						isActive ? 'is-active' : ''
					);

					return (
						<div className={stepClassName} key={`step-${index}`}>
							<div className="advads-wizard-progress--count">
								{isActive ? `Step ${index + 1}` : index + 1}
							</div>
						</div>
					);
				})}
			</div>
		</div>
	);
}
