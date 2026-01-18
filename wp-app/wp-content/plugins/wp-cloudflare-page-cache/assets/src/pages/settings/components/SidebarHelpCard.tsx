import Button from "@/components/Button";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { LINKS, NAV_ITEMS_IDS, ROOT_PAGES } from "@/lib/constants";
import { useAppStore } from "@/store/store";
import { __ } from "@wordpress/i18n";
import { CircleHelpIcon } from "lucide-react";



const SidebarHelpCard = () => {
  const { setRootPage, activeMenuItem, validPro } = useAppStore();

  return (
    <Card>
      <CardHeader>
        <h3 className="font-semibold text-base flex items-center">
          <CircleHelpIcon className="size-4 mr-2 text-orange-600 dark:text-orange-500" />
          {__('Need Help?', 'wp-cloudflare-page-cache')}
        </h3>
      </CardHeader>
      <CardContent className="p-4 space-y-3">


        {activeMenuItem !== NAV_ITEMS_IDS.CLOUDFLARE && (<>
          <button onClick={() => setRootPage(ROOT_PAGES.HELP)} className="w-full text-left block group">
            <h4 className="text-sm font-medium mb-1 group-hover:underline">{__('Help Center', 'wp-cloudflare-page-cache')}</h4>
            <p className="text-xs text-muted-foreground">{__('Popular articles and step-by-step guides', 'wp-cloudflare-page-cache')}</p>
          </button>

          <a
            className="block group"
            href={LINKS.DOCS}
            target="_blank"
            rel="noreferrer"
          >
            <h4 className="text-sm font-medium mb-1 group-hover:underline">{__('Documentation', 'wp-cloudflare-page-cache')}</h4>
            <p className="text-xs text-muted-foreground">{__('Complete technical documentation', 'wp-cloudflare-page-cache')}</p>
          </a>
          <a
            href={validPro ? LINKS.DIRECT_SUPPORT : LINKS.WPORG_FORUM}
            target="_blank"
            className="block group"
            rel="noreferrer"
          >
            <h4 className="text-sm font-medium mb-1 group-hover:underline">{__('Support', 'wp-cloudflare-page-cache')}</h4>
            {validPro && <p className="text-xs text-muted-foreground">{__('Get direct help from our team', 'wp-cloudflare-page-cache')}</p>}
            {!validPro && <p className="text-xs text-muted-foreground">{__('Official plugin support forum', 'wp-cloudflare-page-cache')}</p>}
          </a>
        </>
        )}

        {activeMenuItem === NAV_ITEMS_IDS.CLOUDFLARE && (
          <div className="space-y-6">
            <a href="https://docs.themeisle.com/article/1481-how-to-setup-wp-cloudflare-super-page-cache" target="_blank" rel="noreferrer" className="block group text-sm font-medium text-foreground">
              {__('Cloudflare Setup Guide', 'wp-cloudflare-page-cache')}
            </a>

            <a href="https://docs.themeisle.com/article/2077-super-page-cache-cloudflare-permissions" target="_blank" rel="noreferrer" className="block group text-sm font-medium text-foreground">
              {__('API token Creation', 'wp-cloudflare-page-cache')}
            </a>

            <a href="https://docs.themeisle.com/article/1484-common-issues-for-wp-cloudflare-super-page-cache" target="_blank" rel="noreferrer" className="block group text-sm font-medium text-foreground">
              {__('Troubleshooting connection issues', 'wp-cloudflare-page-cache')}
            </a>
          </div>
        )}

        <Button onClick={() => setRootPage(ROOT_PAGES.HELP)} variant="outline" className="w-full text-orange-600 hover:text-orange-600 border-orange-200 dark:border-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors">Open Help Center</Button>

      </CardContent>
    </Card>
  )
}

export default SidebarHelpCard;