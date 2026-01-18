import Button from "@/components/Button";
import Card, { CardContent, CardFooter, CardHeader } from "@/components/Card";
import Notice from "@/components/Notice";
import { useAppStore } from "@/store/store";

import { __ } from "@wordpress/i18n";
import { Download, Shield } from "lucide-react";

const ExportCard = () => {
  const { asyncLocked } = useAppStore();

  return (
    <Card className="flex flex-col">
      <CardHeader className="p-6 flex items-center border-b bg-green-50 border-green-200 dark:bg-green-700/10 dark:border-green-800/50">
        <div className="size-10 rounded-lg flex items-center justify-center mr-4 bg-green-100 dark:bg-green-900/50 border border-green-200 dark:border-green-800/50">
          <Download className="size-5 text-green-600" />
        </div>

        <div>
          <h3 className="text-base font-semibold text-foreground">
            {__('Export Settings', 'wp-cloudflare-page-cache')}
          </h3>
          <p className="text-sm text-muted-foreground">
            {__('Download your current configuration', 'wp-cloudflare-page-cache')}
          </p>
        </div>
      </CardHeader>

      <CardContent className="space-y-4 h-full flex flex-col">
        <p className="text-sm">
          {__('Download your current cache settings as a JSON file for backup or migration to another site.', 'wp-cloudflare-page-cache')}
        </p>

        <Notice
          className="mt-auto"
          icon={Shield}
          type="info"
        >
          <strong>{__('Secure export', 'wp-cloudflare-page-cache')}:</strong>
          {' '}
          {__('All cache settings and configurations will be included, excluding sensitive data like license keys and Cloudflare connection details.', 'wp-cloudflare-page-cache')}
        </Notice>

      </CardContent>

      <CardFooter className="mt-auto">
        <Button
          icon={Download}
          variant="green"
          className="w-full mt-auto"
          href={window.SPCDash.configExportURL}
          disabled={asyncLocked}
        >
          {__('Download Settings File', 'wp-cloudflare-page-cache')}
        </Button>
      </CardFooter>
    </Card>
  );
};

export default ExportCard;