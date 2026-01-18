import PasswordInput from "@/common/PasswordInput";
import TransitionWrapper from "@/common/TransitionWrapper";
import Button from "@/components/Button";
import { Input } from "@/components/ui/input";
import { spcApi } from "@/lib/api";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";

import { useConnectionStore } from "./connectionStore";

import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Globe } from "lucide-react";

const ApiKeyForm = () => {
  const { setErrorMessage } = useConnectionStore();
  const { asyncLocked, lockAsync } = useAppStore();
  const { settings, updateSettings } = useSettingsStore();
  const [isConnecting, setIsConnecting] = useState(false);

  const [formData, setFormData] = useState({
    email: settings.cf_email as string,
    apiKey: settings.cf_apikey as string,
  });

  const getZoneDomainList = async (e: React.FormEvent<HTMLFormElement>) => {
    setErrorMessage('');

    e.preventDefault();

    lockAsync(true);
    setIsConnecting(true);

    const data = {
      auth_mode: 'api_key',
      email: formData.email,
      api_key: formData.apiKey,
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
  
  const isFormInvalid = (!formData.email || !formData.apiKey);

  return (
    <form onSubmit={getZoneDomainList}>

      <TransitionWrapper>
        <div className="mb-6">
          <label htmlFor="cf_email" className="block text-sm font-medium text-foreground/80 mb-2">{__('Cloudflare Email', 'wp-cloudflare-page-cache')}</label>
          <Input
            type="email"
            id="cf_email"
            disabled={asyncLocked}
            value={formData.email as string}
            onChange={(e) => {
              setFormData({ ...formData, email: e.target.value });
            }}
            className="w-full max-w-full h-10 m-0"
            placeholder="user@example.com"
          />
          <p className="text-xs text-muted-foreground mt-1.5">
            {__('The email address you use to log in to Cloudflare', 'wp-cloudflare-page-cache')}
          </p>
        </div>

        <div className="mb-6">
          <label htmlFor="cf_api_key" className="block text-sm font-medium text-foreground/80 mb-2">{__('Global API Key', 'wp-cloudflare-page-cache')}</label>
          <PasswordInput
            type="password"
            id="cf_api_key"
            disabled={asyncLocked}
            value={formData.apiKey as string}
            className="w-full max-w-full h-10 m-0"
            autoComplete="off"
            onChange={(e) => {
              setFormData({ ...formData, apiKey: e.target.value });
            }}
          />
          <div className="mt-2 text-xs text-muted-foreground">
            <p className="mb-2 text-xs text-muted-foreground">
              {__('To get your API credentials:', 'wp-cloudflare-page-cache')}
            </p>

            <ol className="list-decimal list-inside space-y-2 ml-2">
              <li>{__('Log in to your Cloudflare account and click on "My Profile"', 'wp-cloudflare-page-cache')}</li>
              <li>{__('Click on API Tokens and scroll to the bottom and click on "View beside Global API Key"', 'wp-cloudflare-page-cache')}</li>
              <li>{__('Enter your Cloudflare login password and click on "View"', 'wp-cloudflare-page-cache')}</li>
              <li>{__('Copy the API key and paste it in the form below', 'wp-cloudflare-page-cache')}</li>
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

export default ApiKeyForm;