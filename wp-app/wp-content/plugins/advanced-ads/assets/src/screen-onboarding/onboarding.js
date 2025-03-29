/**
 * WordPress Dependencies
 */
import domReady from '@wordpress/dom-ready';
import { createRoot, createElement, render } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import App from './App';

/**
 * Init
 */
domReady(() => {
	// Render App.
	const domElement = document.getElementById('advads-onboarding-wizard');
	const uiElement = createElement(App);

	if (createRoot) {
		createRoot(domElement).render(uiElement);
	} else {
		render(uiElement, domElement);
	}
});
