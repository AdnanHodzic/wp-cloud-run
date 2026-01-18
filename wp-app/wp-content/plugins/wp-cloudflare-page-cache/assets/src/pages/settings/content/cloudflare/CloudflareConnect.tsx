import BaseControl from "@/pages/settings/controls/BaseControl";
import Select from "@/common/Select";
import ApiKeyForm from "@/pages/settings/content/cloudflare/ApiKeyForm";
import ApiTokenForm from "@/pages/settings/content/cloudflare/ApiTokenForm";
import { CF_AUTH_MODES } from "@/lib/constants";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

const CloudflareConnect = () => {
  const { settings } = useSettingsStore();
  const { asyncLocked } = useAppStore();
  const [authMode, setAuthMode] = useState(parseInt(settings.cf_auth_mode as string));

  return (
    <div className="grid gap-5">
      <BaseControl stackMobile id="cf_auth_mode" label={__('Authentication mode', 'wp-cloudflare-page-cache')} description={__('Select the authentication mode you want to use to connect to Cloudflare.', 'wp-cloudflare-page-cache')}>
        <div className="flex justify-end">
          <Select
            id="cf_auth_mode"
            className="w-xs ml-auto"
            disabled={asyncLocked}
            options={[
              { label: __('API Key', 'wp-cloudflare-page-cache'), value: CF_AUTH_MODES.API_KEY.toString() },
              { label: __('API Token', 'wp-cloudflare-page-cache'), value: CF_AUTH_MODES.API_TOKEN.toString() }
            ]}
            value={authMode.toString()}
            onChange={(v) => {
              setAuthMode(parseInt(v));
            }} />
        </div>
      </BaseControl>

      {authMode === CF_AUTH_MODES.API_KEY && (
        <ApiKeyForm />
      )}

      {authMode === CF_AUTH_MODES.API_TOKEN && (
        <ApiTokenForm />
      )}
    </div>
  )
}

export default CloudflareConnect; 