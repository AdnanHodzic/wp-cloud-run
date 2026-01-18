import Card, { CardContent, CardHeader } from "@/components/Card";
import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import { __ } from "@wordpress/i18n";

const Autoptimize = () => {
  const controls = [
    {
      id: 'cf_autoptimize_purge_on_cache_flush',
      type: 'toggle',
      label: __('Automatically purge the cache when Autoptimize flushs its cache', 'wp-cloudflare-page-cache'),
    }
  ];

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('Autoptimize settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <ControlsGroup controls={controls} />
      </CardContent>
    </Card>
  );
};

export default Autoptimize;