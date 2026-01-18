import Button from "@/components/Button";
import Card, { CardContent } from "@/components/Card";
import { getUpsellURL } from "@/lib/utils";
import { __ } from "@wordpress/i18n";
import { CheckCircle, Crown } from "lucide-react";

const SidebarUpsellCard = () => {

  const features = [
    __('Defer & Delay Javascript', 'wp-cloudflare-page-cache'),
    __('Improve cache hit rates', 'wp-cloudflare-page-cache'),
    __('Priority Support', 'wp-cloudflare-page-cache'),
    __('Support a plugin you love', 'wp-cloudflare-page-cache'),
  ];

  return (
    <Card className="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-lg border border-orange-200 dark:border-orange-700/50">
      <CardContent className="p-4">


        <div className="flex items-center justify-between mb-3">

          <div className="flex items-center">
            <div className="size-5 bg-gradient-to-br from-orange-500 to-red-500 rounded flex items-center justify-center mr-2">
              <Crown className="size-3 text-white" />
            </div>
            <span className="text-sm font-semibold text-orange-900 dark:text-orange-200">
              {__('Super Page Cache Pro', 'wp-cloudflare-page-cache')}
            </span>
          </div>
        </div>

        <p className="text-xs text-orange-700 dark:text-orange-300 mb-3">
          {__('Advanced features for maximum performance', 'wp-cloudflare-page-cache')}
        </p>

        <div className="space-y-1.5 mb-4">
          {features.map((feature, index) => (
            <div key={index} className="flex items-center text-xs text-orange-700 dark:text-orange-300">
              <CheckCircle className="size-3 text-orange-500 mr-2" />
              {feature}
            </div>
          ))}
        </div>

        <Button
          variant="upsell"
          href={getUpsellURL('dashboard-sidebar-upsell')}
          className="w-full">
          {__('Upgrade to Pro', 'wp-cloudflare-page-cache')}
        </Button>
      </CardContent>
    </Card>
  )
}

export default SidebarUpsellCard;