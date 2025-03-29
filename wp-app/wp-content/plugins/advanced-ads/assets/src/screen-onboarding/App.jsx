/**
 * External Dependencies
 */
import { useState } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import Header from './Header';
import Footer from './Footer';
import StepWrapper from './StepWrapper';
import { Wizard } from '@components/wizard';
import Step1 from './steps/Step1';
import GoogleAdsense from './steps/GoogleAdsense';
import BannerAd from './steps/BannerAd';
import CodeAd from './steps/CodeAd';
import Congrats from './steps/Congrats';

let initDone = false;
function initialState() {
	const initial = {
		startIndex: 1,
		taskOption: 'google_adsense',
		googleAdsPlacement: 'manual',
		autoAdsOptions: [],
		adsenseData: false,
	};
	const params = new URLSearchParams(document.location.search);
	const wizardData = advancedAds.wizard.adsenseData;

	if (
		'adsense' === params.get('route') &&
		'advanced-ads-onboarding' === params.get('page')
	) {
		initial.startIndex = 2;
		initial.taskOption = 'google_adsense';
	}

	if (
		'image' === params.get('route') &&
		'advanced-ads-onboarding' === params.get('page')
	) {
		initial.startIndex = 2;
		initial.taskOption = 'ad_image';
	}

	if (!Array.isArray(wizardData.accounts)) {
		const adsenseId = wizardData['adsense-id'];
		initial.adsenseData = {
			account: {
				id: adsenseId,
				name: wizardData.accounts[adsenseId].details.name,
			},
		};
	}

	if (!initDone) {
		if (wizardData.amp) {
			initial.autoAdsOptions.push('enableAmp');
		}

		if (wizardData['page-level-enabled']) {
			initial.autoAdsOptions.push('enable');
		}

		initDone = true;
	}

	return initial;
}

export default function App() {
	const [options, setOptions] = useState({
		...initialState(),
	});

	const handleOptions = (key, value) => {
		const newOptions = {
			...options,
			[key]: value,
		};
		setOptions(newOptions);
	};

	return (
		<div className="advads-onboarding advads-onboarding-frame">
			<div className="absolute top-0 max-w-3xl w-full py-20">
				<Wizard
					header={<Header />}
					footer={<Footer />}
					startIndex={options.startIndex}
				>
					<StepWrapper>
						<Step1 options={options} setOptions={handleOptions} />
					</StepWrapper>
					{'google_adsense' === options.taskOption && (
						<StepWrapper className="pb-4">
							<GoogleAdsense
								options={options}
								setOptions={handleOptions}
							/>
						</StepWrapper>
					)}
					{'ad_image' === options.taskOption && (
						<StepWrapper className="pb-4">
							<BannerAd
								options={options}
								setOptions={handleOptions}
							/>
						</StepWrapper>
					)}
					{'ad_code' === options.taskOption && (
						<StepWrapper className="pb-4">
							<CodeAd
								options={options}
								setOptions={handleOptions}
							/>
						</StepWrapper>
					)}
					<StepWrapper>
						<Congrats
							options={options}
							setOptions={handleOptions}
						/>
					</StepWrapper>
				</Wizard>
			</div>
		</div>
	);
}
