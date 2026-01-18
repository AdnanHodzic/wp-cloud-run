import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { CheckCircleIcon, CheckIcon, Globe, PenLine } from "lucide-react";

import Select from "@/common/Select";
import Button from "@/components/Button";

import { spcApi } from "@/lib/api";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { toast } from "sonner";
import { useConnectionStore } from "./connectionStore";
import { CF_AUTH_MODES } from "@/lib/constants";


const ZoneIdConnect = () => {
  const { settings, updateSettings, cloudflareConnected } = useSettingsStore();
  const { asyncLocked, lockAsync } = useAppStore();
  const { setErrorMessage } = useConnectionStore();

  const [loading, setLoading] = useState(false);
  const [disconnecting, setDisconnecting] = useState(false);
  const [isEditingZoneId, setIsEditingZoneId] = useState(false);
  const [selectedZoneId, setSelectedZoneId] = useState(Object.values(settings.cf_zoneid_list)[0] || '');

  const options = [
    {
      label: __('Select a domain', 'wp-cloudflare-page-cache'),
      value: ''
    },
    ...Object.entries(settings.cf_zoneid_list).map(([domain, zoneId]) => ({ label: domain, value: zoneId }))];


  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    setErrorMessage('');

    if (!selectedZoneId) {
      return;
    }

    lockAsync(true);
    setLoading(true);

    const data = {
      zone_id: selectedZoneId,
    }

    const response = await spcApi.cloudflareConfirmZoneId(data);

    setLoading(false);
    lockAsync(false);

    if (!response.success) {
      toast.error(response.message);
      return;
    }

    if (response?.data?.permissions?.length > 0) {
      setErrorMessage('permission_error');

      return;
    }

    updateSettings(response.data.settings);
    toast.success(response.message);
    setIsEditingZoneId(false);
  }

  const disconnect = async () => {
    const confirm = window.confirm(__('Are you sure you want to disconnect Cloudflare?', 'wp-cloudflare-page-cache'));
    if (!confirm) {
      return;
    }

    setErrorMessage('');
    lockAsync(true);
    setDisconnecting(true);

    const response = await spcApi.cloudflareDisconnect();

    lockAsync(false);
    setDisconnecting(false);

    if (response.success) {
      updateSettings(response.data.settings);

      toast.success(response.message);

      return;
    }

    toast.error(response.message);
  }

  // Get the domain from the zoneid list.
  const domain = Object.keys(settings.cf_zoneid_list).find((key) => settings.cf_zoneid_list[key] === settings.cf_zoneid) || '';

  const isTokenAuth = settings.cf_auth_mode === CF_AUTH_MODES.API_TOKEN;

  return (
    <div className="p-6">

      <div className="border rounded-lg p-3 bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-700/30">
        <div className="flex items-center justify-between">
          <div className="flex items-center">
            <CheckCircleIcon className="size-5 text-green-600 dark:text-green-400 mr-3" />
            <div>
              <h4 className="text-sm font-semibold text-green-900 dark:text-green-200">
                {__('Successfully Connected', 'wp-cloudflare-page-cache')}
              </h4>
              <p className="text-sm text-green-800 dark:text-green-300">
                {isTokenAuth ?
                  __('Using API Token', 'wp-cloudflare-page-cache') :
                  __('Account:', 'wp-cloudflare-page-cache') + ' ' + settings.cf_email
                }
              </p>
            </div>
          </div>
          <Button
            onClick={disconnect}
            disabled={asyncLocked}
            loader={disconnecting}
            variant="link"
            size="sm"
            className="text-red-500 dark:text-red-400 dark:hover:text-red-500 bg-transparent hover:bg-transparent">
            {disconnecting ?
              __('Disconnecting', 'wp-cloudflare-page-cache') + '...' :
              __('Disconnect', 'wp-cloudflare-page-cache')
            }
          </Button>
        </div>
      </div>


      {(!cloudflareConnected || isEditingZoneId) && (
        <form className="mt-6" onSubmit={handleSubmit}>
          <label htmlFor="auth-mode" className="block text-sm font-medium text-foreground/80 mb-2">
            {__('Select Domain', 'wp-cloudflare-page-cache')}
          </label>
          <Select
            id="zone-id"
            className="w-full max-w-full h-10"
            disabled={asyncLocked}
            value={selectedZoneId}
            onChange={(v) => {
              setSelectedZoneId(v);
            }}
            options={options}
          />
          <p className="text-xs text-muted-foreground mt-1.5">
            {__('Choose the domain you want to optimize with Cloudflare', 'wp-cloudflare-page-cache')}
          </p>

          <div className="flex items-center gap-2 mt-3">
            <Button
              className="rounded"
              type="submit"
              size="sm"
              disabled={loading || asyncLocked || !selectedZoneId}
              loader={loading}
              variant="green"
              icon={CheckIcon}
            >

              {loading ?
                __('Saving', 'wp-cloudflare-page-cache') + '...' :
                __('Save Domain', 'wp-cloudflare-page-cache')
              }
            </Button>
            {isEditingZoneId && (
              <Button
                variant="outline"
                className="rounded"
                size="sm"
                onClick={() => setIsEditingZoneId(false)}
              >
                {__('Cancel', 'wp-cloudflare-page-cache')}
              </Button>
            )}
          </div>

        </form>
      )}

      {(cloudflareConnected && !isEditingZoneId) && (
        <div className="mt-6">
          <p className="block text-sm font-medium text-foreground/80 mb-2">
            {__('Active domain', 'wp-cloudflare-page-cache')}
          </p>

          <div className="flex items-center justify-between p-3 bg-muted border border-muted-foreground/20 rounded-md">
            <div className="flex items-center">
              <Globe className="size-4 text-green-600 dark:text-green-400 mr-2" />
              <span className="font-medium">{domain}</span>
            </div>
            <Button
              onClick={() => setIsEditingZoneId(true)}
              variant="link"
              className="p-0 h-auto text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-500 font-medium"
            >
              <PenLine className="size-3 mr-1" />
              {__('Change Domain', 'wp-cloudflare-page-cache')}
            </Button>
          </div>

          <p className="text-xs text-muted-foreground mt-1.5">
            {__('This domain is currently optimized with Cloudflare', 'wp-cloudflare-page-cache')}
          </p>

        </div>
      )}
    </div>
  )
}

export default ZoneIdConnect;