import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import NginxLinkInstructions from "@/common/NginxLinkInstructions";
import Notice from "@/components/Notice";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { useSettingsStore } from "@/store/optionsStore";
import { createInterpolateElement } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

const AdvancedCloudflare = () => {
  const { settings, isToggleOn } = useSettingsStore();
  if (!settings['cf_page_rule_id']) {
    // return null;
  }

  const controls = [
    {
      id: 'group-cf-behavior',
      type: 'checkbox-group',
      label: __('Automatically purge the Cloudflare\'s cache when something changes on the website', 'wp-cloudflare-page-cache'),
      stack: true,
      description: (
        <>
          {__('Example: update/publish a post/page', 'wp-cloudflare-page-cache')}
          <br />
          {createInterpolateElement(
            __('It is recommended to add the browser caching rules that you find <button>here</button>.', 'wp-cloudflare-page-cache'),
            {
              button: <NginxLinkInstructions />
            }
          )}
        </>
      ),
      controls: [
        {
          id: 'cf_auto_purge',
          type: 'checkbox',
          label: __('Purge cache for related pages only', 'wp-cloudflare-page-cache'),
          recommended: true
        },
        {
          id: 'cf_auto_purge_all',
          type: 'checkbox',
          label: __('Purge whole cache', 'wp-cloudflare-page-cache'),
        }
      ]
    },
    {
      id: 'cf_fallback_cache_auto_purge',
      type: 'toggle',
      label: __('Automatically purge the Page cache when Cloudflare cache is purged', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_fallback_cache'),
    },
    {
      id: 'cf_bypass_backend_page_rule',
      type: 'toggle',
      label: __('Force cache bypassing for backend with an additional Cloudflare page rule', 'wp-cloudflare-page-cache'),
      description: createInterpolateElement(__('<strong>Read here:</strong> by default, all back-end URLs are not cached thanks to some response headers, but if for some circumstances your backend pages are still cached, you can enable this option which will add an <strong>additional page rule on Cloudflare</strong> to force cache bypassing for the whole WordPress backend directly from Cloudflare.', 'wp-cloudflare-page-cache'),
        {
          strong: <strong />,
        }
      ),
      hide: isToggleOn('cf_woker_enabled'),
    },
  ];

  const lifetimeControls = [
    {
      id: 'cf_maxage',
      type: 'number',
      label: __('Cache-Control max-age', 'wp-cloudflare-page-cache'),
      description: __('Don\'t touch if you don\'t know what is it. Must be greater than zero. Recommended 31536000 (1 year)', 'wp-cloudflare-page-cache'),
      min: 1,
      max: 315360000,
    },
    {
      id: 'cf_browser_maxage',
      type: 'number',
      label: __('Browser Cache-Control max-age', 'wp-cloudflare-page-cache'),
      description: __('Don\'t touch if you don\'t know what is it. Must be greater than zero. Recommended a value between 60 and 600', 'wp-cloudflare-page-cache'),
      min: 1,
      max: 315360000,
    },
  ];

  return (
    <>
      <Card>
        <CardHeader className="bg-muted">
          <h3 className="font-semibold text-base flex items-center">{__('Cache Lifetime Settings', 'wp-cloudflare-page-cache')}</h3>
        </CardHeader>

        <CardContent className="p-0">
          <ControlsGroup controls={lifetimeControls} />
        </CardContent>
      </Card>

      <Card>
        <CardHeader className="bg-muted">
          <h3 className="font-semibold text-base flex items-center">{__('Cloudflare Cache Behavior', 'wp-cloudflare-page-cache')}</h3>
        </CardHeader>

        <CardContent className="p-0">
          <ControlsGroup controls={controls} />
        </CardContent>
      </Card>
    </>
  )
}

export default AdvancedCloudflare;
