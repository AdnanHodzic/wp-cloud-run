import Card, { CardContent, CardFooter, CardHeader } from "@/components/Card";
import Button from "@/components/Button";
import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import { spcApi } from "@/lib/api";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { DownloadIcon, Eye, Trash } from "lucide-react";
import { toast } from "sonner";

const AdvancedLog = () => {
  const { isToggleOn } = useSettingsStore();

  const controls = [
    {
      id: 'log_enabled',
      type: 'toggle',
      label: __('Log mode', 'wp-cloudflare-page-cache'),
      description: __('Enable this option if you want to log all activity of this plugin.', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'log_max_file_size',
      type: 'number',
      label: __('Max log file size in MB', 'wp-cloudflare-page-cache'),
      description: __('Automatically reset the log file when it exceeded the max file size. Set 0 to never reset it.', 'wp-cloudflare-page-cache'),
      hide: !isToggleOn('log_enabled'),
    },
    {
      id: 'log_verbosity',
      type: 'select',
      label: __('Log verbosity', 'wp-cloudflare-page-cache'),
      options: [
        { value: '1', label: __('Standard', 'wp-cloudflare-page-cache') },
        { value: '2', label: __('High', 'wp-cloudflare-page-cache') },
      ],
      hide: !isToggleOn('log_enabled'),
    }
  ]

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('Logs Settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <ControlsGroup controls={controls} />
      </CardContent>

      {isToggleOn('log_enabled') && (
        <CardFooter className="md:justify-end grid md:flex gap-4">
          <LogActions />
        </CardFooter>
      )}
    </Card >
  );
}

const LogActions = () => {
  const { logDownloadURL, logViewURL } = window.SPCDash;
  const { lockAsync, asyncLocked } = useAppStore();
  const [clearingLog, setClearingLog] = useState(false)

  const onLogClear = async () => {
    lockAsync(true);
    setClearingLog(true);

    const response = await spcApi.clearLogs();

    lockAsync(false);
    setClearingLog(false);

    if (response.success) {
      toast.success(response.message);
      return;
    }

    toast.error(response.message);
  }

  return (
    <>
      <Button
        variant="ghost"
        target="_blank"
        href={logDownloadURL}
        icon={DownloadIcon}
      >
        {__('Download Log File', 'wp-cloudflare-page-cache')}
      </Button>


      <Button
        variant="ghost"
        target="_blank"
        href={logViewURL}
        icon={Eye}
      >
        {__('View Log File', 'wp-cloudflare-page-cache')}
      </Button>

      <Button variant="destructive"
        loader={clearingLog}
        disabled={asyncLocked}
        onClick={onLogClear}
        icon={Trash}
      >
        {__('Clear logs now', 'wp-cloudflare-page-cache')}
      </Button>
    </>
  )
}

export default AdvancedLog;
