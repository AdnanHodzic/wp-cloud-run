import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { __ } from "@wordpress/i18n";
import CacheCompatibilityNotice from "./CacheCompatibilityNotice";

const LitespeedCache = () => {
  const controls = [
    {
      id: 'litespeed_purge',
      type: 'checkbox-group',
      label: __('Automatically purge the cache when', 'wp-cloudflare-page-cache'),
      controls: [{
        id: 'cf_litespeed_purge_on_cache_flush',
        type: 'checkbox',
        label: __('LiteSpeed Cache flushs all caches', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_litespeed_purge_on_ccss_flush',
        type: 'checkbox',
        label: __('LiteSpeed Cache flushs Critical CSS', 'wp-cloudflare-page-cache'),
      },
      {
        id: 'cf_litespeed_purge_on_cssjs_flush',
        type: 'checkbox',
        label: __('LiteSpeed Cache flushs CSS and JS cache', 'wp-cloudflare-page-cache'),
      },
      {
        id: 'cf_litespeed_purge_on_object_cache_flush',
        type: 'checkbox',
        label: __('LiteSpeed Cache flushs object cache', 'wp-cloudflare-page-cache'),
      },
      {
        id: 'cf_litespeed_purge_on_single_post_flush',
        type: 'checkbox',
        label: __('LiteSpeed Cache flushs single post cache via API', 'wp-cloudflare-page-cache'),
      }]
    }
  ];

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('LiteSpeed Cache settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <CacheCompatibilityNotice className="p-4 pb-0" />
        <ControlsGroup controls={controls} />
      </CardContent>
    </Card>
  );
};

export default LitespeedCache;