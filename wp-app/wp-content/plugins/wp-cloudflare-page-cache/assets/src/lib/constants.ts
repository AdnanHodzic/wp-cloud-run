import { __ } from "@wordpress/i18n";
import { CodeIcon, CogIcon, DatabaseZapIcon, GlobeIcon, ImageIcon, PlugIcon, DatabaseIcon } from "lucide-react";

const NAV_ITEMS_IDS = {
  GENERAL: 'general',
  ADVANCED: 'advanced',
  CLOUDFLARE: 'cloudflare',
  ASSETS: 'assets',
  MEDIA: 'media',
  COMPATIBILITIES: 'compatibilities',
  IMAGE_OPTIMIZATION: 'image-optimization',
  DATABASE_OPTIMIZATION: 'database-optimization',
}

const SETTINGS_NAV_MENU_ITEMS = [
  {
    id: NAV_ITEMS_IDS.GENERAL,
    label: __('General', 'wp-cloudflare-page-cache'),
    description: __('Core Configuration', 'wp-cloudflare-page-cache'),
    icon: DatabaseIcon
  },
  {
    id: NAV_ITEMS_IDS.ADVANCED,
    label: __('Advanced', 'wp-cloudflare-page-cache'),
    description: __('Fine-tuning & Debug', 'wp-cloudflare-page-cache'),
    icon: CogIcon
  },
  {
    id: NAV_ITEMS_IDS.CLOUDFLARE,
    label: __('Cloudflare', 'wp-cloudflare-page-cache'),
    description: __('CDN & Edge Caching', 'wp-cloudflare-page-cache'),
    icon: GlobeIcon
  },
  {
    id: NAV_ITEMS_IDS.ASSETS,
    label: __('Files', 'wp-cloudflare-page-cache'),
    description: __('JS, CSS & Fonts', 'wp-cloudflare-page-cache'),
    icon: CodeIcon
  },
  {
    id: NAV_ITEMS_IDS.MEDIA,
    label: __('Media', 'wp-cloudflare-page-cache'),
    description: __('Image & Lazy Load', 'wp-cloudflare-page-cache'),
    icon: ImageIcon
  },
  {
    id: NAV_ITEMS_IDS.COMPATIBILITIES,
    label: __('Compatibilities', 'wp-cloudflare-page-cache'),
    description: __('3rd Party Integrations', 'wp-cloudflare-page-cache'),
    icon: PlugIcon
  },
  {
    id: NAV_ITEMS_IDS.DATABASE_OPTIMIZATION,
    label: __('Database', 'wp-cloudflare-page-cache'),
    description: __('Clean & Optimize', 'wp-cloudflare-page-cache'),
    icon: DatabaseZapIcon
  }
];

const CF_AUTH_MODES = {
  API_KEY: 0,
  API_TOKEN: 1,
};

const ROOT_PAGES = {
  DASHBOARD: 'dashboard',
  SETTINGS: 'settings',
  IMPORT_EXPORT: 'import-export',
  LICENSE: 'license',
  HELP: 'help',
};

const LICENSE_ACTIONS = {
  ACTIVATE: 'activate',
  DEACTIVATE: 'deactivate',
}; 

const LINKS = {
  STORE: 'https://store.themeisle.com',
  SUGGEST_FEATURE: 'https://store.themeisle.com/suggest-a-feature',
  DOCS: 'https://docs.themeisle.com/collection/2199-super-page-cache',
  WPORG_FORUM: 'https://wordpress.org/support/plugin/wp-cloudflare-page-cache',
  YOUTUBE_PLAYLIST: 'https://youtube.com/playlist?list=PLmRasCVwuvpSJuwaV7kDiuXhxl08zbZr5&si=jeVnA7W6rNnGm7vL',
  DIRECT_SUPPORT: window.SPCDash?.directSupportURL,
  MAIN_PAGE: window.SPCDash?.mainPageURL,
  CF_SIGNUP: 'http://rviv.ly/spccfaccountsignup',
  UPSELL: window.SPCDash?.upsellURL,
  PLUGINS_PAGE: window.SPCDash?.pluginsPageURL
}

export { CF_AUTH_MODES, LICENSE_ACTIONS, LINKS, NAV_ITEMS_IDS, ROOT_PAGES, SETTINGS_NAV_MENU_ITEMS };
