import Button from "@/components/Button";
import { cn, formatBytes } from "@/lib/utils";
import { useAssetManagerStore } from "@/store/assetManagerStore";
import { __ } from "@wordpress/i18n";
import { Code, Palette } from "lucide-react";
import SearchAssets from "./SearchAssets";

const Tabs = () => {
  const { activeTab, assets, switchTab } = useAssetManagerStore();

  const jsAssetCount = assets.filter(asset => asset.asset_type === 'js').length;
  const cssAssetCount = assets.length - jsAssetCount;

  const currentTabAssets = assets.filter(asset => asset.asset_type === activeTab);

  const tabs = [
    {
      id: 'css',
      label: __('CSS Files', 'wp-cloudflare-page-cache'),
      icon: Palette,
      length: cssAssetCount,
    },
    {
      id: 'js',
      label: __('JS Files', 'wp-cloudflare-page-cache'),
      icon: Code,
      length: jsAssetCount,
    }
  ];

  const getSizeInBytes = (size: string): number => {
    const trimmed = size.trim().toUpperCase();
    const match = trimmed.match(/^([\d.]+)\s*(KB|MB|B)$/);
    if (!match) return 0;

    const value = parseFloat(match[1]);
    const unit = match[2];

    switch (unit) {
    case 'MB': return value * 1024 * 1024;
    case 'KB': return value * 1024;
    case 'B':  return value;
    default: return 0;
    }
  };

  const totalSizeInBytes = currentTabAssets.reduce((sum, asset) => {
    return sum + getSizeInBytes(asset.size);
  }, 0);

  const totalSize = formatBytes(totalSizeInBytes);

  return (
    <div className="flex items-center gap-2">
      <div className="flex">
        {
          tabs.map((tab, index) => {
            return (
              <Button
                variant="ghost"
                size="sm"
                key={tab.id}
                onClick={() => switchTab(tab.id)}
                icon={tab.icon}
                className={cn(
                  {
                    "rounded-l-lg rounded-r-none border-r-0": index === 0,
                    "rounded-r-lg rounded-l-none border-l-0": index === tabs.length - 1,
                    "rounded-none border-l-0 border-r-0": index > 0 && index < tabs.length - 1,
                    "bg-blue-500 text-white hover:bg-blue-600 hover:text-white": activeTab === tab.id,
                    "hover:bg-transparent text-blue-600 hover:text-foreground/80": activeTab !== tab.id,
                  }
                )}
              >
                {tab.label + ` (${tab.length})`}
              </Button>
            )
          })
        }
      </div>

      <div className="grow">
        <SearchAssets />
      </div>

      <div className="ml-auto text-sm text-gray-600">
        {__('Total Size: ', 'wp-cloudflare-page-cache')}<span className="font-medium">{totalSize}</span>
      </div>
    </div>
  )
}

export default Tabs;