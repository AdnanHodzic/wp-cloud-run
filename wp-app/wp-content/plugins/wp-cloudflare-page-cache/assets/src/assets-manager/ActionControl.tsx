import Button from "@/components/Button";
import { useAssetManagerStore } from "@/store/assetManagerStore";

import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { RotateCcw, Save } from "lucide-react";
import { toast } from "sonner";

const ActionControl = () => {
  const { closeModal, resetAssets, assets, asyncLocked, lockAsync, changesToSave } = useAssetManagerStore();

  const { availableContexts, api, nonce } = window.SPCAssetManager;
  const { locationContexts, userStateContexts } = availableContexts;

  const [loading, setLoading] = useState(false);

  const handleReset = () => {
    resetAssets()

    toast.success(__('All assets have been reset to enabled state.', 'wp-cloudflare-page-cache'), {
      description: __('You can now select the contexts you want to disable.', 'wp-cloudflare-page-cache'),
    });
  };

  const reloadCleanURL = () => {
    const url = new URL(window.location.href);
    url.search = '';
    window.history.replaceState({}, '', url.href);
    window.location.reload();
  }

  const handleSave = async () => {
    lockAsync(true);
    setLoading(true);

    const saveData = {};
    const flatContexts = [...locationContexts, ...userStateContexts];
    const flatAssets = Object.values(assets).flat();

    Object.entries(changesToSave).forEach(([assetHash, changes]) => {
      const foundAsset = flatAssets.find(asset => asset.asset_hash === assetHash);

      if (!foundAsset) return;

      saveData[assetHash] = {
        rules: {},
      }

      Object.entries(changes).forEach(([key, boolValue]) => {
        const foundContext = flatContexts.find(context => context.key === key);

        if (!foundContext || !foundContext.saveAs) return;

        saveData[assetHash].rules[foundContext.saveAs] = boolValue;
      });

      if (saveData[assetHash]?.rules) {
        saveData[assetHash] = {
          ...saveData[assetHash],
          asset_name: foundAsset.name,
          asset_type: foundAsset.asset_type,
          origin_type: foundAsset.origin_type,
          asset_url: foundAsset.asset_url,
        }
      }
    });


    if (Object.keys(saveData).length === 0) {
      toast.error(__('No changes to save.', 'wp-cloudflare-page-cache'));
      setLoading(false);
      lockAsync(false);
      return;
    }

    const data = JSON.stringify({ assets_data: saveData });

    try {
      const response = await fetch(`${api}/assets/save-rules`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': nonce
        },
        body: data,
      });

      const result = await response.json();

      if (!result.success) {
        // Handle error response
        const errorMessage = result.data || __('Unknown error occurred.', 'wp-cloudflare-page-cache');
        toast.error(__('Failed to save asset rules.', 'wp-cloudflare-page-cache'), {
          description: errorMessage,
        });
        lockAsync(false);
        return;
      }

      // Handle success response
      toast.success(result.message, {
        description: __('Please wait, the page will reload soon.', 'wp-cloudflare-page-cache'),
        action: {
          label: __('Reload Now', 'wp-cloudflare-page-cache'),
          onClick: reloadCleanURL
        }
      });

      setTimeout(reloadCleanURL, 3000);

    } catch (error) {
      toast.error(__('Failed to save asset rules.', 'wp-cloudflare-page-cache'), {
        description: error.message,
      });

      lockAsync(false);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex items-center justify-between p-4 border-t border-gray-200 bg-gray-50 relative gap-3">
      <Button
        variant="outline"
        icon={RotateCcw}
        onClick={handleReset}
        disabled={asyncLocked}
        className="rounded-lg py-1 px-3"
      >
        {__('Enable All (Reset)', 'wp-cloudflare-page-cache')}
      </Button>

      <Button
        variant="outline"
        className="ml-auto rounded-lg py-1 px-3"
        onClick={closeModal}
        disabled={asyncLocked}
      >
        {__('Cancel', 'wp-cloudflare-page-cache')}
      </Button>

      <Button
        variant="cta"
        className="shadow-none hover:shadow-none rounded-lg py-1 px-3"
        onClick={handleSave}
        disabled={asyncLocked}
        icon={Save}
        loader={loading}
      >
        {asyncLocked ?
          __('Saving', 'wp-cloudflare-page-cache') + '...' :
          __('Save Changes', 'wp-cloudflare-page-cache')}
      </Button>
    </div>
  )
}

export default ActionControl;
