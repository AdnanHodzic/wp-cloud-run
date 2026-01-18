import AssetIcon from "@/assets-manager/AssetIcon";
import { cn } from "@/lib/utils";
import { __ } from "@wordpress/i18n";
import { ChevronRight, Lock, Power, PowerOff } from "lucide-react";

interface AssetStatusDetails {
  status: string;
  label?: string;
}

type listItemProps = {
  asset: Record<string, any>,
}

const ListItem = ({ asset }: listItemProps) => {
  const getAssetStatus = (asset): AssetStatusDetails => {
    if (asset.locationContexts.length === 0 && asset.userStateContexts.length === 0) {
      return {
        status: 'enabled',
      };
    }

    if (asset.locationContexts.includes('global')) {
      return {
        status: 'disabled',
      };
    }

    return {
      status: 'partially_disabled',
    };    
  }

  const { label, status } = getAssetStatus(asset);

  return (

    <div className="p-3 flex items-center gap-3 max-w-full w-full">
      <div className="flex items-center space-x-3 flex-1 min-w-0">
        <ChevronRight className="size-3 transition-transform duration-300 group-data-[state=open]:rotate-90 flex-shrink-0" />

        <AssetIcon className="flex items-center flex-shrink-0" category={asset.category} />

        <div className="text-left min-w-0 flex-1">
          <div className="flex items-center space-x-2 min-w-0">

            <p className="font-semibold text-[14px] text-gray-900 truncate min-w-0">{asset.name}</p>
            <span className="text-xs px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded font-mono flex-shrink-0">
              {asset.size}
            </span>
            <span className="text-xs px-1.5 py-0.5 bg-blue-100 text-blue-900 font-medium rounded flex-shrink-0">
              {asset.origin_type}
            </span>
          </div>

          <p className="text-sm text-gray-500 mt-1 truncate">
            {asset.asset_url}
          </p>
        </div>
      </div>

      <AssetStatus status={status} label={label || ''} />
    </div>
  )
}

export default ListItem;

const AssetStatus = ({ status, label = '' }: AssetStatusDetails) => {
  const statusConfig = {
    enabled: {
      Icon: Power,
      label: __('Loaded Everywhere', 'wp-cloudflare-page-cache'),
      colorClass: "text-green-600"
    },
    disabled: {
      Icon: PowerOff,
      label: __('Not Loaded Anywhere', 'wp-cloudflare-page-cache'),
      colorClass: "text-red-600"
    },
    partially_disabled: {
      Icon: Lock,
      label: __('Loaded Only on Some Pages', 'wp-cloudflare-page-cache'),
      colorClass: "text-amber-600"
    },
  }

  const { colorClass, Icon, label: configLabel } = statusConfig[status]

  return (
    <div className={cn("flex items-center gap-2", colorClass)}>
      <span className="text-sm font-semibold">{label || configLabel}</span>
      <span className="flex items-center p-2">
        <Icon className="size-5" />
      </span>
    </div >
  )
}