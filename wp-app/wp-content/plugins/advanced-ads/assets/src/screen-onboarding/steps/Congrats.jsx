/**
 * External Dependencies
 */
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { wizard } from '@advancedAds/i18n';

/**
 * Internal Dependencies
 */
import { adminUrl } from '@utilities';
import Divider from '@components/Divider';
import Checkmark from '../../icons/Checkmark';
import Upgradebox from '../../icons/UpgradeBox';

function ListItem({ title = '', text, icon = false }) {
	return (
		<div className="flex w-full gap-x-3 items-center">
			{icon && (
				<div className="mt-2">
					<Checkmark />
				</div>
			)}
			<div className={`grow ${icon ? '' : 'ml-7'}`}>
				<strong>{title}</strong> {text}
			</div>
		</div>
	);
}

function Newsletter({ email, setEmail }) {
	return (
		<>
			<h1 className="!mt-0">{wizard.newsletter.title}</h1>
			<div className="advads-admin-notice">
				<div className="advads-notice-box_wrapper flex gap-7 items-center">
					<input
						type="email"
						id="newsletter_email"
						className="advads-input-text"
						placeholder={wizard.newsletter.inputPlaceholder}
						value={email}
						onChange={(e) => setEmail(e.target.value)}
						style={{ minWidth: '65%', maxWidth: '65%' }}
					/>
					<div>
						<button
							className="button-onboarding advads-notices-button-subscribe"
							data-notice="nl_free_addons"
						>
							{wizard.newsletter.btnLabel}
						</button>
					</div>
				</div>
			</div>
		</>
	);
}

function Loader() {
	return (
		/* eslint-disable jsx-a11y/anchor-is-valid */
		<a
			href="#"
			className="button-onboarding button-onboarding--gray disabled"
		>
			{wizard.loading}
		</a>
		/* eslint-enable jsx-a11y/anchor-is-valid */
	);
}

function CongratsHeader(options, result) {
	let title, para;

	title = wizard.stepTitles.congrats.default;
	para = wizard.congrats.stepHeading;

	if (options.taskOption === 'google_adsense') {
		title =
			options.googleAdsPlacement === 'auto_ads'
				? wizard.stepTitles.congrats.adsenseAuto
				: wizard.stepTitles.congrats.adsenseManual;

		para =
			options.googleAdsPlacement === 'auto_ads'
				? wizard.congrats.adsenseAuto.stepHeading
				: wizard.congrats.adsenseManual.stepHeading;
	}

	return (
		<>
			<h1 className="!mt-0">{title}</h1>
			<div className="congrats-flex">
				<p
					className="text-justify min-w m-0"
					dangerouslySetInnerHTML={{
						__html: para,
					}}
				/>
				{getEditButton(options, result)}
			</div>

			{getLiveHeading(options, result)}
		</>
	);
}

function getEditButton(options, result) {
	if (options.taskOption === 'google_adsense' && result?.success) {
		if (
			options.googleAdsPlacement === 'auto_ads' &&
			result.adsenseAccount
		) {
			return (
				<div>
					<a
						href={result.adsenseAccount}
						className="button-onboarding button-onboarding--gray"
						target="_blank"
						rel="noreferrer"
					>
						{wizard.congrats.adsenseAuto.btnAccount}
					</a>
				</div>
			);
		}

		if (options.googleAdsPlacement === 'manual' && result.itemEditLink) {
			return (
				<div>
					<a
						href={result.itemEditLink}
						className="button-onboarding !bg-red-600 !border-red-700 !text-white"
					>
						{wizard.congrats.adsenseManual.btnEditItem}
					</a>
				</div>
			);
		}
	}

	if (result?.success && result.itemEditLink) {
		return (
			<div>
				<a
					href={result.itemEditLink}
					className="button-onboarding button-onboarding--gray"
				>
					{wizard.congrats.btnEditItem}
				</a>
			</div>
		);
	}

	return null;
}

function getLiveHeading(options, result) {
	if (
		options.taskOption === 'google_adsense' &&
		options.googleAdsPlacement === 'auto_ads'
	) {
		return null;
	}

	if (
		options.taskOption === 'google_adsense' &&
		options.googleAdsPlacement === 'manual'
	) {
		return (
			<div className="congrats-flex mt-7">
				<p
					className="m-0"
					dangerouslySetInnerHTML={{
						__html: wizard.congrats.adsenseManual.liveHeading,
					}}
				/>
			</div>
		);
	}

	return (
		<div className="congrats-flex mt-7">
			<p
				className="m-0"
				dangerouslySetInnerHTML={{
					__html: wizard.congrats.liveHeading,
				}}
			/>
			<div className="mr-3">
				{result && result.success && '' !== result.postLink ? (
					<a href={result.postLink} className="button-onboarding">
						{wizard.congrats.btnLiveAd}
					</a>
				) : (
					<Loader />
				)}
			</div>
		</div>
	);
}

export default function Congrats({ options }) {
	const [result, setResult] = useState(null);
	const [userEmail, setUserEmail] = useState('');

	if (result === null) {
		apiFetch({
			path: '/advanced-ads/v1/onboarding',
			method: 'POST',
			data: options,
		}).then((response) => {
			setResult(response);
		});
	}

	if (userEmail === '') {
		apiFetch({
			path: '/advanced-ads/v1/user-email',
			method: 'GET',
		}).then((email) => {
			setUserEmail(email);
		});
	}

	return (
		<>
			{CongratsHeader(options, result)}
			<Divider />
			<Newsletter email={userEmail} setEmail={setUserEmail} />
			<Divider />
			<h1 className="!mt-0">{wizard.congrats.upgradeHeading}</h1>
			<div className="congrats-flex items-center gap-x-12">
				<p className="text-justify m-0">
					{wizard.congrats.upgradeText}
				</p>
				<a
					href="https://wpadvancedads.com/add-ons/all-access/?utm_source=advanced-ads&utm_medium=link&utm_campaign=wizard-upgrade"
					className="button-onboarding !bg-red-600 !border-red-700 !text-white text-center"
					target="_blank"
					rel="noreferrer"
				>
					{wizard.congrats.btnUpgrade}
				</a>
			</div>

			<div className="flex gap-x-12 items-center">
				<div className="space-y-2 mt-4 text-lg tracking-wide grow">
					{wizard.congrats.upgradePoints.map((point, index) => (
						<ListItem key={`point-${index}`} {...point} />
					))}
				</div>
				<div>
					<Upgradebox className="w-40" />
				</div>
			</div>
			<Divider />
			<div className="text-right">
				<a
					href={adminUrl('admin.php?page=advanced-ads')}
					className="button-onboarding button-onboarding--gray"
				>
					{wizard.congrats.btnDashboard}
				</a>
			</div>
		</>
	);
}
