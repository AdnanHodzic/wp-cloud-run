import Notice from "@/components/Notice";
import Card, { CardContent, CardHeader } from "@/components/Card";
import ExternalLink from "@/common/ExternalLink";
import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import PageContent from "@/layout/PageContent";
import { useSettingsStore } from "@/store/optionsStore";
import { __ } from "@wordpress/i18n";
import { InfoIcon } from "lucide-react";
import { ImageOptimization } from "./ImageOptimization";
const Media = () => {

  const { i18n, isPro } = window.SPCDash;
  const { pageCacheOn, isToggleOn, isValueSelected,updateSetting } = useSettingsStore();
  const { active: isOptimoleActive } = window.SPCDash.optimoleData;
  const controls = [
    {
      id: 'cf_native_lazy_loading',
      type: 'toggle',
      label: __('Native Lazy Load', 'wp-cloudflare-page-cache'),
      disabled: isToggleOn('cf_lazy_loading'),
    },
    {
      id: 'cf_lazy_loading',
      type: 'toggle',
      label: __('Lazy Load', 'wp-cloudflare-page-cache'),
      description: <>
        {__('Disables native lazy-loading and uses a custom solution for better control over image loading, potentially improving performance.', 'wp-cloudflare-page-cache')}

        <ExternalLink url="https://docs.themeisle.com/article/2059-native-lazyloading-vs-spc-lazyloading">
          {__('More Info', 'wp-cloudflare-page-cache')}
        </ExternalLink>
      </>,
      sideEffectCallback: (nextValue: boolean) => {
        if (nextValue) {
          updateSetting('cf_native_lazy_loading', 0);
        }
      },
    },
    {
      id: 'cf_lazy_load_behaviour',
      type: 'select',
      label: __('Lazy load behavior for images', 'wp-cloudflare-page-cache'),
      description:  <>
        {__('Choose how we will handle lazy loading for images on your website.', 'wp-cloudflare-page-cache')}
        <p className="mt-2 pt-2 text-sm italic text-foreground/70">
          <InfoIcon className="w-3 h-3 inline mr-1 mb-1" />
          {isValueSelected('cf_lazy_load_behaviour', 'all') && 
          __('All images will use lazy loading regardless of position.', 'wp-cloudflare-page-cache')
          }
          {isValueSelected('cf_lazy_load_behaviour', 'fixed') && 
          __('Indicate how many images at the top of each page should bypass lazy loading, ensuring they\'re instantly visible.', 'wp-cloudflare-page-cache')
          }
          {isValueSelected('cf_lazy_load_behaviour', 'viewport') && (
            <>
              {__('Automatically detects and immediately loads images visible in the initial viewport. Detection is done with a lightweight client-side script that identifies what\'s visible on each user\'s screen. All other images will lazy load.', 'wp-cloudflare-page-cache')}
              {!isPro && (
                <p className="text-sm mt-2 text-muted-foreground">
                  {__('This feature is only available in the Pro version.', 'wp-cloudflare-page-cache')}
                </p>
              )}
            </>
          )}
        </p>
      </>,
      options: [
        { label: __('Lazy load all images', 'wp-cloudflare-page-cache'), value: 'all' },
        { label: __('Skip Lazy Loading for First Images', 'wp-cloudflare-page-cache'), value: 'fixed' },
        { label: (!isPro ? '[PRO]' :'' ) + ' ' + __('Skip Lazy Loading for Initial Viewport', 'wp-cloudflare-page-cache') , value: 'viewport' },
      ], 
      hide: !isToggleOn('cf_lazy_loading')
    },
    {
      id: 'cf_lazy_load_skip_images',
      type: 'number',
      label: __('Skip Lazy Loading for First Images', 'wp-cloudflare-page-cache'),
      min: 0,
      description: i18n.bypassLazyLoadDescription,
      hide: !isToggleOn('cf_lazy_loading') || !isValueSelected('cf_lazy_load_behaviour', 'fixed')
    },
    {
      id: 'cf_lazy_load_video_iframe',
      type: 'toggle',
      label: __('Lazy load videos and iframes', 'wp-cloudflare-page-cache'),
      description: __('By default, lazy loading does not work for embedded videos and iframes. Enable this option to activate the lazy-load on these elements.', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_lazy_loading')
    },
    {
      id: 'cf_lazy_load_excluded',
      type: 'textarea',
      placeholder: 'logo.jpg\nexcluded-class',
      label: __('Media Lazy Load Exclusions', 'wp-cloudflare-page-cache'),
      description: __('Enter one keyword per line to exclude items from lazy loading by checking if URLs, class names, or data attributes contain these keywords.', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_lazy_loading')
    },
    {
      id: 'cf_lazy_load_bg',
      type: 'toggle',
      label: __('Background images lazy load', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_lazy_loading')
    },
    {
      id: 'cf_lazy_load_bg_selectors',
      type: 'textarea',
      placeholder: '.bg-selector\nbody > .container',
      label: __('Background Images Lazy Load Exclusions', 'wp-cloudflare-page-cache'),
      description: __('Enter CSS selectors for any background images not covered by the default lazy loading. This ensures those images also benefit from the optimized loading process.', 'wp-cloudflare-page-cache'),
      hide: !(isToggleOn('cf_lazy_load_bg') && isToggleOn('cf_lazy_loading'))
    }
  ]

  return (
    <PageContent>
      <Card>

        {(!pageCacheOn) && (
          <CardHeader>
            <Notice type="warning">
              <span dangerouslySetInnerHTML={{ __html: i18n.warningMediaSection }} />
            </Notice>
          </CardHeader>
        )}


        <CardContent className="p-0">
          <ControlsGroup controls={controls} />
        </CardContent>

      </Card>

      {!isOptimoleActive && <ImageOptimization />}
    </PageContent >
  )
}

export default Media;
