import { useAppStore } from "@/store/store";

import { SETTINGS_NAV_MENU_ITEMS, NAV_ITEMS_IDS } from "@/lib/constants";
import { useSettingsStore } from "@/store/optionsStore";

export function useNav() {
  const { activeMenuItem, setActiveMenuItem } = useAppStore();
  const { settings } = useSettingsStore();
  const { thirdPartyVisible } = window.SPCDash;

  const navItems = SETTINGS_NAV_MENU_ITEMS.filter(({ id }) => {
  
    if (id === NAV_ITEMS_IDS.ADVANCED) {
      return Boolean(settings['show_advanced']);
    }
    if (id === NAV_ITEMS_IDS.COMPATIBILITIES) {
      return Boolean(thirdPartyVisible);
    }
    return true;
  });

  return { setActiveMenuItem, activeMenuItem, navItems };
}
