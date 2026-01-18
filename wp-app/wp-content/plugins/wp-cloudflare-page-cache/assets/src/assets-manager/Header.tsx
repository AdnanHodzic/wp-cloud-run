import Button from "@/components/Button";
import Notice from "@/components/Notice";
import { useAssetManagerStore } from "@/store/assetManagerStore";
import { createInterpolateElement } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Info, X, Zap } from "lucide-react";
import PageContextInfo from "./PageContextInfo";
import Tabs from "./Tabs";

const Header = () => {
  const { closeModal, asyncLocked } = useAssetManagerStore();

  return (
    <>
      <div className="flex items-center justify-between p-4 bg-gradient-to-r from-orange-500 to-red-500 text-white border-b border-orange-500">

        <div className="flex items-center gap-2">
          <div className="size-8 bg-white/20 rounded-lg flex items-center justify-center">
            <Zap className="size-5" />
          </div>
          <div>
            <h2 className="text-xl font-semibold">{__('Asset Manager', 'wp-cloudflare-page-cache')}</h2>
            <p className="text-sm text-orange-100">{__('Control CSS & JavaScript loading on specific contexts', 'wp-cloudflare-page-cache')}</p>
          </div>
        </div>

        <Button
          variant="ghost"
          onClick={closeModal}
          size="icon"
          disabled={asyncLocked}
          className="text-white hover:bg-orange-500/50 hover:text-white border-none"
          icon={X}
        >
          <span className="sr-only">{__('Close Modal', 'wp-cloudflare-page-cache')}</span>
        </Button>
      </div>

      <PageContextInfo />

      <div className="p-3 border-b border-gray-200 bg-muted grid gap-2">
        <Tabs />

        <Notice
          type="info"
          icon={Info}
        >
          {createInterpolateElement(
            __("<strong>How it works:</strong> When you disable an asset for a context, it won't load on those pages. User filters can be combined with location filters for fine-grained control.", 'wp-cloudflare-page-cache'),
            {
              strong: <strong className="font-semibold" />,
            }
          )}
        </Notice>
      </div>
    </>
  )
}

export default Header;