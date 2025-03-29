/** @type {import('tailwindcss').Config} */
export default {
	content: [
		'./assets/src/components/**/*.js',
		'./assets/src/components/**/*.jsx',
		'./assets/src/screen-onboarding/**/*.js',
		'./assets/src/screen-onboarding/**/*.jsx',
	],
	theme: {
		extend: {
			colors: {
				wordpress: '#2271b1',
				primary: '#0474a2',
			},
		},
	},
	plugins: [],
};
