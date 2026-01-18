import { cn } from "@/lib/utils";
import { __ } from "@wordpress/i18n";
import { AlertTriangle, Lock, LucideIcon, Power, PowerOff } from "lucide-react";

interface AssetStatusProps {
  text: string;
  icon: LucideIcon;
  className: string;
}

const Legend = () => {
  const assestStatuses: AssetStatusProps[] = [
    {
      icon: Power,
      text: __("Enabled", "wp-cloudflare-page-cache"),
      className: "text-green-500"
    },
    {
      icon: PowerOff,
      text: __("Disabled", "wp-cloudflare-page-cache"),
      className: "text-red-500"
    },
    {
      icon: Lock,
      text: __("Partially", "wp-cloudflare-page-cache"),      
      className: "text-yellow-500"
    },
  ];

  return (
    <div className="px-4 py-2 bg-muted border-t border-gray-200">
      <div className="flex items-center space-x-3 text-muted-foreground text-xs font-medium">
        {assestStatuses.map(({ className, icon: Icon, text }, index) => {
          return (
            <div key={index} className="flex items-center gap-1">
              <Icon className={cn(className, "size-3")} />
              <span>{text}</span>
            </div>
          )
        })}
        <div className="flex items-center space-x-1 ml-auto">
          <AlertTriangle className="size-4 text-yellow-600" />
          <span>{__('Changes take effect immediately after saving', 'wp-cloudflare-page-cache')}</span>
        </div>
      </div>
    </div>
  )
}

export default Legend;