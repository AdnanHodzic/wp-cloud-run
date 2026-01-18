import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { __ } from "@wordpress/i18n";
import CacheCompatibilityNotice from "./CacheCompatibilityNotice";

const W3TC = () => {
  const controls = [
    {
      id: 'w3tc_purge',
      type: 'checkbox-group',
      label: __('Automatically purge the cache when', 'wp-cloudflare-page-cache'),
      controls: [{
        id: 'cf_w3tc_purge_on_flush_all',
        type: 'checkbox',
        label: __('W3TC flushs all caches', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_w3tc_purge_on_flush_dbcache',
        type: 'checkbox',
        label: __('W3TC flushs database cache', 'wp-cloudflare-page-cache'),
      },
      {
        id: 'cf_w3tc_purge_on_flush_fragmentcache',
        type: 'checkbox',
        label: __('W3TC flushs fragment cache', 'wp-cloudflare-page-cache'),
      },
      {
        id: 'cf_w3tc_purge_on_flush_objectcache',
        type: 'checkbox',
        label: __('W3TC flushs object cache', 'wp-cloudflare-page-cache'),
      },
      {
        id: 'cf_w3tc_purge_on_flush_posts',
        type: 'checkbox',
        label: __('W3TC flushs posts cache', 'wp-cloudflare-page-cache'),
      },
      {
        id: 'cf_w3tc_purge_on_flush_minfy',
        type: 'checkbox',
        label: __('W3TC flushs minify cache', 'wp-cloudflare-page-cache'),
      }]
    }
  ];

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('W3 Total Cache settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <CacheCompatibilityNotice className="p-4 pb-0" />
        <ControlsGroup controls={controls} />
      </CardContent>
    </Card>
  );
};

export default W3TC;