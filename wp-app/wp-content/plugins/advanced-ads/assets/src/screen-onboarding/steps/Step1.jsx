/**
 * External Dependencies
 */
import { wizard } from '@advancedAds/i18n';

/**
 * Internal Dependencies
 */
import { useWizard } from '@components/wizard';
import RadioList from '@components/inputs/RadioList';
import IconAdSense from '../../icons/AdSense';
import IconCode from '../../icons/Code';
import IconImage from '../../icons/Image';

export default function Step1({ setOptions }) {
	const { nextStep } = useWizard();

	const taskOptions = [
		{
			label: (
				<>
					<IconAdSense />
					<span>{wizard.firstStep.taskAdSense}</span>
				</>
			),
			value: 'google_adsense',
		},
		{
			label: (
				<>
					<IconImage />
					<span>{wizard.firstStep.taskImage}</span>
				</>
			),
			value: 'ad_image',
		},
		{
			label: (
				<>
					<IconCode />
					<span>{wizard.firstStep.taskCode}</span>
				</>
			),
			value: 'ad_code',
		},
	];

	return (
		<>
			<p className="font-medium mt-0">{wizard.firstStep.stepHeading}</p>
			<p className="mt-4 font-medium">
				<label className="advads-input-radio" htmlFor="agreement">
					<input
						type="checkbox"
						name="agreement"
						id="agreement"
						onChange={(event) =>
							setOptions('agreement', event.target.value)
						}
					/>
					<span
						dangerouslySetInnerHTML={{
							__html: wizard.firstStep.agreementText,
						}}
					/>
				</label>
			</p>
			<h2>{wizard.firstStep.inputTitle}</h2>
			<RadioList
				id="task"
				className="!mb-0"
				isButton
				options={taskOptions}
				onChange={(value) => {
					setOptions('taskOption', value);
					nextStep();
				}}
			/>
		</>
	);
}
