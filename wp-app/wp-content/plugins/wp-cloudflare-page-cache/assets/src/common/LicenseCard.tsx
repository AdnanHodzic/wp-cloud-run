import Button from "@/components/Button";
import { spcApi } from "@/lib/api";
import { LICENSE_ACTIONS, LINKS, ROOT_PAGES } from "@/lib/constants";
import { getUpsellURL } from "@/lib/utils";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { KeySquare, Play } from "lucide-react";
import { toast } from "sonner";

const LicenseCard = () => {
  const { asyncLocked, lockAsync, setLicenseData, setShowWizard, setRootPage } = useAppStore();
  const { updateSettings } = useSettingsStore();

  const [activating, setActivating] = useState(false);
  const [enablingCache, setEnablingCache] = useState(false);
  const [inputValue, setInputValue] = useState('');

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    setActivating(true);
    lockAsync(true);

    const formData = new FormData(e.currentTarget);
    const keyToSend = formData.get('licenseKey') as string;

    const response = await spcApi.toggleLicenseKey({
      action: LICENSE_ACTIONS.ACTIVATE,
      key: keyToSend
    })

    setActivating(false);

    if (!response.success) {
      toast.error(response.message);

      lockAsync(false);

      return;
    }

    const licenseData = response.data.license;

    setEnablingCache(true);
    setActivating(false);

    const activationResponse = await spcApi.enablePageCache();

    if (!activationResponse.success) {
      toast.error(activationResponse.message);

      setEnablingCache(false);
      lockAsync(false);

      return;
    }

    toast.success(response.message, { description: activationResponse.message });

    updateSettings(activationResponse.data.settings);

    lockAsync(false);
    setEnablingCache(false);
    setLicenseData(licenseData);
    setShowWizard(false);
  }

  const isLoading = activating || enablingCache;

  return (
    <div className="mb-8 p-4 bg-amber-50 dark:bg-yellow-900/20 rounded-lg border border-amber-200 dark:border-yellow-700/50">
      <div className="flex items-start">

        <div className="flex-1">

          <h3 className="text-base font-semibold text-amber-900 dark:text-yellow-200 mb-3 flex items-center">
            <KeySquare className="size-4 mr-2" />
            {__('License Key Required', 'wp-cloudflare-page-cache')}
          </h3>
          <p className="text-sm text-amber-800 dark:text-yellow-300 mb-4">
            {__('Enter your Super Page Cache Pro license key to activate advanced caching features.', 'wp-cloudflare-page-cache')}
          </p>

          <form onSubmit={handleSubmit}>
            <label htmlFor="license_key" className="block text-sm font-medium text-amber-900 dark:text-yellow-200 mb-2">
              {__('License Key', 'wp-cloudflare-page-cache')}
            </label>
            <div className="flex gap-3">
              <input
                id="license_key"
                placeholder={__('Enter your license key', 'wp-cloudflare-page-cache')}
                className="flex-1 px-3 py-2 border font-mono border-amber-300 dark:border-yellow-600/50 bg-card rounded-md text-sm dark:text-gray-200 dark:placeholder-gray-500"
                type="password"
                autoComplete="off"
                value={inputValue}
                onChange={(e) => setInputValue(e.target.value)}
                disabled={isLoading || asyncLocked}
                name="licenseKey"
                required
              />

              <Button
                variant="cta"
                size="lg"
                type="submit"
                disabled={isLoading || asyncLocked}
                loader={isLoading}
                icon={Play}
              >
                {isLoading ?
                  __('Activating...', 'wp-cloudflare-page-cache') :
                  __('Activate Cache & License', 'wp-cloudflare-page-cache')}
              </Button>
            </div>
          </form>

          <div className="flex items-center gap-4 mt-4 text-xs">
            <a
              className="hover:underline font-medium text-orange-500 dark:text-orange-400 hover:text-orange-800"
              href={LINKS.STORE}
              target="_blank" rel="noreferrer"
            >
              {__('Find my license key', 'wp-cloudflare-page-cache')}
            </a>
            <a
              className="hover:underline font-medium text-orange-500 dark:text-orange-400 hover:text-orange-800"
              href={getUpsellURL('license-card')}
              target="_blank" rel="noreferrer"
            >
              {__('Purchase a license', 'wp-cloudflare-page-cache')}
            </a>
            <button onClick={() => setRootPage(ROOT_PAGES.HELP)} className="hover:underline font-medium text-orange-500 dark:text-orange-400 hover:text-orange-800">
              {__('Help', 'wp-cloudflare-page-cache')}
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}

export default LicenseCard;