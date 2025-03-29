/**
 * External Dependencies
 */
import jQuery from 'jquery';
import classnames from 'classnames';

/**
 * WordPress Dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import { OneClickContext } from './store/context';
import { noticeSuccess, noticeError, removeAllNotices } from './Notices';

export default function AddonRow() {
	const { addonRow } = advancedAds.oneclick;
	const { isConnected, toggleMetabox, disconnect } = useContext(OneClickContext);

	const buttonRow = classnames('cta', {
		primary: !isConnected,
		secondary: isConnected,
	});

	const onDisconnect = () => {
		jQuery.ajax({
			url: advancedAds.endpoints.ajaxUrl,
			type: 'POST',
			data: {
				action: 'pubguru_disconnect',
				nonce: advancedAds.oneclick.security,
			},
			success: (response) => {
				if (response.success) {
					noticeSuccess(
						response.data.message
					);
					disconnect();
				} else {
					noticeError(
						response.data.message
					);
				}
			},
			error: (error) => {
				noticeError(
					'Error disconnecting: ' + error.statusText
				);
			},
		})
	};

	return (
		<div className="single-item add-on js-pubguru-connect">
			<div className="item-details">
				<div className="icon">
					<img src={addonRow.icon} alt="" />
				</div>
				<span></span>
				<div className="name">{addonRow.title}</div>
				<span></span>
				<div className="description">{addonRow.content}</div>
				<span></span>
				<div className={buttonRow}>
					{isConnected ? (
						<button
							className="button"
							onClick={onDisconnect}
						>
							<i className="dashicons dashicons-dismiss"></i>
							{addonRow.disconnect}
						</button>
					) : (
						<button
							className="button"
							onClick={() => {
								removeAllNotices();
								toggleMetabox(true);
							}}
						>
							<i className="dashicons dashicons-plus"></i>
							{addonRow.connect}
						</button>
					)}
				</div>
			</div>
		</div>
	);
}
