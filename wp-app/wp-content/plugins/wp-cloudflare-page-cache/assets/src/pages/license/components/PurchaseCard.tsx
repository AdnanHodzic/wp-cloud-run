import Button from "@/components/Button";
import { getUpsellURL } from "@/lib/utils";
import { __ } from "@wordpress/i18n";

const PurchaseCard = () => {
  return (
    <div className="rounded-lg border bg-gradient-to-br from-orange-50 dark:from-orange-600/20 to-red-50 dark:to-red-600/20 border-orange-200 dark:border-orange-700/30">

      <div className="p-6">
        <h3 className="font-semibold mb-2 text-base text-orange-900 dark:text-orange-200">
          {__('Don\'t have a license?', 'wp-cloudflare-page-cache')}
        </h3>
        <p className="text-sm mb-4 text-orange-800 dark:text-orange-300">
          {__('Get Super Page Cache Pro and unlock all advanced features including database caching, CDN integration, real-time analytics, and priority support.', 'wp-cloudflare-page-cache')}
        </p>
        <div className="flex justify-center">
          <Button
            target="_blank"
            variant="cta"
            className="rounded"
            href={getUpsellURL('license-upsell')}
          >
            {__('Purchase License', 'wp-cloudflare-page-cache')}
          </Button>
        </div>
      </div>
    </div>
  )
}

export default PurchaseCard;