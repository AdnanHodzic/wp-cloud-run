import Button from "@/components/Button";
import Notice from "@/components/Notice";
import { spcApi } from "@/lib/api";
import { LINKS } from "@/lib/constants";
import { cn } from "@/lib/utils";
import { createInterpolateElement, useState } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";
import { Settings } from "lucide-react";
import { toast } from "sonner";

const PluginConflictsNotice = () => {
  const {conflicts }= window.SPCDash;

  const [isDismissed, setIsDismissed] = useState(false);
  const [isVisible, setIsVisible] = useState(true);

  const text = createInterpolateElement(
    sprintf(
      /* translators: %s: Plugin name/names */
      __('We also detected %s active on your site. Running multiple caching plugins simultaneously may cause conflicts and reduce performance.', 'wp-cloudflare_page_cache'),
      conflicts.map(conflict => `<strong>${conflict}</strong>`).join(', ')
    ), {
      strong: <strong />
    });

  const handleDismiss = async () => {
    const response = await spcApi.dismissNotice('conflicts');

    if (response.success) {
      setIsVisible(false);
      window.SPCDash.conflicts = [];

      setTimeout(() => {
        setIsDismissed(true);
      }, 300);

      return;
    }

    toast.error(response.message);
  }

  if (isDismissed) {
    return null;
  }

  return (
    <Notice
      type="warning"
      className={cn("mb-6 transition-all duration-300", {
        "opacity-0 overflow-hidden": !isVisible,
        "opacity-100": isVisible,
      })} 
      title={__('Multiple Caching Plugins Detected', 'wp-cloudflare_page_cache')}
      onDismiss={handleDismiss}
    >
      <span className="block">
        {text}  
      </span>
      <span className="block mt-1">
        {__('For the best results, we recommend temporarily disabling other caching plugins to test Super Page Cache performance, or choose one primary caching solution.', 'wp-cloudflare_page_cache')}
      </span>

      <Button href={LINKS.PLUGINS_PAGE} target="_blank" size="xs" variant="orange" className="mt-2 bg-yellow-600 hover:bg-yellow-700 border-yellow-600">
        <Settings className="size-3 mr-1" />
        {__('Manage Plugins', 'wp-cloudflare-page-cache')}
      </Button>
    </Notice>
  );
};

export default PluginConflictsNotice;