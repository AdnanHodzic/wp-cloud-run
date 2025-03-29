/**
 * External Dependencies
 */
import { wizard } from '@advancedAds/i18n';

/**
 * Internal Dependencies
 */
import StepFooter from '../StepFooter';

export default function CodeAd({ options, setOptions }) {
	return (
		<>
			<h1 className={`!mt-0 ${options.adCode ? 'text-gray-300' : ''}`}>
				{wizard.stepTitles.adCode}
			</h1>
			<div className="space-y-4">
				<div>
					<textarea
						name="ad_code_code"
						id="ad_code_code"
						className="w-full p-4 text-base"
						rows={6}
						placeholder={wizard.codeAd.inputPlaceholder}
						onChange={(event) =>
							setOptions('adCode', event.target.value)
						}
					/>
				</div>
			</div>
			<StepFooter
				isEnabled={options.adCode}
				enableText={wizard.codeAd.footerEnableText}
				disableText={wizard.codeAd.footerDisableText}
				onNext={() => {}}
			/>
		</>
	);
}
