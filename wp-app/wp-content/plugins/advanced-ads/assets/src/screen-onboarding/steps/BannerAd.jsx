/**
 * External Dependencies
 */
import { wizard } from '@advancedAds/i18n';
import { useEffect } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import StepFooter from '../StepFooter';

export default function BannerAd({ options, setOptions }) {
	let fileFrame = null;

	const handleUpload = (event) => {
		event.preventDefault();

		if (fileFrame) {
			fileFrame.uploader.uploader.param('post_id', 0);
			fileFrame.open();
			return;
		}

		fileFrame = wp.media.frames.file_frame = wp.media({
			title: wizard.bannerAd.mediaFrameTitle,
			button: {
				text: wizard.bannerAd.mediaFrameButton,
			},
			multiple: false,
		});

		fileFrame.on('select', () => {
			const attachment = fileFrame
				.state()
				.get('selection')
				.first()
				.toJSON();

			setOptions('adImage', attachment);
		});

		fileFrame.open();
	};

	useEffect(() => {
		if (options.adImage) {
			const fileName = options.adImage.url.split('/').pop();
			const maxWidth = 250;
			const textP = document.createElement('p');
			textP.style.visibility = 'hidden';
			textP.style.whiteSpace = 'nowrap';
			textP.style.position = 'absolute';
			textP.innerText = fileName;
			document.body.appendChild(textP);
			const isTextLong = textP.offsetWidth > maxWidth;
			document.body.removeChild(textP);

			setOptions('isTextLong', isTextLong);
		}
	}, [options.adImage]);

	return (
		<>
			<h1 className={`!mt-0 ${options.adImage ? 'text-gray-300' : ''}`}>
				{wizard.stepTitles.adImage}
			</h1>
			{options.adImage ? (
				<div className="space-y-4">
					<div className="flex items-center gap-5">
						<button
							className="button-onboarding button-onboarding--gray"
							onClick={handleUpload}
						>
							{wizard.bannerAd.mediaBtnReplace}
						</button>
						<div
							className={`file-name-rtl m-0 ${options.isTextLong ? 'truncate' : ''}`}
						>
							<p>{options.adImage.url.split('/').pop()}</p>
						</div>
						<span className="dashicons dashicons-yes-alt flex items-center justify-center text-4xl w-9 h-9 text-primary"></span>
					</div>
					<div>
						<h2>{wizard.bannerAd.stepHeading}</h2>
						<input
							type="url"
							name="ad_image_url"
							id="ad_image_url"
							className="advads-input-text"
							placeholder={wizard.bannerAd.inputPlaceholder}
							onChange={(event) =>
								setOptions('adImageUrl', event.target.value)
							}
						/>
					</div>
				</div>
			) : (
				<button className="button-onboarding" onClick={handleUpload}>
					{wizard.bannerAd.mediaBtnUpload}
				</button>
			)}
			<StepFooter
				isEnabled={options.adImage}
				enableText={wizard.bannerAd.footerEnableText}
				disableText={wizard.bannerAd.footerDisableText}
				onNext={() => {}}
			/>
		</>
	);
}
