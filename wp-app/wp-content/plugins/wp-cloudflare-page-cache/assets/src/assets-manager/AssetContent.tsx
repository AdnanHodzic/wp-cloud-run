import ContextControl from "@/assets-manager/ContextControl";
import { useAssetManagerStore } from "@/store/assetManagerStore";
import { AccordionContent, AccordionItem, AccordionTrigger } from "@radix-ui/react-accordion";
import { __ } from "@wordpress/i18n";
import { CircleAlert, MapPin, Users } from "lucide-react";
import ListItem from "./ListItem";
import { Asset } from "./globals";
import TransitionWrapper from "@/common/TransitionWrapper";


const AssetContent = ({ asset }: {asset: Asset}) => {
  const { assets, markChange, updateAssets } = useAssetManagerStore();
  const { availableContexts } = window.SPCAssetManager;
  const { locationContexts, userStateContexts } = availableContexts;

  const handleToggle: ( contextKey: string, value: boolean, type: 'locationContexts' | 'userStateContexts') => void = ( contextKey, value, type = 'locationContexts') => {
    const assetId = asset.asset_hash;
    const assetToUpdate = {...asset};

    const contextData = (type === 'locationContexts' ? locationContexts : userStateContexts)
      .find((rule) => rule.key === contextKey);

    if (!contextData) {
      return;
    }

    const ruleSaveAsKey = contextData.saveAs;
    
    if (value) {
      // If on, we remove the disabled rule.
      assetToUpdate[type] = assetToUpdate[type].filter((key) =>  key !== ruleSaveAsKey );
    } else {
      // If off, we add the disabled rule.
      assetToUpdate[type].push(ruleSaveAsKey);
    }

    assetToUpdate[type] = Array.from(new Set(assetToUpdate[type]));

    const updatedAssets = [...assets].map((a) => a.asset_hash === assetId ? assetToUpdate : a);

    updateAssets(updatedAssets);
    markChange(assetId, { [contextKey]: value });
  };

  const assetContext = [
    {
      key: 'locationContexts',
      title: __('Location Rules', 'wp-cloudflare-page-cache'),
      icon: MapPin,
      contexts: locationContexts,
      handleToggle: (key, value) => handleToggle( key, value, 'locationContexts'),
    },
    {
      key: 'userStateContexts',
      title: __('User State Filters', 'wp-cloudflare-page-cache'),
      icon: Users,
      contexts: userStateContexts,
      handleToggle: (key, value) => handleToggle( key, value, 'userStateContexts'),
    },
  ];

  return (
    <AccordionItem value={asset.asset_hash} className="max-w-full rounded-lg border border-gray-200">

      <AccordionTrigger className="group max-w-full w-full">
        <ListItem asset={asset}/>
      </AccordionTrigger>

      <AccordionContent className="p-4 bg-muted space-y-4 border-t border-gray-200">
        {assetContext.map((context, index) => (
          <ContextControl key={index} asset={asset} context={context} />
        ))}

        <OtherExclusion assetHash={asset.asset_hash}/>

      </AccordionContent>

    </AccordionItem>
  )
}


const OtherExclusion = ( {assetHash } : {assetHash: string} ) => {
  const { otherExclusions } = window.SPCAssetManager;

  if( ! otherExclusions[assetHash] ) {
    return null;
  }

  return            (   <TransitionWrapper from="fade">
    <div className="flex items-center mb-4 text-sm font-medium">
      <CircleAlert className="w-4 h-4 mr-2" />
      <h5 className="text-gray-700">
        {__('Other Exclusions', 'wp-cloudflare-page-cache')}
      </h5>
    </div>
    <div className="flex flex-wrap gap-2">
      {otherExclusions[assetHash].map((exclusion, index) => (
        <a key={index} href={exclusion.url} className="text-sm bg-gray-700 px-3 py-1 rounded text-white font-semibold hover:bg-gray-800 transition-colors">
          {exclusion.label}
        </a>
      ))}
    </div>

  </TransitionWrapper>)
}

export default AssetContent;