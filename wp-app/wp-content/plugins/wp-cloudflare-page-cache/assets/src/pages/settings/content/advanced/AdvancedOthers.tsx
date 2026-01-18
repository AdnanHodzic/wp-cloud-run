import LockedInputCopy from "@/common/LockedInputCopy";
import Separator from "@/common/Separator";
import Card, { CardContent, CardHeader } from "@/components/Card";
import CheckboxControl from "@/pages/settings/controls/CheckboxControl";
import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { __ } from "@wordpress/i18n";

const AdvancedOthers = () => {
  const { settings } = useSettingsStore();
  const { cronjobPurgeURL } = window.SPCDash;

  const fullCronjobPurgeURL = cronjobPurgeURL.replace('replace:cf_purge_url_secret_key', settings['cf_purge_url_secret_key'] as string || '');

  const controls = [
    {
      id: 'cf_opcache_purge_on_flush',
      type: 'toggle',
      label: __('Automatically purge the OPcache cache is purged', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'cf_object_cache_purge_on_flush',
      type: 'toggle',
      label: __('Automatically purge the object cache when cache is purged', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'cronjob_purge_url',
      type: 'custom',
      label: __('Purge the whole cache via Cronjob', 'wp-cloudflare-page-cache'),
      description: __('If you want purge the whole cache at specific intervals decided by you, you can create a cronjob that hits the following URL:', 'wp-cloudflare-page-cache'),
      component: <LockedInputCopy content={fullCronjobPurgeURL} />,
    },
    {
      id: 'cf_purge_url_secret_key',
      type: 'text',
      label: __('Purge cache URL secret key', 'wp-cloudflare-page-cache'),
      description: __('Secret key to use to purge the whole Cloudflare cache via URL. Don\'t touch if you don\'t know how to use it.', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'cf_remove_purge_option_toolbar',
      type: 'toggle',
      label: __('Remove purge option from toolbar', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'cf_disable_single_metabox',
      type: 'toggle',
      label: __('	Hide Metaboxes', 'wp-cloudflare-page-cache'),
      description: __('Disable the metaboxes on single pages and posts to avoid conflicts with other plugins.', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'cf_seo_redirect',
      type: 'toggle',
      label: __('SEO redirect', 'wp-cloudflare-page-cache'),
      description: __('Enable this option if you want to redirect the user to the correct page when the page is not found.', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'purge_roles_custom',
      label: __('Select user roles allowed to purge the cache', 'wp-cloudflare-page-cache'),
      description: __('Admins are always allowed.', 'wp-cloudflare-page-cache'),
      type: 'custom',
      component: <UserRolesCheckboxes />,
    },
    {
      id: 'cf_prefetch_urls_viewport',
      type: 'toggle',
      label: __('Auto prefetch URLs in viewport', 'wp-cloudflare-page-cache'),
      description: __('If enabled, the browser prefetches in background all the internal URLs found in the viewport.', 'wp-cloudflare-page-cache'),
      //		render_description(__('Purge the cache and wait about 30 seconds after enabling/disabling this option.', 'wp-cloudflare-page-cache'), true);
      //		render_description(__('URIs in <em>Prevent the following URIs to be cached</em> will not be prefetched.', 'wp-cloudflare-page-cache'), true);

    },
    {
      id: 'cf_prefetch_urls_on_hover',
      type: 'toggle',
      label: __('Auto prefetch URLs on mouse hover', 'wp-cloudflare-page-cache'),
      description: __('If enabled, the browser prefetches in background all the internal URLs found in the viewport.', 'wp-cloudflare-page-cache'),
    },
    {
      type: 'toggle',
      id: 'cf_remove_cache_buster',
      label: __('Remove Cache Buster Query Parameter', 'wp-cloudflare-page-cache'),
      description: __('Stop adding cache buster query parameter when using the default page rule mode.', 'wp-cloudflare-page-cache'),
      // This is here as a legacy fallback.
      hide: window.SPCDash.settings?.cf_remove_cache_buster || 1,
    },
    {
      type: 'toggle',
      id: 'keep_settings_on_deactivation',
      label: __('Keep settings on deactivation', 'wp-cloudflare-page-cache'),
      description: __('Keep settings on plugin deactivation.', 'wp-cloudflare-page-cache'),
    },
  ]

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('Other Settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <ControlsGroup controls={controls} />
      </CardContent>
    </Card >
  )
}


const UserRolesCheckboxes = () => {
  const { wordpressRoles } = window.SPCDash;
  const { settings, updateSetting } = useSettingsStore();
  const { asyncLocked } = useAppStore();

  const handleToggleUpdate = (id, nextValue) => {
    let nextVal = [...(settings['cf_purge_roles'] || []) as string[]];

    if (nextValue) {
      nextVal.push(id);
    } else {
      nextVal = nextVal.filter(item => item != id);
    }

    updateSetting('cf_purge_roles', nextVal);
  }

  return (
    <div className="grid gap-3 mt-5  md:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 w-full">
      {Object.entries(wordpressRoles).map(([role, name], idx) => {
        return (<div key={`${role}-${idx}`} className="grid gap-3">
          {(idx !== 0 && idx !== 1) && <Separator />}
          <div className="flex items-center gap-2">
            <CheckboxControl
              value={(settings['cf_purge_roles'] as string[]).includes(role) || false}
              id={role}
              label={name}
              onChange={(nextVal) => handleToggleUpdate(role, nextVal)}
              disabled={asyncLocked}
            />
          </div>
        </div>)
      })}
    </div>
  )
}


export default AdvancedOthers;