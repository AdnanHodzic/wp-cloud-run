/**
 * WordPress Dependencies
 */
import { decodeEntities } from '@wordpress/html-entities';
import { useContext, useState } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import Modal from './Modal';
import Settings from './Settings';
import { OneClickContext } from './store/context';

export default function Step3() {
	const { step3 } = advancedAds.oneclick;
	const [open, toggleModal] = useState(false);
	const { selectedMethod, selectedPage } = useContext(OneClickContext);

	const onClose = () => toggleModal(false);

	return (
		<>
			<div className="mt-6 mb-8">
				<p>
					{String.format(
						decodeEntities(step3.yourDomain),
						advancedAds.oneclick.options.connectedDomain
					)}
				</p>
				{'final' === selectedMethod && (
					<p
						dangerouslySetInnerHTML={{ __html: step3.finalContent }}
					></p>
				)}
			</div>
			{'page' === selectedMethod && (
				<div className="mt-6 mb-8">
					<div className="subheader inline">{step3.title}</div>
					{!selectedPage ? (
						<div className="mt-6"
							dangerouslySetInnerHTML={{ __html: step3.importContent }}
						></div>
					) : (
						<p
							dangerouslySetInnerHTML={{ __html: step3.previewContent }}
						></p>
					)}
					<p className="buttons-set">
						<button
							className="button button-primary"
							onClick={() => toggleModal(true)}
						>
							{step3.btnImport}
						</button>
						{selectedPage > 0 && (
							<a href={`${advancedAds.siteInfo.homeUrl}/?p=${selectedPage}`} target="_blank" className="button button-secondary !flex items-center gap-x-1">
								<span>{advancedAds.oneclick.modal.btnGoto}</span>
								<span className="dashicons dashicons-external"></span>
							</a>
						)}
					</p>
				</div>
			)}

			<Modal open={open} onClose={onClose} />

			<Settings />
		</>
	);
}
