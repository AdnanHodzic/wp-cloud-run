import { useSettingsStore } from "@/store/optionsStore";

const CoreDashboardMenuHandler = () => {
  const { rootPagePrefix } = window.SPCDash;
  const { settings } = useSettingsStore();

  const isCacheEnabled = settings.cf_cache_enabled;

  const css = `
      #toplevel_page_super-page-cache li:has(a[href*="${rootPagePrefix}settings"]),
      #toplevel_page_super-page-cache li:has(a[href*="${rootPagePrefix}import-export"]),
      #toplevel_page_super-page-cache li:has(a[href*="${rootPagePrefix}license"]) {
        display: ${isCacheEnabled ? 'list-item' : 'none'};
      }
    `;

  return <style dangerouslySetInnerHTML={{ __html: css }} />;
};

export default CoreDashboardMenuHandler;