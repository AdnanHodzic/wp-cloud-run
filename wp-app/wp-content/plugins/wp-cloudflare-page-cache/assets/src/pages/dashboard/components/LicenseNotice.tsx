import Button from "@/components/Button";
import Notice from "@/components/Notice";
import { LINKS, ROOT_PAGES } from "@/lib/constants";
import { getUpsellURL } from "@/lib/utils";
import { useAppStore } from "@/store/store";
import { __ } from "@wordpress/i18n";
import { KeySquare } from "lucide-react";

const LicenseNotice = () => {
  const { setRootPage } = useAppStore();

  return (
    <Notice
      type="warning"
      className="mb-6"
      title={__('Pro Features Available - License Required', 'wp-cloudflare-page-cache')}
      description={__('You have Super Page Cache Pro installed but need to activate your license to unlock advanced features like JavaScript optimization, database caching, and marketing parameter ignoring.', 'wp - cloudflare - page - cache')}
    >
      <div className="flex items-center gap-3">
        <Button onClick={() => setRootPage(ROOT_PAGES.LICENSE)} variant="orange" size="xs">
          <KeySquare className="size-3 mr-1" />
          {__('Enter License Key', 'wp-cloudflare-page-cache')}
        </Button>
        <Button href={LINKS.STORE} target="_blank" variant="link" size="xs" className="text-orange-600 dark:text-orange-400 hover:text-orange-700 p-0">
          {__('Find my license key', 'wp-cloudflare-page-cache')}
        </Button>
        <Button href={getUpsellURL('dashboard-license-notice')} target="_blank" variant="link" size="xs" className="text-orange-600 dark:text-orange-400 hover:text-orange-700 p-0">
          {__('Purchase license', 'wp-cloudflare-page-cache')}
        </Button>
        <Button onClick={() => setRootPage(ROOT_PAGES.HELP)} variant="link" size="xs" className="text-orange-600 dark:text-orange-400 hover:text-orange-700 p-0">
          {__('Get Help', 'wp-cloudflare-page-cache')}
        </Button>
      </div>
    </Notice >
  )
};

export default LicenseNotice;