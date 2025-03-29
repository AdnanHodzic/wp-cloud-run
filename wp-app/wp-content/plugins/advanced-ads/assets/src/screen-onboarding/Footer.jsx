/**
 * External Dependencies
 */
import { wizard } from '@advancedAds/i18n';

/**
 * Internal Dependencies
 */
import { adminUrl } from '@utilities';
import { useWizard } from '@components/wizard';

export default function Footer() {
	const { isLastStep } = useWizard();

	if (isLastStep) {
		return null;
	}

	return (
		<div className="text-center">
			<a
				href={adminUrl('admin.php?page=advanced-ads')}
				className="no-underline text-base border-0 border-b-2 pb-0.5 border-solid border-gray-800 text-gray-800"
			>
				{wizard.exitLabel}
			</a>
		</div>
	);
}
