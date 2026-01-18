import { __ } from "@wordpress/i18n";
import { ROOT_PAGES } from "./constants";

const updateCoreDashboardNav = (page: string) => {
  const { rootPagePrefix } = window.SPCDash;

  const currentURLObject = new URL(window.location.href);
  const currentPage = currentURLObject.searchParams.get('page')?.toLowerCase();
  // trim last - if it's the same as dashboard
  const upcomingPage = page === ROOT_PAGES.DASHBOARD ?
    rootPagePrefix.replace(/-$/, '') :
    (rootPagePrefix + page).toLowerCase();

  if (currentPage === upcomingPage) {
    return;
  }


  currentURLObject.searchParams.set('page', upcomingPage);

  const upcomingURL = currentURLObject.toString();

  updateHistory(upcomingURL);
  updateMenuState(upcomingURL);
  updateHTMLTitle(page);
}

const updateHTMLTitle = (page: string) => {
  const title = document.head.querySelector('title');

  if (!title) {
    return;
  }

  const titleMap = {
    [ROOT_PAGES.DASHBOARD]: __('Dashboard - Super Page Cache', 'wp-cloudflare-page-cache'),
    [ROOT_PAGES.SETTINGS]: __('Settings - Super Page Cache', 'wp-cloudflare-page-cache'),
    [ROOT_PAGES.IMPORT_EXPORT]: __('Import/Export Settings - Super Page Cache', 'wp-cloudflare-page-cache'),
    [ROOT_PAGES.LICENSE]: __('License - Super Page Cache', 'wp-cloudflare-page-cache'),
    [ROOT_PAGES.HELP]: __('Help - Super Page Cache', 'wp-cloudflare-page-cache'),
  }

  title.textContent = titleMap[page] || titleMap[ROOT_PAGES.DASHBOARD];
}

const updateHistory = (href: string) => {
  const url = new URL(href, window.location.href);
  window.history.pushState({}, '', url.pathname + url.search);
}

const updateMenuState = (href: string) => {
  const menuItems = document.querySelectorAll('.toplevel_page_super-page-cache a');
  const currentUrl = new URL(href, window.location.href);

  menuItems.forEach(link => {
    const linkUrl = new URL(link.getAttribute('href') || '', window.location.href);
    const isCurrent = linkUrl.searchParams.get('page') === currentUrl.searchParams.get('page');

    link.classList.toggle('current', isCurrent);
    link.parentElement?.classList.toggle('current', isCurrent);
  });
}




export { updateCoreDashboardNav };