/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		'./includes/**/*.php',
		'./views/**/*.php',
		'./modules/one-click/modules/adstxt/class-detector.php',
	],
	theme: {
		extend: {
			colors: {
				wordpress: '#2271b1',
				primary: '#0074a2',
			},
			borderColor: {
				advads: '#b5bfc9',
			},
		},
	},
};
