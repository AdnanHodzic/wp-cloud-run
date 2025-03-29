/**
 * External Dependencies
 */
import jQuery from 'jquery';

/**
 * WordPress Dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import { OneClickContext } from './store/context';
import { noticeSuccess, noticeError } from './Notices';
import actions from './store/actions';

function updateSetting(module, status) {
	return jQuery.ajax({
		url: ajaxurl,
		method: 'POST',
		data: {
			action: 'pubguru_module_change',
			security: advancedAds.oneclick.security,
			module,
			status,
		},
	});
}

function backupAdsTxt() {
	return jQuery.ajax({
		url: ajaxurl,
		method: 'POST',
		data: {
			action: 'pubguru_backup_ads_txt',
			security: advancedAds.oneclick.security,
		},
	});
}

function Setting({ id, label, className, option, disabled = false, children }) {
	const { settings, updateSettings } = useContext(OneClickContext);
	const checked = settings[option] ?? false;
	const name = id.replace(/-/g, '_').replace('pubguru_', '');

	const onChange = (event) => {
		const status = event.target.checked
		updateSetting(name, status)
			.done((response) => {
				if ( response.data.notice && '' !== response.data.notice ) {
					noticeError(
						response.data.notice,
						response.data.action ? {
							actions: [
								{
									label: response.data.action,
									onClick: (event, remove) => {
										event.target.disabled = true;
										backupAdsTxt()
											.done((response) => {
												remove();
												event.target.disabled = false;
												if ( response.success ) {
													noticeSuccess(response.data);
												} else {
													noticeError(response.data);
												}
											})
											.error((response) => {
												event.target.disabled = false;
												noticeError('Error: ' + response.statusText);
											});
									},
								},
							]
						} : null
					);
				}

				updateSettings(option, status);
			});
	};

	return (
		<div className={className}>
			<label htmlFor={id} className="advads-ui-switch">
				<input
					type="checkbox"
					id={id}
					checked={checked}
					onChange={onChange}
					disabled={disabled}
				/>
				<div></div>
				<span dangerouslySetInnerHTML={{ __html: label }} />
				{children}
			</label>
		</div>
	);
}

export default function Settings() {
	const { settings } = advancedAds.oneclick;
	const { settings: options, selectedMethod } = useContext(OneClickContext);
	const disabled = 'page' === selectedMethod;
	let headerBiddingLabel = settings.headerBidding;
	if (disabled) {
		headerBiddingLabel += ' &nbsp;<em class="muted">' + settings.onlyPreview + '</em>'
	}

	return (
		<div className="mb-8">
			<div className="subheader inline">{settings.title}</div>

			<div className="advads-ui-switch-list mt-6">
				<Setting
					id="pubguru-header-bidding"
					label={headerBiddingLabel}
					option="headerBidding"
					disabled={disabled}
				/>

				{options.headerBidding && (
					<Setting
						id="pubguru-header-bidding-at-body"
						label={settings.scriptLocation}
						className="ml-4"
						option="headerBiddingAtBody"
					/>
				)}

				<Setting
					id="pubguru-ads-txt"
					label={settings.adsTxt}
					option="adsTxt"
				/>

				<Setting
					id="pubguru-traffic-cop"
					label={settings.trafficCop}
					option="trafficCop"
				>
					{settings.hasTrafficCop && (<span className="pg-tc-trail">
						{settings.trafficCopTrial}
					</span>)}
				</Setting>

				<Setting
					id="pubguru-tag-conversion"
					className="hidden"
					label={settings.activateTags}
					option="tagConversion"
				/>
			</div>

			<p dangerouslySetInnerHTML={{ __html: settings.help }}></p>
		</div>
	);
}
