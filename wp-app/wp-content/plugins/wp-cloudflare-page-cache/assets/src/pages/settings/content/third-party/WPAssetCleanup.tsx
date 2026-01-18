import Card, { CardContent, CardHeader } from "@/components/Card";
import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import { __ } from "@wordpress/i18n";

const WPAssetCleanup = () => {
  const controls = [
    {
      id: 'cf_wpacu_purge_on_cache_flush',
      type: 'toggle',
      label: __('Automatically purge the cache when WP Asset Clean Up flushs its own cache', 'wp-cloudflare-page-cache'),
    }
  ];

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('WP Asset Clean Up settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <ControlsGroup controls={controls} />
      </CardContent>
    </Card>
  );
};

export default WPAssetCleanup;