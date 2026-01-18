import Button from "@/components/Button";
import Card, { CardContent } from "@/components/Card";
import { getUpsellURL } from "@/lib/utils";
import { __ } from "@wordpress/i18n";
import { CrownIcon } from "lucide-react";


const SidebarUpsellCard = () => {

  const upsellData = [
    {
      title: __('JavaScript Optimization', 'wp-cloudflare-page-cache'),
      description: __('Delay and defer JS files for better performance', 'wp-cloudflare-page-cache'),
    },
    {
      title: __('Marketing Parameter Ignoring', 'wp-cloudflare-page-cache'),
      description: __('Improve cache hit rates significantly', 'wp-cloudflare-page-cache'),
    },
    {
      title: __('Viewport-based Lazy Loading', 'wp-cloudflare-page-cache'),
      description: __('Smart lazy loading based on viewport detection', 'wp-cloudflare-page-cache'),
    },
    {
      title: __('Priority Email Support', 'wp-cloudflare-page-cache'),
      description: __('Get dedicated help from our team', 'wp-cloudflare-page-cache'),
    },
    {
      title: __('Support the plugin you love', 'wp-cloudflare-page-cache'),
      description: __('Help us continue developing amazing features', 'wp-cloudflare-page-cache'),
    },
  ]

  return (
    <Card className="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-lg border border-orange-200 dark:border-orange-700/50">
      <CardContent className="p-5">

        <div className="flex items-center mb-4">
          <div className="w-8 h-8 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg flex items-center justify-center mr-3">
            <CrownIcon className="size-4 text-white" />
          </div>
          <div>
            <h3 className="text-lg font-semibold text-orange-900 dark:text-orange-200">{__('Super Page Cache Pro', 'wp-cloudflare-page-cache')}</h3>
            <p className="text-xs text-orange-700 dark:text-orange-300">{__('Unlock advanced features', 'wp-cloudflare-page-cache')}</p>
          </div>
        </div>

        <div className="space-y-3 mb-5">
          {upsellData.map((item, index) => (
            <div key={index} className="flex items-start">
              <div className="w-4 h-4 bg-orange-500 rounded-full flex items-center justify-center mr-3 mt-0.5">
                <div className="w-1.5 h-1.5 bg-white rounded-full">
                </div>
              </div>

              <div>
                <div className="text-sm font-medium text-orange-900 dark:text-orange-200">{item.title}</div>
                <div className="text-xs text-orange-700 dark:text-orange-300">{item.description}</div>
              </div>
            </div>
          ))}
        </div>


        <Button href={getUpsellURL('settings-sidebar-upsell')} variant="upsell" className="w-full">
          {__('Upgrade to Pro', 'wp-cloudflare-page-cache')}
        </Button>
        <p className="text-xs text-orange-600 dark:text-orange-400 text-center mt-3">
          {__('30-day money-back guarantee', 'wp-cloudflare-page-cache')}
        </p>

      </CardContent>
    </Card>
  )
}

export default SidebarUpsellCard;