import { Crown, ExternalLink, MessageCircle } from "lucide-react";
import { __ } from "@wordpress/i18n";
import { cn, getUpsellURL } from "@/lib/utils";
import { useAppStore } from "@/store/store";
import Button from "@/components/Button";
import { LINKS } from "@/lib/constants";

const GetHelp = () => {
  const { validPro } = useAppStore();

  const wrapClasses = {
    'from-green-900/10 to-blue-900/10 border-green-200 dark:border-green-900/20': !validPro,
    'from-orange-900/10 to-amber-900/10 border-orange-200 dark:border-orange-900/20': validPro,
  }

  const Icon = validPro ? MessageCircle : ExternalLink;

  return (
    <div className={cn("mt-12 bg-gradient-to-r rounded-lg border p-8 text-center", wrapClasses)}>
      <Icon className={cn("w-12 h-12 mx-auto mb-4", {
        'text-green-600': !validPro,
        'text-orange-600': validPro,
      })} />

      <h3 className="text-lg font-semibold mb-2">
        {validPro ?
          __('Still need help?', 'wp-cloudflare-page-cache') :
          __('Get Help on WordPress.org', 'wp-cloudflare-page-cache')}
      </h3>

      <p className="text-muted-foreground mb-6">
        {validPro ?
          __('Our support team is ready to assist you with any questions', 'wp-cloudflare-page-cache') :
          __('Join the official Super Page Cache support forum on WordPress.org. Connect with users and get community support.', 'wp-cloudflare-page-cache')}
      </p>

      {!validPro && (
        <>
          <div className="grid md:flex items-center justify-center gap-4 mb-6">
            <Button variant="green" size="lg" href={LINKS.WPORG_FORUM} target="_blank">
              {__('Visit Support Forum', 'wp-cloudflare-page-cache')}
            </Button>
            <div className="text-sm text-muted-foreground">
              {__('Free', 'wp-cloudflare-page-cache')}
              {' '}•{' '}
              {__('Community-powered support', 'wp-cloudflare-page-cache')}
            </div>
          </div>

          <div className="mt-6 p-4 bg-card rounded-lg border">
            <div className="flex items-center justify-center">
              <Crown className="w-4 h-4 text-orange-600 mr-2" />
              <span className="text-sm text-muted-foreground">
                {__('Need direct email support?', 'wp-cloudflare-page-cache')}
                <a target="_blank" href={getUpsellURL('help-support')} className="text-orange-600 hover:text-orange-700 font-medium p-0 ml-1" rel="noreferrer">
                  {__('Upgrade to Pro', 'wp-cloudflare-page-cache')}
                </a>
              </span>
            </div>
          </div>
        </>
      )}

      {validPro && (
        <div className="grid items-center justify-center gap-4">
          <Button variant="orange" size="lg" href={LINKS.DIRECT_SUPPORT} target="_blank">
            {__('Contact Support Team', 'wp-cloudflare-page-cache')}
          </Button>
          <div className="text-sm text-muted-foreground">
            {__('Average response time: 2-4 hours', 'wp-cloudflare-page-cache')}
          </div>
        </div>
      )}
    </div >
  )
}

export default GetHelp; 