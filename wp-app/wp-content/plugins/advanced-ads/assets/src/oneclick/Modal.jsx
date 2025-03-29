/**
 * External Dependencies
 */
import jQuery from 'jquery';
import classnames from 'classnames';

/**
 * WordPress Dependencies
 */
import Select from './Select2';
import { useContext, useEffect, useRef, useState } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import { OneClickContext } from './store/context';

const selectOptions = {
	minimumInputLength: 3,
	dropdownParent: '#modal-id',
	ajax: {
		url: advancedAds.endpoints.ajaxUrl,
		dataType: 'json',
		delay: 250,
		data: (params) => {
			return {
				q: params.term,
				action: 'search_posts',
				security: advancedAds.oneclick.security,
			};
		},
		processResults(data) {
			return {
				results: data,
			};
		},
	}
};

export default function Modal({ open, onClose }) {
	const dialogRef = useRef(null);
	const [updating, setUpdating] = useState(false);
	const [saved, setSaved] = useState(false);
	const { modal } = advancedAds.oneclick;
	const { selectedMethod, selectedPage, selectedPageTitle, setMethod, setPage } = useContext(OneClickContext);
	const [localMethod, setLocalMethod] = useState(selectedMethod);
	const [localPage, setLocalPage] = useState(selectedPage);

	useEffect(() => {
		if (open) {
			dialogRef.current.showModal();
		} else {
			dialogRef.current.close();
		}
	}, [open]);

	const selectData = selectedPage ? [{ value: selectedPage, label: selectedPageTitle }] : [];
	const isDisabled = 'final' === localMethod || ! localPage;

	const updatePreview = () => {
		setUpdating(true);
		setSaved(true);

		jQuery.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'update_oneclick_preview',
				security: advancedAds.oneclick.security,
				method: localMethod,
				page: localPage,
			},
		}).complete(() => {
			setUpdating(false);
			setMethod(localMethod);
			setPage(localPage);
		});
	}

	const finalImport = () => {
		setSaved(true);

		jQuery.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'update_oneclick_preview',
				security: advancedAds.oneclick.security,
				method: localMethod,
			},
		}).complete(() => {
			setUpdating(false);
			setMethod(localMethod);
			setPage(0);
		});
	}

	const updateAndClose = () => {
		if (saved) {
			setSaved(false);
			onClose();
			return;
		}

		if ('page' === localMethod) {
			updatePreview();
		} else if ('final' === localMethod) {
			finalImport();
		}

		setSaved(false);
		onClose();
	}

	const finalTextClass = classnames('ml-7', {
		'import-active': 'final' === localMethod,
		'text-[#a7aaad]': 'final' !== localMethod,
	});
	const finalButtonClass = classnames('button button-primary advads-modal-close-action', {
		'button-primary': 'final' !== localMethod,
		'!bg-[#cc3000] !border-[#cc3000] !shadow-none': 'final' === localMethod,
	});

	return (
		<dialog id="modal-id" className="advads-modal" ref={dialogRef}>
			<a
				href="#close"
				className="advads-modal-close-background"
				onClick={onClose}
			>
				{advancedAds.oneclick.btnClose}
			</a>
			<div className="advads-modal-content">
				<div className="advads-modal-header">
					<a
						href="#close"
						className="advads-modal-close"
						title={advancedAds.oneclick.btnCancel}
						onClick={onClose}
					>
						&times;
					</a>
					<h3>{modal.title}</h3>
				</div>
				<div className="advads-modal-body">
					<div className="flex gap-x-8">
						<div>
							<strong>{modal.labelImport}</strong>
						</div>
						<div>
							<div className="mb-5">
								<label htmlFor="specific-page">
									<input
										type="radio"
										name="import-methods"
										id="specific-page"
										value="page"
										checked={'page' === localMethod}
										onChange={() => setLocalMethod('page')}
									/>
									<span className="pl-1">
										{modal.labelSpecificPage}
									</span>
								</label>
								<div className="ml-7 mt-6">
									<div>
										<Select
											defaultValue={localPage}
											data={selectData}
											options={selectOptions}
											style={{ width: '100%' }}
											disabled={'final' === localMethod}
											onChange={(value) => setLocalPage(value.target.value)}
										/>
									</div>
									<p className="buttons-set">
										<button className="button button-primary" disabled={isDisabled} onClick={updatePreview}>
											{modal.btnUpdate}
										</button>
										<a href={`${advancedAds.siteInfo.homeUrl}/?p=${selectedPage}`} target="_blank" className="button button-secondary !flex items-center gap-x-1" disabled={isDisabled}>
											<span>{modal.btnGoto}</span>
											<span className="dashicons dashicons-external"></span>
										</a>
										{updating && <img src={advancedAds.oneclick.spinner} alt="" className="h-[11px]" />}
									</p>
								</div>
							</div>
							<div>
								<label htmlFor="final-import">
									<input
										type="radio"
										name="import-methods"
										id="final-import"
										value="final"
										checked={'final' === localMethod}
										onChange={() => setLocalMethod('final')}
									/>
									<span className="pl-1">
										{modal.labelFinalImport}
									</span>
								</label>
								<div
									className={finalTextClass}
									dangerouslySetInnerHTML={{
										__html: modal.descFinalImport,
									}}
								/>
							</div>
						</div>
					</div>
				</div>
				<div className="advads-modal-footer">
					<div className="tablenav bottom">
						<a
							href="#close"
							className="button button-secondary advads-modal-close"
							onClick={onClose}
						>
							{advancedAds.oneclick.btnCancel}
						</a>

						<button
							type="submit"
							form=""
							className={finalButtonClass}
							onClick={updateAndClose}
						>
							{'final' === localMethod ? modal.btnFinal : modal.btnSave}
						</button>
					</div>
				</div>
			</div>
		</dialog>
	);
}
