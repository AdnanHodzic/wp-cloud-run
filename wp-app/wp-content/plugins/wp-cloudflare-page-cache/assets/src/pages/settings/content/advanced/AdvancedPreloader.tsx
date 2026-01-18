import CheckboxControl from "@/pages/settings/controls/CheckboxControl";
import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import LockedInputCopy from "@/common/LockedInputCopy";
import StartPreloaderButton from "@/common/StartPreloaderButton";
import Card, { CardContent, CardFooter, CardHeader } from "@/components/Card";
import Separator from "@/common/Separator";
import { cn } from "@/lib/utils";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { createInterpolateElement } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";

const AdvancedPreloader = () => {
  const { settings, isToggleOn } = useSettingsStore();
  const { cronjobURL } = window.SPCDash;

  const fullCronjobURL = cronjobURL.replace('replace:cf_preloader_url_secret_key', settings['cf_preloader_url_secret_key'] as string || '');

  const controls = [
    {
      id: 'cf_preloader',
      type: 'toggle',
      label: __('Enable preloader', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'cf_preloader_start_on_purge',
      type: 'toggle',
      label: __('Automatically preload the pages you have purged from cache.', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_preloader'),
    },
    {
      id: 'preloader-custom-operation',
      type: 'custom',
      label: __('Preloader operation', 'wp-cloudflare-page-cache'),
      description: __('Choose the URLs preloading logic that the preloader must use. If no option is chosen, the most recently published URLs and the home page will be preloaded.', 'wp-cloudflare-page-cache'),
      component: <PreloaderOperationCheckboxes />,
      hide: !isToggleOn('cf_preloader'),
    },
    {
      id: 'cf_preload_sitemap_urls',
      type: 'textarea',
      label: __('Preload all URLs into the following sitemaps', 'wp-cloudflare-page-cache'),
      description: (
        <>
          {__('One sitemap per line.', 'wp-cloudflare-page-cache')}
          <br />
          <br />

          {__('Example', 'wp-cloudflare-page-cache') + ':'}
          <br />
          <pre>
            /post-sitemap.xml<br />
            /page-sitemap.xml<br />
          </pre>
        </>
      ),
      hide: !isToggleOn('cf_preloader'),
    },
    {
      id: 'preloader_cronjob_info',
      type: 'custom',
      label: __('Start the preloader via Cronjob', 'wp-cloudflare-page-cache'),
      description: __('If you want start the preloader at specific intervals decided by you, you can create a cronjob that hits the following URL:', 'wp-cloudflare-page-cache'),
      component: <LockedInputCopy content={fullCronjobURL} />,
      hide: !isToggleOn('cf_preloader'),
    },
    {
      id: 'cf_preloader_url_secret_key',
      type: 'text',
      label: __('Cronjob secret key', 'wp-cloudflare-page-cache'),
      description: __('Secret key to use to start the preloader via URL. Don\'t touch if you don\'t know how to use it.', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('cf_preloader'),
    }
  ];

  return (
    <Card className={cn({ "pb-0": isToggleOn('cf_preloader') })}>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('Preloader', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <ControlsGroup controls={controls} />
      </CardContent>

      {isToggleOn('cf_preloader') && (
        <CardFooter className="flex justify-end">
          <StartPreloaderButton />
        </CardFooter>
      )}
    </Card>
  )
}


const PreloaderOperationCheckboxes = () => {
  const { wordpressMenus } = window.SPCDash;
  const { settings, isToggleOn, updateSetting } = useSettingsStore();
  const { asyncLocked } = useAppStore();

  const theValue = settings['cf_preloader_nav_menus'] || [];

  const handleToggleUpdate = (id, nextValue) => {
    let nextVal = [...theValue as string[]];

    if (nextValue) {
      nextVal.push(id);
    } else {
      nextVal = nextVal.filter(item => item != id);
    }

    updateSetting('cf_preloader_nav_menus', nextVal);
  }

  return (
    <div className="grid gap-3 mt-5">
      {Object.entries(wordpressMenus).map(([id, name], idx) => ([
        idx !== 0 && <Separator key={`separator-${id}`} />,
        <div className="flex items-center gap-2" key={id}>
          <CheckboxControl
            value={(settings['cf_preloader_nav_menus'] as string[]).includes(id) || false}
            id={id}
            label={<span> {createInterpolateElement(
              /* translators: %s is the menu name */ sprintf(__('Preload all internal links in <strong>%s</strong> WP Menu', 'wp-cloudflare-page-cache'), name),
              {
                strong: <strong className="font-bold opacity-75" />
              }
            )}</span>}
            onChange={(nextVal) => handleToggleUpdate(id, nextVal)}
            disabled={asyncLocked}
          />
        </div>
      ]))}

      {Object.keys(wordpressMenus).length > 0 && <Separator />}
      <div className="flex items-center gap-2">
        <CheckboxControl
          value={isToggleOn('cf_preload_last_urls')}
          id='cf_preload_last_urls'
          label={__('Preload last 20 published/updated posts, pages & CPTs combined', 'wp-cloudflare-page-cache')}
          disabled={asyncLocked}
          onChange={(nextValue) => {
            updateSetting('cf_preload_last_urls', nextValue ? 1 : 0);
          }}
        />
      </div>
    </div>
  );
}

export default AdvancedPreloader;
