import Button from "@/components/Button";
import { useAppStore } from "@/store/store";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { spcApi } from "@/lib/api";
import { toast } from "sonner";

const DatabaseOptimizationButton = () => {
  const { i18n } = window.SPCDash;
  const [running, setRunning] = useState(false);
  const { asyncLocked, lockAsync } = useAppStore();

  const databaseOptimization = async () => {
    lockAsync(true);
    setRunning(true);

    const response = await spcApi.databaseOptimization();

    if (!response.success) {
      toast.error(response.message || i18n.genericError);
    } else {
      toast.success(response.message);
    }

    lockAsync(false);
    setRunning(false);
  }
  return (
    <Button
      variant="orange"
      onClick={databaseOptimization}
      disabled={running || asyncLocked}
      loader={running}
    >
      {running ?
        __('Optimizing Database', 'wp-cloudflare-page-cache') + '...'
        : __('Start Database Optimization', 'wp-cloudflare-page-cache')
      }
    </Button>
  );
}

export default DatabaseOptimizationButton;