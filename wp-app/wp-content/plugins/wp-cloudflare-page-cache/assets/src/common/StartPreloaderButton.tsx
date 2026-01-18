import { useAppStore } from "@/store/store";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { toast } from "sonner";
import Button from "@/components/Button";
import { spcApi } from "@/lib/api";

const StartPreloaderButton = () => {
  const [running, setRunning] = useState(false);
  const { asyncLocked, lockAsync } = useAppStore();

  const handlePreloadStart = async () => {
    lockAsync(true);
    setRunning(true);

    const response = await spcApi.startPreloader();

    lockAsync(false);
    setRunning(false);

    if (!response.success) {
      toast.error(response.message);

      return;
    }
    toast.success(response.message);
  }

  return (
    <Button
      variant="orange"
      onClick={handlePreloadStart}
      disabled={running || asyncLocked}
      loader={running}
    >
      {running ?
        __('Starting Preloader', 'wp-cloudflare-page-cache') + '...'
        : __('Start Preloader', 'wp-cloudflare-page-cache')
      }
    </Button>
  )
}

export default StartPreloaderButton;