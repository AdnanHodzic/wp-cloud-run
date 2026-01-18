import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { __ } from "@wordpress/i18n";

const Siteground = () => {
  const controls = [
    {
      id: 'cf_siteground_purge_on_flush',
      type: 'toggle',
      label: __('Automatically purge the Siteground cache when Cloudflare cache is purged', 'wp-cloudflare-page-cache'),
    }
  ];

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('Siteground SuperCacher settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <ControlsGroup controls={controls} />
      </CardContent>
    </Card>
  );
};

export default Siteground;