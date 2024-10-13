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
		jquery: 'jQuery',
		lodash: 'lodash',
		moment: 'moment',
	},
});

// Aliasing Paths.
mix.alias({
	'@root': join(__dirname, 'assets/src'),
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
			'assets/css/*.css',
			'assets/css/*.min.css',
			'assets/js/*.js',
			'**/*.php',
		],
	});
}

/**
 * WordPress translation
 */
mix.wpPot({
	output: packageData.wpPot.output,
	file: packageData.wpPot.file,
	skipJS: true,
	domain: packageData.wpPot.domain,
});

/**
 * CSS Files
 */
mix.sass('assets/scss/app.scss', 'assets/css/app.css').tailwind();

/**
 * JavaScript Files
 */
mix.js('assets/src/app.js', 'assets/js/app.js');
mix.js('public/assets/js/advanced.js', 'public/assets/js/advanced.min.js');
mix.js('public/assets/js/ready.js', 'public/assets/js/ready.min.js');
mix.js('assets/src/admin/page-quick-edit.js', 'assets/js/admin/page-quick-edit.js');
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
