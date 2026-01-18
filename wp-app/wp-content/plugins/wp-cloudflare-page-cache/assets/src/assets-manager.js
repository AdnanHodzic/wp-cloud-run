import { createRoot } from '@wordpress/element';

import './asset-manager.css';
import 'sonner/dist/styles.css';
 
import AssetsManagerApp from './AssetsManagerApp.tsx';

// Create shadow DOM container.
const shadowHost = document.createElement('div');
document.body.appendChild(shadowHost);
const shadowRoot = shadowHost.attachShadow({ mode: 'open' });


// Append CSS to shadow root.
const linkElement = document.createElement('link');
linkElement.rel = 'stylesheet';
linkElement.href = window.SPCAssetManager.cssURL;
shadowRoot.appendChild(linkElement);

const root = document.createElement('div');
shadowRoot.appendChild(root);

// Mount the app.
createRoot(root).render(<AssetsManagerApp />);
