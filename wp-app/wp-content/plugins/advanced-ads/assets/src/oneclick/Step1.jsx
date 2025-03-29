/**
 * WordPress Dependencies
 */
import { useContext, useState } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import { OneClickContext } from './store/context';

export default function Step1() {
	const [state, setState] = useState(false);
	const { setStep, toggleMetabox } = useContext(OneClickContext);
	const { step1 } = advancedAds.oneclick;

	const onCancel = () => {
		setState(false);
		toggleMetabox(false);
		setStep(1);
	};

	const onAgree = () => {
		setStep(2);
	};

	return (
		<div className="mt-6 mb-8">
			<p dangerouslySetInnerHTML={{ __html: step1.content }}></p>
			<p>
				<label htmlFor="consent">
					<input
						type="checkbox"
						id="consent"
						onClick={() => setState(!state)}
					/>
					<span>{step1.agreeText}</span>
				</label>
			</p>
			<p className="buttons-set">
				<button
					className="button button-primary"
					disabled={!state}
					onClick={onAgree}
				>
					{step1.btnAgree}
				</button>
				<button className="button" onClick={onCancel}>
					{advancedAds.oneclick.btnCancel}
				</button>
			</p>
		</div>
	);
}
