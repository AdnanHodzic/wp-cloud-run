import { __, sprintf } from "@wordpress/i18n";
import { Info } from "lucide-react";

const PageContextInfo = () => {
  const { currentContext } = window.SPCAssetManager;

  const getPageTypeDescription = () => {
    switch (currentContext.pageType) {
    case 'is_singular':
      // translators: %s is the post type label
      return sprintf(__(`Viewing a single %s`, 'wp-cloudflare-page-cache'), currentContext.postTypeLabel.toLowerCase());
    case 'is_archive':
      if (currentContext.subType === 'is_tax') { 
        return /* translators: %1$s is the taxonomy label, %2$s is the taxonomy slug */ sprintf(__(`Viewing %1$s archive: %2$s`, 'wp-cloudflare-page-cache'), currentContext.taxonomyLabel.toLowerCase() , currentContext.taxonomySlug);
      }
      if (currentContext.subType === 'is_author') {
        return __('Viewing author archive', 'wp-cloudflare-page-cache');
      }
      if (currentContext.subType === 'is_date') {
        // translators: %s is the date
        return sprintf(__(`Viewing %s archive`, 'wp-cloudflare-page-cache'), currentContext.currentDate);
      }
      return 'Viewing archive page';
    case 'is_search':
      return __('Viewing search results', 'wp-cloudflare-page-cache');
    case 'is_404':
      return __('Viewing 404 error page', 'wp-cloudflare-page-cache');
    case 'is_front_page':
      return __('Viewing front page', 'wp-cloudflare-page-cache');
    case 'is_home':
      return __('Viewing blog homepage', 'wp-cloudflare-page-cache');
    default:
      return __('Unknown page type', 'wp-cloudflare-page-cache');
    }
  };

  const  removeSPCAssetsParam = (url) => {
    return url
      .replace(/([?&])spc_assets=yes(&?)/, (match, p1, p2) => {
        if (p1 === '?' && !p2) return '';
        if (p1 === '?' && p2 === '&') return '?';
        if (p1 === '&') return '';
      }).replace(/[?&]$/, '');
  }

  return (
    <div className="p-3 bg-blue-50 border-b flex items-center gap-5">
      <div className="min-w-0 flex items-center gap-2">
        <Info className="size-4 text-blue-600 shrink-0 min-w-0" />

        <div className="min-w-0 flex flex-col">
          {currentContext.title && <span className="font-medium text-sm text-blue-900 truncate">{currentContext.title}:</span> }
          <span className="text-xs text-blue-700 truncate">{removeSPCAssetsParam(currentContext.url)}</span>
        </div>

      </div>

      <div className="shrink-0 ml-auto flex items-center gap-1 text-sm text-blue-700">
        <span>{__('Page Type', 'wp-cloudflare-page-cache')}:</span>
        <span className="font-medium">{getPageTypeDescription()}</span>
      </div>
    </div>
  )
}

export default PageContextInfo;