import { wizard } from '@advancedAds/i18n';

export default function Preloader() {
	return (
		<div className="absolute inset-0 flex justify-center items-center z-10 bg-white bg-opacity-70">
			<img
				alt={wizard.processing}
				src={`${advancedAds.endpoints.adminUrl}images/spinner-2x.gif`}
			/>
		</div>
	);
}
