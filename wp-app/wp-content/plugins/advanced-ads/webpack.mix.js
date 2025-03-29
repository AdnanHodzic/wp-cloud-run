/* eslint-disable import/no-extraneous-dependencies */
// webpack.mix.js

const mix = require('laravel-mix');
const { join } = require('path');
const packageData = require('./package.json');
require('./tools/laravel-mix/wp-pot');
require('mix-tailwindcss');

// Local config.
let localConfig = {};

try {
	localConfig = require('./webpack.mix.local');
} catch {}

// Webpack Config.
mix.webpackConfig({
	externals: {
		// Plugin
		advancedAds: 'advancedAds',

		// External
		jquery: 'jQuery',
		lodash: 'lodash',
		moment: 'moment',

		// Advanced ads.
		'@advancedAds/i18n': 'advancedAds.i18n',

		// WordPress Packages.
		'@wordpress/api-fetch': 'wp.apiFetch',
		'@wordpress/blocks': 'wp.blocks',
		'@wordpress/block-editor': 'wp.blockEditor',
		'@wordpress/components': 'wp.components',
		'@wordpress/compose': 'wp.compose',
		'@wordpress/data': 'wp.data',
		'@wordpress/date': 'wp.date',
		'@wordpress/dom-ready': 'wp.domReady',
		'@wordpress/editor': 'wp.editor',
		'@wordpress/edit-post': 'wp.editPost',
		'@wordpress/element': 'wp.element',
		'@wordpress/hooks': 'wp.hooks',
		'@wordpress/html-entities': 'wp.htmlEntities',
		'@wordpress/i18n': 'wp.i18n',
		'@wordpress/keycodes': 'wp.keycodes',
		'@wordpress/media-utils': 'wp.mediaUtils',
		'@wordpress/notices': 'wp.notices',
		'@wordpress/plugins': 'wp.plugins',
		'@wordpress/rich-text': 'wp.richText',
		'@wordpress/url': 'wp.url',
	},
});

// Aliasing Paths.
mix.alias({
	'@root': join(__dirname, 'assets/src'),
	'@components': join(__dirname, 'assets/src/components'),
	'@utilities': join(__dirname, 'assets/src/utilities'),
});

// Browsersync
if (undefined !== localConfig.wpUrl && '' !== localConfig.wpUrl) {
	mix.browserSync({
		proxy: localConfig.wpUrl,
		ghostMode: false,
		notify: false,
		ui: false,
		open: true,
		online: false,
		files: [
			'assets/css/**/*.css',
			'assets/css/**/*.min.css',
			'assets/js/**/*.js',
			'**/*.php',
		],
	});
}

/**
 * WordPress translation
 */
if (process.argv.includes('wpPot')) {
	mix.wpPot({
		output: packageData.wpPot.output,
		file: packageData.wpPot.file,
		skipJS: true,
		domain: packageData.wpPot.domain,
	});
}

/**
 * CSS Files
 */
mix.sass(
	'assets/scss/admin/common.scss',
	'assets/css/admin/common.css'
).tailwind('./tailwind.config.common.js');
mix.sass(
	'assets/scss/admin/screen-onboarding.scss',
	'assets/css/admin/screen-onboarding.css'
).tailwind('./tailwind.config.onboarding.js');
mix.sass(
	'assets/scss/admin/notifications.scss',
	'assets/css/admin/notifications.css'
).tailwind();
mix.sass(
	'assets/scss/admin/screen-ads-editing.scss',
	'assets/css/admin/screen-ads-editing.css'
).tailwind();
mix.sass(
	'assets/scss/admin/screen-ads-listing.scss',
	'assets/css/admin/screen-ads-listing.css'
).tailwind();
mix.sass(
	'assets/scss/admin/screen-dashboard.scss',
	'assets/css/admin/screen-dashboard.css'
).tailwind();
mix.sass(
	'assets/scss/admin/screen-groups-listing.scss',
	'assets/css/admin/screen-groups-listing.css'
).tailwind();
mix.sass(
	'assets/scss/admin/screen-placements-listing.scss',
	'assets/css/admin/screen-placements-listing.css'
).tailwind();
mix.sass(
	'assets/scss/admin/screen-settings.scss',
	'assets/css/admin/screen-settings.css'
).tailwind();
mix.sass(
	'assets/scss/admin/screen-status.scss',
	'assets/css/admin/screen-status.css'
).tailwind();
mix.sass(
	'assets/scss/admin/wp-dashboard.scss',
	'assets/css/admin/wp-dashboard.css'
).tailwind();

/**
 * JavaScript Files
 */
mix.js('public/assets/js/advanced.js', 'public/assets/js/advanced.min.js');
mix.js('public/assets/js/ready.js', 'public/assets/js/ready.min.js');
mix.js(
	'public/assets/js/ready-queue.js',
	'public/assets/js/ready-queue.min.js'
);
mix.js(
	'public/assets/js/frontend-picker.js',
	'public/assets/js/frontend-picker.min.js'
);
mix.js(
	'modules/adblock-finder/public/adblocker-enabled.js',
	'modules/adblock-finder/public/adblocker-enabled.min.js'
);
mix.js(
	[
		'modules/adblock-finder/public/adblocker-enabled.js',
		'modules/adblock-finder/public/ga-adblock-counter.js',
	],
	'modules/adblock-finder/public/ga-adblock-counter.min.js'
);
mix.combine(
	[
		'admin/assets/js/admin.js',
		'admin/assets/js/termination.js',
		'admin/assets/js/dialog-advads-modal.js',
	],
	'admin/assets/js/admin.min.js'
);

// New files
mix.js('assets/src/admin/notifications.js', 'assets/js/admin/notifications.js');
mix.js('assets/src/admin/admin-common.js', 'assets/js/admin/admin-common.js');
mix.js(
	'assets/src/admin/page-quick-edit.js',
	'assets/js/admin/page-quick-edit.js'
);
mix.js(
	'assets/src/admin/screen-ads-editing/index.js',
	'assets/js/admin/screen-ads-editing.js'
);
mix.js(
	'assets/src/admin/screen-ads-listing/index.js',
	'assets/js/admin/screen-ads-listing.js'
);
mix.js(
	'assets/src/admin/screen-dashboard/index.js',
	'assets/js/admin/screen-dashboard.js'
);
mix.js(
	'assets/src/admin/screen-groups-listing/index.js',
	'assets/js/admin/screen-groups-listing.js'
);
mix.js(
	'assets/src/admin/screen-placements-listing/index.js',
	'assets/js/admin/screen-placements-listing.js'
);
mix.js(
	'assets/src/admin/screen-settings/index.js',
	'assets/js/admin/screen-settings.js'
);
mix.js(
	'assets/src/admin/wp-dashboard/index.js',
	'assets/js/admin/wp-dashboard.js'
);

// React
mix.js(
	'assets/src/screen-onboarding/onboarding.js',
	'assets/js/screen-onboarding.js'
).react();

mix.js(
	'assets/src/admin/screen-tools/screen-tools.js',
	'assets/js/admin/screen-tools.js'
).react();

mix.js(
	'assets/src/oneclick/main.js',
	'assets/js/admin/oneclick-onboarding.js'
).react();
