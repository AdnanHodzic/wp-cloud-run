import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { __ } from "@wordpress/i18n";
import CacheCompatibilityNotice from "./CacheCompatibilityNotice";

const FlyingPress = () => {
  const controls = [
    {
      id: 'cf_flypress_purge_on_cache_flush',
      type: 'toggle',
      label: __('Automatically purge the cache when Flying Press flushs its own cache', 'wp-cloudflare-page-cache'),
    }
  ];

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('Flying Press settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <CacheCompatibilityNotice className="p-4 pb-0" />
        <ControlsGroup controls={controls} />
      </CardContent>
    </Card>
  );
};

export default FlyingPress;