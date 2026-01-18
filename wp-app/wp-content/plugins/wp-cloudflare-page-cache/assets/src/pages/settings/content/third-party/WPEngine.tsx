import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { __ } from "@wordpress/i18n";

const WPEngine = () => {
  const controls = [
    {
      id: 'cf_wpengine_purge_on_flush',
      type: 'toggle',
      label: __('Automatically purge the WP Engine cache when Cloudflare cache is purged', 'wp-cloudflare-page-cache'),
    }
  ];

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('WP Engine settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <ControlsGroup controls={controls} />
      </CardContent>
    </Card>
  );
};

export default WPEngine;