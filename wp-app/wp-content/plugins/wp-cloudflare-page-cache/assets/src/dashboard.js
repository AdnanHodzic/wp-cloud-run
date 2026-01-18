import { createRoot } from '@wordpress/element';

import './dashboard.css';
 
import App from './App.tsx';

const announcementBanner = document.querySelector('.themeisle-sale.notice[data-event-slug="black_friday"]');

if ( announcementBanner ) {
  window.SPCBlackFridayBanner = announcementBanner.innerHTML;
  announcementBanner.remove();
}

createRoot(document.getElementById('spc-dashboard')).render(<App />);
