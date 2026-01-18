import TransitionWrapper from "@/common/TransitionWrapper";
import { Switch } from "@/components/ui/switch";
import { cn } from "@/lib/utils";
import { useAssetManagerStore } from "@/store/assetManagerStore";
import { __ } from "@wordpress/i18n";
import { AlertTriangle, Archive, FileText, FolderOpen, Globe, Home, Search, Tag, User } from "lucide-react";

type contextControlProps = {
  asset: Record<string, any>,
  context: Record<string, any>,
}

const ContextControl = ({ asset, context }: contextControlProps) => {
  const { asyncLocked } = useAssetManagerStore();
  const { currentContext } = window.SPCAssetManager;

  const isContextRedundant = (asset, contextKey) => {
    // If global is selected, all other location contexts are redundant
    if (contextKey !== 'global' && asset?.locationContexts?.includes('global')) {
      return true;
    }
    return false;
  };

  const getIcon = (context) => {
    let icon = User;
    if (context.category === 'global') {
      icon = Globe;
    }

    if (context.category === 'singular') {
      icon = FileText;
    }

    if (context.category === 'archive') {
      if (currentContext.subType === 'is_tax') {
        icon = currentContext.taxonomyType === 'category' ? FolderOpen : Tag;
      } else if (currentContext.subType === 'is_author') {
        icon = User;
      } else {
        icon = Archive;
      }
    }

    if (context.category === 'special') {
      if (currentContext.pageType === 'is_search') {
        icon = Search;
      } else if (currentContext.pageType === 'is_404') {
        icon = AlertTriangle;
      } else if (currentContext.pageType === 'is_front_page' || currentContext.pageType === 'is_home') {
        icon = Home;
      }
    }

    return icon;
  }

  return (
    <TransitionWrapper from="fade">
      <div className="flex items-center mb-4 text-sm font-medium">
        <context.icon className="w-4 h-4 mr-2" />
        <h5 className="text-gray-700">
          {context.title}
        </h5>
      </div>
      <div className="space-y-3">
        {context.contexts.map((ctx) => {
          const isDisabled = asset[context.key].includes(ctx.saveAs);
          const isRedundant = isContextRedundant(asset, ctx.key);
          const IconComponent = getIcon(ctx);

          return (
            <div key={ctx.key} className={cn("min-w-0 flex items-center gap-5 py-2 px-3 rounded-lg border bg-white border-gray-200 hover:border-gray-300", {
              'opacity-60': isRedundant,
            })}>
              <IconComponent className="size-4 shrink-0" />
              <div className="min-w-0">
                <div className="font-medium text-sm text-gray-900 flex items-center gap-2">
                  {ctx.label}
                  {isRedundant && (
                    <TransitionWrapper className="text-xs px-1.5 py-0.5 bg-gray-300 text-gray-900 rounded">
                      {__('Redundant', 'wp-cloudflare-page-cache')}
                    </TransitionWrapper>
                  )}
                </div>
                <div className="text-xs text-gray-500 truncate mt-1">
                  {ctx.description}
                </div>
              </div>

              <Switch
                checked={!isDisabled}
                onCheckedChange={(value) => context.handleToggle(ctx.key, value)}
                disabled={isRedundant || asyncLocked}
                className="text-sm ml-auto"
              />

            </div>
          );
        })}
      </div>
    </TransitionWrapper>
  )
}

export default ContextControl;