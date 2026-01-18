import { useAssetManagerStore } from "@/store/assetManagerStore";
import { Accordion } from "@radix-ui/react-accordion";
import { useMemo } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";
import { CircleOff, Search } from "lucide-react";
import AssetContent from "./AssetContent";

const List = () => {
  const { activeTab, searchQuery, assets } = useAssetManagerStore();

  const visibleAssets = useMemo(() => {
    return assets.filter(asset =>
      asset.asset_type === activeTab &&
      (
        asset.name.toLowerCase().includes(searchQuery)
        || asset.asset_url.toLowerCase().includes(searchQuery)
        || asset.handle.toLowerCase().includes(searchQuery)
        || asset.category.toLowerCase().includes(searchQuery))
    );
  }, [searchQuery, activeTab, assets]);

  return ( 
    <div className="grow overflow-y-auto">
      <div className="bg-background p-4 max-w-full">
        {visibleAssets.length > 0 && (
          <Accordion type="single" collapsible className="space-y-2">
            {visibleAssets.map((asset) => {
              return (
                <AssetContent asset={asset} key={asset.asset_hash} />
              );
            })}
          </Accordion>
        )}

        {visibleAssets.length < 1 && searchQuery.length > 0 && (
          <EmptyAssetsSearchResult />
        )}

        {visibleAssets.length < 1 && searchQuery.length < 1 && (
          <EmptyAssets />
        )}
      </div>
    </div>
  )
}

const EmptyAssetsSearchResult = () => {
  const { searchQuery } = useAssetManagerStore();

  return (
    <div className="p-8 text-center text-gray-500">
      <Search className="w-8 h-8 mx-auto mb-2 text-gray-300" />
      <p className="text-sm">
        {sprintf(
          // translators: %s is the search query
          __('No assets found matching "%s"', 'wp-cloudflare-page-cache'), searchQuery
        )}
      </p>
    </div>
  )
}

const EmptyAssets = () => {
  const { activeTab } = useAssetManagerStore();

  return (
    <div className="p-8 text-center text-gray-500">
      <CircleOff className="w-8 h-8 mx-auto mb-2 text-gray-300" />
      <p className="text-sm">
        {sprintf(
          // translators: %s is the asset type (css/js)
          __('No %s assets found.', 'wp-cloudflare-page-cache'), activeTab
        )}
      </p>
    </div>
  )
}

export default List;

