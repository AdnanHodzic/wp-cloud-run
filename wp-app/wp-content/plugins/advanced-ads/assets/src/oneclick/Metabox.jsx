/**
 * WordPress Dependencies
 */
import { useContext } from '@wordpress/element';
import { OneClickContext } from './store/context';

/**
 * Internal Dependencies
 */
import Step1 from './Step1';
import Step2 from './Step2';
import Step3 from './Step3';
import StepWrapper from './StepWrapper';

export default function Metabox() {
	const { showMetabox, currentStep } = useContext(OneClickContext);
	const { metabox } = advancedAds.oneclick;
	if (!showMetabox) {
		return null;
	}

	return (
		<div id="advads-m2-connect" className="postbox position-full">
			<h2 className="hndle">
				<span>{metabox.title}</span>
			</h2>
			<div className="inside">
				{1 === currentStep && (
					<StepWrapper title={advancedAds.oneclick.step1.title}>
						<Step1 />
					</StepWrapper>
				)}
				{2 === currentStep && (
					<StepWrapper title={advancedAds.oneclick.step2.title}>
						<Step2 />
					</StepWrapper>
				)}
				{3 === currentStep && (
					<StepWrapper>
						<Step3 />
					</StepWrapper>
				)}
				<footer>
					<a
						href={metabox.visitLink}
						target="_blank"
						rel="noreferrer"
					>
						{metabox.visitText}
						<span className="screen-reader-text">
							{' '}
							(opens in a new tab)
						</span>
						<span
							aria-hidden="true"
							className="dashicons dashicons-external"
						></span>
					</a>
				</footer>
			</div>
		</div>
	);
}
