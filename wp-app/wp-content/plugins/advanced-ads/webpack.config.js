/**
 * External Dependencies
 */
const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils/config' );

const isProduction = process.env.NODE_ENV === 'production';

if ( ! isProduction ) {
	defaultConfig.devServer.allowedHosts = 'all';
}

// const rootPath = path.resolve(__dirname);
// const basePath = path.resolve(__dirname, 'src');

module.exports = {
	...defaultConfig,
	externals: {
		...defaultConfig.externals,
		window: 'window',
		jquery: 'jQuery',
		lodash: 'lodash',
		moment: 'moment',

		// Advanced ads.
		advancedAds: 'advancedAds',
		'@advancedAds/i18n': 'advancedAds.i18n',
	},
	resolve: {
		...defaultConfig.resolve,
		alias: {
			...defaultConfig.resolve.alias,
			'@root': path.join( __dirname, 'assets/src' ),
			'@components': path.join( __dirname, 'assets/src/components' ),
			'@utilities': path.join( __dirname, 'assets/src/utilities' ),
		},
	},
	entry: {
		...getWebpackEntryPoints(),
		// CSS
		// common: path.join(basePath, '/scss/admin/common.js'),

		// JavaScript
	},
	output: {
		filename: '[name].js', // Dynamically generate output file names
		path: path.resolve( __dirname, 'assets/dist' ),
	},
};

/** TODO: convert old files to new system */
/**
 * CSS Files
 */
// mix.sass(
// 	'assets/scss/admin/common.scss',
// 	'assets/css/admin/common.css'
// ).tailwind('./tailwind.config.common.js');
// mix.sass(
// 	'assets/scss/admin/screen-onboarding.scss',
// 	'assets/css/admin/screen-onboarding.css'
// ).tailwind('./tailwind.config.onboarding.js');
// mix.sass(
// 	'assets/scss/admin/notifications.scss',
// 	'assets/css/admin/notifications.css'
// ).tailwind();
// mix.sass(
// 	'assets/scss/admin/screen-ads-editing.scss',
// 	'assets/css/admin/screen-ads-editing.css'
// ).tailwind();
// mix.sass(
// 	'assets/scss/admin/screen-ads-listing.scss',
// 	'assets/css/admin/screen-ads-listing.css'
// ).tailwind();
// mix.sass(
// 	'assets/scss/admin/screen-dashboard.scss',
// 	'assets/css/admin/screen-dashboard.css'
// ).tailwind();
// mix.sass(
// 	'assets/scss/admin/screen-groups-listing.scss',
// 	'assets/css/admin/screen-groups-listing.css'
// ).tailwind();
// mix.sass(
// 	'assets/scss/admin/screen-placements-listing.scss',
// 	'assets/css/admin/screen-placements-listing.css'
// ).tailwind();
// mix.sass(
// 	'assets/scss/admin/screen-settings.scss',
// 	'assets/css/admin/screen-settings.css'
// ).tailwind();
// mix.sass(
// 	'assets/scss/admin/screen-status.scss',
// 	'assets/css/admin/screen-status.css'
// ).tailwind();
// mix.sass(
// 	'assets/scss/admin/wp-dashboard.scss',
// 	'assets/css/admin/wp-dashboard.css'
// ).tailwind();

/**
 * JavaScript Files
 */
// mix.js('public/assets/js/advanced.js', 'public/assets/js/advanced.min.js');
// mix.js('public/assets/js/ready.js', 'public/assets/js/ready.min.js');
// mix.js(
// 	'public/assets/js/ready-queue.js',
// 	'public/assets/js/ready-queue.min.js'
// );
// mix.js(
// 	'public/assets/js/frontend-picker.js',
// 	'public/assets/js/frontend-picker.min.js'
// );
// mix.js(
// 	'modules/adblock-finder/public/adblocker-enabled.js',
// 	'modules/adblock-finder/public/adblocker-enabled.min.js'
// );
// mix.js(
// 	[
// 		'modules/adblock-finder/public/adblocker-enabled.js',
// 		'modules/adblock-finder/public/ga-adblock-counter.js',
// 	],
// 	'modules/adblock-finder/public/ga-adblock-counter.min.js'
// );
// mix.combine(
// 	[
// 		'admin/assets/js/admin.js',
// 		'admin/assets/js/termination.js',
// 		'admin/assets/js/dialog-advads-modal.js',
// 	],
// 	'admin/assets/js/admin.min.js'
// );

// // New files
// mix.js('assets/src/admin/notifications.js', 'assets/js/admin/notifications.js');
// mix.js('assets/src/admin/admin-common.js', 'assets/js/admin/admin-common.js');
// mix.js(
// 	'assets/src/admin/page-quick-edit.js',
// 	'assets/js/admin/page-quick-edit.js'
// );
// mix.js(
// 	'assets/src/admin/screen-ads-editing/index.js',
// 	'assets/js/admin/screen-ads-editing.js'
// );
// mix.js(
// 	'assets/src/admin/screen-ads-listing/index.js',
// 	'assets/js/admin/screen-ads-listing.js'
// );
// mix.js(
// 	'assets/src/admin/screen-dashboard/index.js',
// 	'assets/js/admin/screen-dashboard.js'
// );
// mix.js(
// 	'assets/src/admin/screen-groups-listing/index.js',
// 	'assets/js/admin/screen-groups-listing.js'
// );
// mix.js(
// 	'assets/src/admin/screen-placements-listing/index.js',
// 	'assets/js/admin/screen-placements-listing.js'
// );
// mix.js(
// 	'assets/src/admin/screen-settings/index.js',
// 	'assets/js/admin/screen-settings.js'
// );
// mix.js(
// 	'assets/src/admin/wp-dashboard/index.js',
// 	'assets/js/admin/wp-dashboard.js'
// );

// // React
// mix.js(
// 	'assets/src/screen-onboarding/onboarding.js',
// 	'assets/js/screen-onboarding.js'
// ).react();

// mix.js(
// 	'assets/src/admin/screen-tools/screen-tools.js',
// 	'assets/js/admin/screen-tools.js'
// ).react();

// mix.js(
// 	'assets/src/oneclick/main.js',
// 	'assets/js/admin/oneclick-onboarding.js'
// ).react();
