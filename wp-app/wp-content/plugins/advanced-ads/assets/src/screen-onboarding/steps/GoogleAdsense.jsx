/**
 * External Dependencies
 */
import { wizard } from '@advancedAds/i18n';

/**
 * Internal Dependencies
 */
import RadioList from '@components/inputs/RadioList';
import CheckboxList from '@components/inputs/CheckboxList';
import ErrorMessage from '@components/ErrorMessage';
import {
	authUrl,
	hasAuthCode,
	submitCode,
	getAccountDetails,
	getErrorMessage,
} from '../../utilities';
import StepFooter from '../StepFooter';
import Preloader from '../../components/Preloader';
import SelectAccount from '../../components/SelectAccount';

export default function GoogleAdsense({ options, setOptions }) {
	if (!options.adsenseData.account) {
		if (options.adsenseData.phase) {
			switch (options.adsenseData.phase) {
				case 'error':
					// Something went south, abort.
					return <ErrorMessage message={options.adsenseData.error} />;
				case 'select':
					// Ask the user to choose one account from the network account.
					return (
						<SelectAccount
							accounts={options.adsenseData.accountsList}
							tokenData={options.adsenseData.tokenData}
							done={(response) => {
								setOptions('adsenseData', {
									account: response.account,
								});
							}}
							fail={(response) => {
								let message = getErrorMessage(response);
								if (!message) {
									message = wizard.googleAd.errors.notSaved;
								}
								setOptions('adsenseData', {
									phase: 'error',
									error: message,
								});
							}}
						/>
					);
				default:
			}
		}

		if (hasAuthCode()) {
			// Submit the authorization code from Google's oAuth2 page.
			submitCode()
				.done(function (response) {
					// Got the refresh token, get account info.
					getAccountDetails(response.token_data)
						.done(function (accDetails) {
							if (accDetails.account) {
								// Standard account.
								setOptions('adsenseData', {
									account: accDetails.account,
								});
							}
							if (accDetails.details) {
								// Network account, show the list of child accounts to choose from.
								setOptions('adsenseData', {
									phase: 'select',
									accountsList: accDetails.details,
									tokenData: accDetails.token_data,
								});
							}
						})
						.fail(function (detailsRes) {
							// Error while getting account details.
							let message = getErrorMessage(detailsRes);
							if (!message) {
								message = wizard.googleAd.errors.notFetched;
							}
							setOptions('adsenseData', {
								phase: 'error',
								error: message,
							});
						});
				})
				.fail(function (response) {
					// Error while requesting the refresh (permanent) token.
					let message = getErrorMessage(response);
					if (!message) {
						message = wizard.googleAd.errors.notAuthorized;
					}
					setOptions('adsenseData', {
						phase: 'error',
						error: message,
					});
				});

			return <Preloader />;
		}

		return (
			<>
				<h1 className="!mt-0">{wizard.googleAd.stepHeading}</h1>
				<div className="mt-8 flex gap-x-8 justify-start items-center font-medium">
					<a
						className="button button-hero !text-base !px-3 !py-4"
						href={advancedAds.wizard.newAccountLink}
						target="_blank"
						rel="noopener noreferrer"
					>
						{wizard.googleAd.btnSignup}
					</a>
					<a
						className="button-onboarding !text-base !px-3 !py-4"
						href={authUrl()}
					>
						{wizard.googleAd.btnConnect}
					</a>
				</div>
				<StepFooter
					isEnabled={false}
					enableText={wizard.googleAd.footerProcessText}
					disableText={wizard.googleAd.footerDisableText}
				/>
			</>
		);
	}

	const enableText = (() => {
		switch (options.googleAdsPlacement) {
			case 'auto_ads':
				return wizard.googleAd.footerEnableText.autoAds;
			case 'manual':
				return wizard.googleAd.footerEnableText.manual;
			default:
				return wizard.googleAd.footerDisableText;
		}
	})();

	return (
		<>
			<h1 className="!mt-0 !text-gray-300">
				{wizard.googleAd.stepHeading}
			</h1>
			<div className="space-y-4">
				<p>
					{wizard.googleAd.labelConnected}{' '}
					{options.adsenseData.account.id}
					<br />
					{wizard.googleAd.labelAccount}{' '}
					{options.adsenseData.account.name}
				</p>
				<div>
					<h2
						className={
							'auto_ads' === options.googleAdsPlacement
								? '!text-gray-300'
								: null
						}
					>
						{wizard.googleAd.labelAdsPlacement}
					</h2>
					<RadioList
						id="google_ads_placement"
						options={wizard.googleAd.adsPlacement}
						value={options.googleAdsPlacement}
						onChange={(value) =>
							setOptions('googleAdsPlacement', value)
						}
					/>
				</div>
				{options.googleAdsPlacement &&
					'auto_ads' === options.googleAdsPlacement && (
						<div>
							<h2>{wizard.googleAd.labelAutoAds}</h2>
							<CheckboxList
								id="auto_ads"
								options={wizard.googleAd.autoAdsOptions}
								value={options.autoAdsOptions}
								onChange={(value) => {
									const newOptions = [
										...options.autoAdsOptions,
									];
									const index = newOptions.indexOf(value);
									if (index > -1) {
										newOptions.splice(index, 1);
									} else {
										newOptions.push(value);
									}

									setOptions('autoAdsOptions', newOptions);
								}}
							/>
						</div>
					)}
			</div>
			<StepFooter
				isEnabled={options.googleAdsPlacement}
				enableText={enableText}
				disableText={wizard.googleAd.footerDisableText}
			/>
		</>
	);
}
