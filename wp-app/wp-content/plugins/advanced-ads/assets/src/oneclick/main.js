/**
 * WordPress Dependencies
 */
import domReady from '@wordpress/dom-ready';
import { createRoot, createElement, render } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import App from './App';

if (!String.format) {
	String.format = function (format) {
		const args = Array.prototype.slice.call(arguments, 1);
		return format.replace(/{(\d+)}/g, function (match, number) {
			return typeof args[number] !== 'undefined' ? args[number] : match;
		});
	};
}

/**
 * Init
 */
domReady(() => {
	const domNode = document.getElementById('advads-oneclick-app');
	if (!domNode) {
		return;
	}
	// add a div at the end of the element with id=advanced-ads-addon-box
	const div = document.createElement('div');
	div.id = 'advads-oneclick-addon-row';
	document.getElementById('advanced-ads-addon-box').appendChild(div);

	const uiElement = createElement(App);

	if (createRoot) {
		createRoot(domNode).render(uiElement);
	} else {
		render(uiElement, domNode);
	}
});
