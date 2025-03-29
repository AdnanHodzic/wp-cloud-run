/**
 * External Dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { store as noticesStore } from '@wordpress/notices';
import { dispatch, useSelect, useDispatch } from '@wordpress/data';

export function removeAllNotices() {
	dispatch(noticesStore).removeAllNotices();
}

export function noticeSuccess(message, options) {
	dispatch(noticesStore).createSuccessNotice(message, options);
}

export function noticeError(message, options) {
	dispatch(noticesStore).createErrorNotice(message, options);
}

function Notice({ status, isDismissible, actions, onRemove, children, type }) {
	const wrapperClasses = classnames(
		'flex items-center justify-between notice notice-alt !px-3',
		`notice-${status}`,
		{ 'is-dismissible': isDismissible }
	);

	useEffect(() => {
		if ('timeout' !== type) {
			return;
		}

		const timeId = setTimeout(() => {
			onRemove();
		}, 5000);

		// Cleanup function to clear the timeout
		return () => clearTimeout(timeId);
	}, []); // Empty array means it will only run on mount

	return (
		<div className={wrapperClasses}>
			<div className="py-3" dangerouslySetInnerHTML={{ __html: children }} />
			{actions.map((action, index) => {
				return (
					<button
						key={index}
						className="button button-primary !ml-auto !mr-2"
						onClick={(event) => action.onClick(event, onRemove)}
					>
						{action.label}
					</button>
				)
			})}
			{ isDismissible && (
				<button
					className="button-link !no-underline"
					onClick={ onRemove }
				>
					<span className="dashicons dashicons-no-alt"></span>
				</button>
			) }
		</div>
	)
}
/**
 * Renders the notices
 *
 * @return {React.ReactNode} The rendered component.
 */
export default function Notices() {
	const notices = useSelect(
		( select ) => select( noticesStore ).getNotices(),
		[]
	);
	const { removeNotice } = useDispatch( noticesStore );

	return (
		<>
			{notices.map( ( notice ) => {
				return (
					<Notice key={notice.id} onRemove={() => removeNotice(notice.id)} {...notice}>
						{notice.content}
					</Notice>
				)
			})}
		</>
	);
}
