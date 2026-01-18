import Card, { CardContent, CardHeader } from "@/components/Card";
import { __ } from "@wordpress/i18n";
import { Settings } from "lucide-react";

const DetailsCard = () => {
  return <Card>
    <CardHeader className="bg-muted">
      <div className="flex items-center">
        <Settings className="size-5 mr-2 text-muted-foreground" />
        <h3 className="font-semibold text-base flex items-center m-0">
          {__('Import/Export Information', 'wp-cloudflare-page-cache')}
        </h3>
      </div>
    </CardHeader>

    <CardContent className="grid md:grid-cols-2 gap-4">
      <div>
        <h4 className="text-md font-semibold mb-2">{__('What\'s exported:', 'wp-cloudflare-page-cache')}</h4>
        <ul className="text-sm text-muted-foreground space-y-1 list-disc list-inside">
          <li>
            {__('Cache configuration settings', 'wp-cloudflare-page-cache')}
          </li>
          <li>
            {__('Advanced performance options', 'wp-cloudflare-page-cache')}
          </li>
          <li>
            {__('Plugin integration settings', 'wp-cloudflare-page-cache')}
          </li>
          <li>
            {__('CDN and optimization preferences', 'wp-cloudflare-page-cache')}
          </li>
          <li>
            {__('Exclusion rules and bypass settings', 'wp-cloudflare-page-cache')}
          </li>
        </ul>
      </div>
      <div>
        <h4 className="text-md font-semibold mb-2">{__('What\'s NOT exported:', 'wp-cloudflare-page-cache')}</h4>
        <ul className="text-sm text-muted-foreground space-y-1 list-disc list-inside">
          <li>
            {__('License keys and authentication', 'wp-cloudflare-page-cache')}
          </li>
          <li>
            {__('Cached files and data', 'wp-cloudflare-page-cache')}
          </li>
          <li>
            {__('Site-specific URLs or paths', 'wp-cloudflare-page-cache')}
          </li>
          <li>
            {__('Performance statistics', 'wp-cloudflare-page-cache')}
          </li>
          <li>
            {__('Log files and debug information', 'wp-cloudflare-page-cache')}
          </li>
        </ul>
      </div>
    </CardContent>
  </Card>;
};

export default DetailsCard;