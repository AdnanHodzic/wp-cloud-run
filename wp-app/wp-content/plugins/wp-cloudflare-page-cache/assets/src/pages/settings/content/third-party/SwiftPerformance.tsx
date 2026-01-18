import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { __ } from "@wordpress/i18n";
import CacheCompatibilityNotice from "./CacheCompatibilityNotice";

const SwiftPerformance = () => {
  const controls = [
    {
      id: 'swift_performance_purge',
      type: 'checkbox-group',
      label: __('Automatically purge the cache when', 'wp-cloudflare-page-cache'),
      stack: true,
      controls: [{
        id: 'cf_spl_purge_on_flush_all',
        type: 'checkbox',
        label: __('Swift Performance (Lite/Pro) flushs all caches', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_spl_purge_on_flush_single_post',
        type: 'checkbox',
        label: __('Swift Performance (Lite/Pro) flushs single post cache', 'wp-cloudflare-page-cache'),
        recommended: true,
      }]
    }
  ];

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('Swift Performance (Lite/Pro) settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <CacheCompatibilityNotice className="p-4 pb-0" />
        <ControlsGroup controls={controls} />
      </CardContent>
    </Card>
  );
};

export default SwiftPerformance;