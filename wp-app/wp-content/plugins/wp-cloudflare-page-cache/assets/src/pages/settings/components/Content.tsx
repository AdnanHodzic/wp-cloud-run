import { NAV_ITEMS_IDS } from "@/lib/constants";
import { cn } from "@/lib/utils";
import SettingCardPlaceholder from "@/pages/placeholders/SettingCardPlaceholder";
import Cache from "@/pages/settings/content/Cache";
import Cloudflare from "@/pages/settings/content/Cloudflare";
import Assets from "@/pages/settings/content/Assets";
import Media from "@/pages/settings/content/Media";
import ThirdParty from "@/pages/settings/content/ThirdParty";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { lazy, Suspense } from "@wordpress/element";
import { ConnectionProvider } from "../content/cloudflare/connectionStore";
import { DatabaseOptimization } from "@/pages/settings/content/DatabaseOptimization";

const Advanced = lazy(() => import('../content/Advanced'));

const Content = ({ className = "" }: { className?: string }) => {

  const { activeMenuItem } = useAppStore();
  const { isToggleOn } = useSettingsStore();

  return (
    <div className={cn(
      "flex flex-col w-full",
      className,
      `content-wrap-${activeMenuItem.toLowerCase()}`
    )}>

      {activeMenuItem === NAV_ITEMS_IDS.GENERAL && (<Cache />)}
      {activeMenuItem === NAV_ITEMS_IDS.CLOUDFLARE && (<ConnectionProvider><Cloudflare /></ConnectionProvider>)}
      {activeMenuItem === NAV_ITEMS_IDS.ASSETS && (<Assets />)}
      {activeMenuItem === NAV_ITEMS_IDS.MEDIA && (<Media />)}
      {activeMenuItem === NAV_ITEMS_IDS.COMPATIBILITIES && (<ThirdParty />)}
      {activeMenuItem === NAV_ITEMS_IDS.DATABASE_OPTIMIZATION && (<DatabaseOptimization />)}
      {isToggleOn('show_advanced') && (
        <Suspense fallback={<SettingCardPlaceholder />}>
          {activeMenuItem === NAV_ITEMS_IDS.ADVANCED && (<Advanced />)}
        </Suspense>
      )}

    </div>
  )
}

export default Content;