import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import Button from "@/components/Button";
import Card, { CardContent, CardFooter, CardHeader } from "@/components/Card";
import { spcApi } from "@/lib/api";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { toast } from "sonner";

const AdvancedVarnish = () => {
  const { isToggleOn } = useSettingsStore();

  const controls = [
    {
      id: 'cf_varnish_support',
      type: 'toggle',
      label: __('Varnish Support', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'cf_varnish_hostname',
      type: 'text',
      label: __('Hostname', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_varnish_support'),
    },
    {
      id: 'cf_varnish_port',
      type: 'number',
      label: __('Port', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_varnish_support'),
      min: 0,
      max: 65535,
    },
    {
      id: 'cf_varnish_purge_method',
      type: 'text',
      label: __('HTTP method for single page cache purge', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_varnish_support'),
    },
    {
      id: 'cf_varnish_purge_all_method',
      type: 'text',
      label: __('HTTP method for whole page cache purge', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_varnish_support'),
    },
    {
      id: 'cf_varnish_cw',
      type: 'text',
      label: __('Cloudways Varnish', 'wp-cloudflare-page-cache'),
      description: __('Enable this option if you are using Varnish on Cloudways.', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_varnish_support'),
    },
    {
      id: 'cf_varnish_auto_purge',
      type: 'toggle',
      label: __('Automatically purge Varnish cache when the cache is purged.', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_varnish_support'),
    },
  ]

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('Varnish Settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <ControlsGroup controls={controls} />
      </CardContent>

      {isToggleOn('cf_varnish_support') && (
        <CardFooter className="flex justify-end">
          <PurgeVarnishCacheButton />
        </CardFooter>
      )}
    </Card>
  );
}

const PurgeVarnishCacheButton = () => {
  const { i18n } = window.SPCDash;
  const { asyncLocked, lockAsync } = useAppStore();
  const [purging, setPurging] = useState(false);

  const handlePurge = async () => {
    lockAsync(true);
    setPurging(true);

    const response = await spcApi.purgeCacheVarnish();

    lockAsync(false);
    setPurging(false);

    if (!response.success) {
      toast.error(response.message || i18n.genericError);
      return;
    }

    toast.success(response.message);
  };

  return (
    <Button
      variant="destructive"
      onClick={handlePurge}
      loader={purging}
      disabled={purging || asyncLocked}
      className="flex items-center gap-2"
    >
      {purging ?
        __('Purging Varnish Cache', 'wp-cloudflare-page-cache') + '...' :
        __('Purge Varnish Cache', 'wp-cloudflare-page-cache')}
    </Button>
  );
}

export default AdvancedVarnish;
