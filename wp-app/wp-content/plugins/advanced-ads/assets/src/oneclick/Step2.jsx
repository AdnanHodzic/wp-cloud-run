/**
 * External Dependencies
 */
import jQuery from 'jquery';

/**
 * WordPress Dependencies
 */
import { decodeEntities } from '@wordpress/html-entities';
import { useContext, useEffect, useState } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import { OneClickContext } from './store/context';

function Loader() {
	const { step2 } = advancedAds.oneclick;

	return (
		<div className="mt-6 mb-8">
			<p>{step2.loading}</p>
			<p>
				<img src={advancedAds.oneclick.spinner} alt="" />
			</p>
		</div>
	);
}

function DomainNotFound({ domain, onCancel, setDomain, setFetched }) {
	const { step2 } = advancedAds.oneclick;

	return (
		<div className="mt-6 mb-8">
			<p className="step-error">
				{String.format(
					decodeEntities(step2.notRegistered),
					advancedAds.oneclick.siteDomain
				)}
			</p>
			<p dangerouslySetInnerHTML={{ __html: step2.content }}></p>
			<p>
				<strong>{step2.inputLabel}</strong>{' '}
				<input
					type="text"
					value={domain}
					onChange={(event) => setDomain(event.target.value)}
				/>
			</p>
			<p className="buttons-set">
				<button
					className="button button-primary"
					disabled={'' === domain}
					onClick={() => setFetched(false)}
				>
					{advancedAds.oneclick.btnContinue}
				</button>
				<button className="button" onClick={onCancel}>
					{advancedAds.oneclick.btnCancel}
				</button>
			</p>
		</div>
	);
}

function ServerError({ error, onCancel, setFetched }) {
	const { step2 } = advancedAds.oneclick;

	return (
		<div className="mt-6 mb-8">
			<p className="step-error">
				{String.format(decodeEntities(step2.serverError), error)}
			</p>
			<p dangerouslySetInnerHTML={{ __html: step2.serverContent }}></p>
			<p className="buttons-set">
				<button
					className="button button-primary"
					onClick={() => setFetched(false)}
				>
					{advancedAds.oneclick.btnRetry}
				</button>
				<button className="button" onClick={onCancel}>
					{advancedAds.oneclick.btnCancel}
				</button>
			</p>
		</div>
	);
}

export default function Step2() {
	const [fetched, setFetched] = useState(false);
	const [domain, setDomain] = useState('');
	const [error, setError] = useState('');
	const { setStep, toggleMetabox, connected } = useContext(OneClickContext);

	const onCancel = () => {
		setDomain('');
		setFetched(false);
		toggleMetabox(false);
		setStep(1);
	};

	useEffect(() => {
		if (!fetched) {
			jQuery
				.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'pubguru_connect',
						nonce: advancedAds.oneclick.security,
						testDomain: domain,
					},
					dataType: 'json',
				})
				.done(function (response) {
					if (!response.success) {
						if ('connect_error' === response.code) {
							setFetched('server-error');
							setError(response.message);
						}

						if ('domain_not_found' === response.code) {
							setFetched('error');
						}

						return;
					}

					advancedAds.oneclick.options.connectedDomain =
						'' !== domain
							? domain
							: advancedAds.oneclick.siteDomain;
					connected();
				})
				.fail(function (jqXHR) {
					setFetched('server-error');
					setError(jqXHR.statusText);
				});
		}
	}, [fetched]);

	if (!fetched) {
		return <Loader />;
	}

	if (fetched === 'error') {
		return (
			<DomainNotFound
				domain={domain}
				onCancel={onCancel}
				setDomain={setDomain}
				setFetched={setFetched}
			/>
		);
	}

	if (fetched === 'server-error') {
		return (
			<ServerError
				error={error}
				onCancel={onCancel}
				setFetched={setFetched}
			/>
		);
	}

	return 'unknow error';
}
