import PasswordInput from "@/common/PasswordInput";
import TransitionWrapper from "@/common/TransitionWrapper";
import Button from "@/components/Button";
import { spcApi } from "@/lib/api";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { createInterpolateElement, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Globe } from "lucide-react";
import { useConnectionStore } from "./connectionStore";

const ApiTokenForm = () => {
  const { asyncLocked, lockAsync } = useAppStore();
  const { settings, updateSettings } = useSettingsStore();

  const { setErrorMessage } = useConnectionStore();

  const [isConnecting, setIsConnecting] = useState(false);
  const [formData, setFormData] = useState({
    apiToken: settings.cf_apitoken as string
  })

  const isFormInvalid = (!formData.apiToken);

  const getZoneDomainList = async (e: React.FormEvent<HTMLFormElement>) => {
    setErrorMessage('');

    e.preventDefault();

    lockAsync(true);
    setIsConnecting(true);

    const data = {
      auth_mode: 'api_token',
      api_token: formData.apiToken,
    }

    const response = await spcApi.cloudflareConnect(data);

    lockAsync(false);
    setIsConnecting(false);

    if (response.success) {
      updateSettings(response.data.settings);

      return;
    }

    setErrorMessage(response.message);
  }

  return (
    <form onSubmit={getZoneDomainList} className="grid gap-5">
      <TransitionWrapper>
        <div className="mb-6">
          <label htmlFor="cf_api_token" className="block text-sm font-medium text-foreground/80 mb-2">{__('API Token', 'wp-cloudflare-page-cache')}</label>
          <PasswordInput
            type="password"
            id="cf_api_token"
            disabled={asyncLocked}
            value={formData.apiToken as string}
            className="w-full max-w-full h-10 m-0"
            autoComplete="off"
            onChange={(e) => {
              setFormData({ ...formData, apiToken: e.target.value });
            }}
          />

          <div className="mt-2 text-xs text-muted-foreground">
            <p className="mb-2 text-xs text-muted-foreground">
              {__('To get your API token:', 'wp-cloudflare-page-cache')}
            </p>

            <ol className="list-decimal list-inside space-y-2 ml-2">
              <li>{__('Log into your Cloudflare dashboard and navigate to "My Profile" > "API Tokens"', 'wp-cloudflare-page-cache')}</li>
              <li>{__('Click "Create Token" and select the "WordPress" template', 'wp-cloudflare-page-cache')}</li>
              <li>{createInterpolateElement(__('Add the <strong>Zone > Cache Rules > Edit</strong> permission', 'wp-cloudflare-page-cache'), {
                strong: <strong/>
              })}</li>
              <li>{createInterpolateElement(__('Add the <strong><strong>Zone > Transform Rules > Edit</strong></strong> permission', 'wp-cloudflare-page-cache'), {
                strong: <strong/>
              })}</li>
              <li>{__('Click "Continue to summary", review permissions, and click "Create Token"', 'wp-cloudflare-page-cache')}</li>
              <li>{__('Copy the generated token', 'wp-cloudflare-page-cache')}</li>
            </ol>
          </div>

        </div>
      </TransitionWrapper>

      <div className="flex justify-end">
        <Button
          className="w-full"
          variant="blue"
          type="submit"
          loader={isConnecting}
          disabled={isFormInvalid || asyncLocked}
          icon={Globe}
        >
          {isConnecting ?
            __('Connecting to Cloudflare', 'wp-cloudflare-page-cache') + '...' :
            __('Connect to Cloudflare', 'wp-cloudflare-page-cache')}
        </Button>
      </div>
    </form>
  )
}

export default ApiTokenForm;