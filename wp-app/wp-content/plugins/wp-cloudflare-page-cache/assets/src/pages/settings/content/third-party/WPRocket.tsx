import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { __ } from "@wordpress/i18n";
import CacheCompatibilityNotice from "./CacheCompatibilityNotice";

const WPRocket = () => {
  const controls = [
    {
      id: 'wp_rocket_purge',
      type: 'checkbox-group',
      label: __('Automatically purge the cache when', 'wp-cloudflare-page-cache'),
      controls: [{
        id: 'cf_wp_rocket_purge_on_domain_flush',
        type: 'checkbox',
        label: __('WP Rocket flushs all caches', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_wp_rocket_purge_on_post_flush',
        type: 'checkbox',
        label: __('WP Rocket flushs single post cache', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_wp_rocket_purge_on_cache_dir_flush',
        type: 'checkbox',
        label: __('WP Rocket flushs cache directories', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_wp_rocket_purge_on_clean_files',
        type: 'checkbox',
        label: __('WP Rocket flushs files', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_wp_rocket_purge_on_clean_cache_busting',
        type: 'checkbox',
        label: __('WP Rocket flushs cache busting', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_wp_rocket_purge_on_clean_minify',
        type: 'checkbox',
        label: __('WP Rocket flushs minified files', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_wp_rocket_purge_on_ccss_generation_complete',
        type: 'checkbox',
        label: __('CCSS generation process ends', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_wp_rocket_purge_on_rucss_job_complete',
        type: 'checkbox',
        label: __('RUCSS generation process ends', 'wp-cloudflare-page-cache'),
        recommended: true,
      }]
    },
    {
      id: 'cf_wp_rocket_disable_cache',
      type: 'toggle',
      label: __('Disable WP Rocket page cache', 'wp-cloudflare-page-cache'),
    }
  ];

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('WP Rocket settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <CacheCompatibilityNotice className="p-4 pb-0" />
        <ControlsGroup controls={controls} />
      </CardContent>
    </Card>
  );
};

export default WPRocket;