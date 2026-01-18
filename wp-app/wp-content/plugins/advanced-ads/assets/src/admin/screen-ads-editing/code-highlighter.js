export default function () {
	'use strict';

	let editor = null;

	/**
	 * Check ad code and toggle warnings for shortcode or PHP.
	 */
	const checkSource = () => {
		const text = editor
			? editor.codemirror.getValue()
			: jQuery('#advads-ad-content-plain').val();
		const phpWarning = jQuery('#advads-parameters-php-warning');
		const allowPhpWarning = jQuery('#advads-allow-php-warning');
		phpWarning.hide();
		allowPhpWarning.hide();

		// Allow PHP is enabled
		if (jQuery('#advads-parameters-php').prop('checked')) {
			// ad content has opening php tag.
			if (/<\?(?:php|=)/.test(text)) {
				allowPhpWarning.show();
			} else {
				phpWarning.show();
			}
		}

		// Shortcode warning.
		jQuery('#advads-parameters-shortcodes-warning').toggle(
			jQuery('#advads-parameters-shortcodes').prop('checked') &&
				!/\[[^\]]+\]/.test(text)
		);
	};

	jQuery(document).on('keyup', '#advads-ad-content-plain', checkSource);

	jQuery(document).on(
		'click',
		'#advads-parameters-php,#advads-parameters-shortcodes',
		checkSource
	);

	jQuery(document).on('paramloaded', '#advanced-ads-ad-parameters', () => {
		let settings;
		try {
			settings = window.advancedAds.admin.codeMirror.settings;
		} catch (Ex) {
			editor = null;
			return;
		}

		if (
			'plain' !== jQuery('input[name="advanced_ad[type]"]:checked').val()
		) {
			editor = null;
			return;
		}

		const source = jQuery('#advads-ad-content-plain');

		if (!source.length) {
			editor = null;
			return;
		}

		editor = wp.codeEditor.initialize(source, settings);
		editor.codemirror.on('keyup', checkSource);

		window.advancedAds.admin.codeMirror = editor.codemirror;

		window.advancedAds = window.advancedAds || {};
		window.advancedAds.admin.getSourceCode = () => {
			return editor
				? editor.codemirror.getValue()
				: jQuery('#advads-ad-content-plain').val();
		};

		window.advancedAds.admin.setSourceCode = (text) => {
			if (editor) {
				editor.codemirror.setValue(text);
			} else {
				jQuery('#advads-ad-content-plain').val(text);
			}
		};
	});
}
