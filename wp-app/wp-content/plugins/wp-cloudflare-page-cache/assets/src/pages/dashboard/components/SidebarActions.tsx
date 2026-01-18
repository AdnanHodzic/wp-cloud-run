import { useState } from "@wordpress/element";
import { Database, FlaskConical, Trash2 } from "lucide-react";


import CacheTestDialog from "@/common/CacheTestDialog";
import StatusDialog from "@/common/StatusDialog";
import Button from "@/components/Button";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { ApiResponse, spcApi } from "@/lib/api";
import { useAppStore } from "@/store/store";
import { __ } from "@wordpress/i18n";
import { toast } from "sonner";

const SidebarActions = () => {
  const { asyncLocked, lockAsync } = useAppStore();

  const [purging, setPurging] = useState(false);
  const [testing, setTesting] = useState(false);
  const [preloading, setPreloading] = useState(false);
  const [purgeDialogData, setDialogContent] = useState(null as ApiResponse | null);
  const [cacheTestDialogData, setCacheTestDialogData] = useState(null as ApiResponse | null);

  const onCachePurge = async () => {
    lockAsync(true);
    setPurging(true);
    const result = await spcApi.purgeCacheAll();
    setDialogContent(result);
    lockAsync(false);
    setPurging(false);
  }

  const onTestingStart = async () => {
    lockAsync(true);
    setTesting(true);
    const result = await spcApi.testCache();
    setCacheTestDialogData(result);
    lockAsync(false);
    setTesting(false);
  }

  const onPreloadStart = async () => {
    lockAsync(true);
    setPreloading(true);
    const response = await spcApi.startPreloader();
    lockAsync(false);
    setPreloading(false);

    if (!response.success) {
      toast.error(response.message);

      return;
    }
    toast.success(response.message);
  }

  return (
    <>
      {!!purgeDialogData && <StatusDialog
        onClose={() => setDialogContent(null)}
        description={purgeDialogData?.message}
        success={purgeDialogData?.success}
      />}

      {!!cacheTestDialogData && <CacheTestDialog
        onClose={() => setCacheTestDialogData(null)}
        data={cacheTestDialogData}
      />}


      <Card>
        <CardHeader>
          <h3 className="font-semibold text-base">
            {__('Actions', 'wp-cloudflare-page-cache')}
          </h3>
        </CardHeader>

        <CardContent className="grid xl:grid-cols-2 lg:grid-cols-1 sm:grid-cols-2 grid-cols-1 gap-3">
          <Button
            variant="destructive"
            className="xl:col-span-2 lg:col-span-1 sm:col-span-2"
            icon={Trash2}
            disabled={asyncLocked}
            loader={purging}
            onClick={onCachePurge}
          >
            {__('Purge Cache', 'wp-cloudflare-page-cache')}
          </Button>

          <Button
            variant="outline"
            icon={FlaskConical}
            disabled={asyncLocked}
            loader={testing}
            onClick={onTestingStart}
          >
            {__('Test Cache', 'wp-cloudflare-page-cache')}
          </Button>

          <Button
            variant="outline"
            icon={Database}
            disabled={asyncLocked}
            loader={preloading}
            onClick={onPreloadStart}
          >
            {preloading ?
              __('Preloading', 'wp-cloudflare-page-cache') + '...' :
              __('Preload', 'wp-cloudflare-page-cache')
            }
          </Button>
        </CardContent>
      </Card>
    </>
  );
};

export default SidebarActions;
