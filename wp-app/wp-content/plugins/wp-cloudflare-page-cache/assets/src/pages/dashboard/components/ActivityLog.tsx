import Button from "@/components/Button";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { spcApi } from "@/lib/api";
import { useSettingsStore } from "@/store/optionsStore";
import { useEffect, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Ban, BrushCleaning, RefreshCcw, AlertCircle } from "lucide-react";

const ActivityLog = () => {
  const { isToggleOn } = useSettingsStore();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);
  const [logs, setLogs] = useState([]);
  const { logViewURL } = window.SPCDash;
  const isLogEnabled = isToggleOn('log_enabled');

  const fetchLogs = async () => {
    setError(false);
    setLoading(true);
    setLogs([]);

    const result = await spcApi.getLogs();

    if (!result.success) {
      setLoading(false);
      setError(true);

      return;
    }

    setLogs(result.data as any[]);
    setLoading(false);
  };

  useEffect(() => {
    if (isLogEnabled) {
      fetchLogs();
    } else {
      setLoading(false);
    }
  }, [isLogEnabled]);


  const secondsToReadable = (seconds: number) => {
    if (seconds < 60) {
      return `${seconds}s`;
    }

    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) {
      return `${minutes}m`;
    }

    const hours = Math.floor(minutes / 60);
    if (hours < 24) {
      return `${hours}h`;
    }

    const days = Math.floor(hours / 24);
    return `${days}d`;
  };

  const refreshLogs = () => {
    fetchLogs();
  };

  return (
    <Card className="mb-6">
      <CardHeader className="flex items-center justify-between">
        <h3 className="font-semibold text-base">
          {__('Activity Log', 'wp-cloudflare-page-cache')}
        </h3>

        {isLogEnabled && (
          <div className="flex items-center gap-2">
            <Button icon={RefreshCcw} loader={loading} disabled={loading} size="icon" variant="ghost" className="size-8" onClick={refreshLogs} />

            <Button
              variant="link"
              size="sm"
              href={logViewURL}
              target="_blank"
              className="text-xs text-muted-foreground p-0"
            >
              {__('View All', 'wp-cloudflare-page-cache')}
            </Button>
          </div>
        )}
      </CardHeader>

      <CardContent className="p-0 divide-y divide-muted-foreground/10">
        {!isLogEnabled && (
          <div className="px-4 py-10 flex flex-col gap-4 items-center justify-center text-sm">
            <span className="p-3 rounded-full bg-muted-foreground/10">
              <AlertCircle className="size-12 text-muted-foreground" strokeWidth={1.5} />
            </span>
            <h4 className="text-muted-foreground font-semibold text-base">
              {__('Activity logging is currently not enabled.', 'wp-cloudflare-page-cache')}
            </h4>
          </div>
        )}

        {isLogEnabled && loading && (
          Array.from({ length: 20 }).map((_, idx) => (
            <div key={idx} className="px-4 py-3 flex items-center text-sm animate-pulse">
              <div className="size-1.5 rounded-full bg-muted-foreground/50 mr-3" />
              <div className="flex-1 h-5 bg-muted-foreground/30 rounded w-2/3" />
              <div className="h-3 w-14 bg-muted-foreground/30 rounded ml-4" />
            </div>
          ))
        )}

        {isLogEnabled && (!loading && logs.length > 0) && logs.map((log, idx) => (
          <div key={idx} className="px-4 py-3 flex items-center text-sm">
            <div className="size-1.5 rounded-full mr-3 bg-muted-foreground/50">
            </div>
            <div className="flex-1 min-w-0">
              <span className="text-foreground/80 break-all">{log.message}</span>
            </div>
            <span className="text-xs text-muted-foreground ml-4">{secondsToReadable(log.seconds_ago)} ago</span>
          </div>
        ))}

        {isLogEnabled && (!loading && logs.length === 0 && !error) && (
          <div className="px-4 py-10 flex flex-col gap-4 items-center justify-center text-sm">
            <span className="p-3 rounded-full bg-muted-foreground/10">
              <BrushCleaning className="size-12 text-muted-foreground" strokeWidth={1.5} />
            </span>
            <h4 className="text-muted-foreground font-semibold text-base">
              {__('No logs found', 'wp-cloudflare-page-cache')}
            </h4>

            <p className="text-muted-foreground text-sm">
              {__('The activity log will appear here once you start using the plugin.', 'wp-cloudflare-page-cache')}
            </p>
          </div>
        )}

        {isLogEnabled && (!loading && error) && (
          <div className="px-4 py-10 flex flex-col gap-4 items-center justify-center text-sm">
            <span className="p-3 rounded-full bg-muted-foreground/10">
              <Ban className="size-12 text-muted-foreground" strokeWidth={1.5} />
            </span>

            <h4 className="text-muted-foreground font-semibold text-base">
              {__('Error fetching logs', 'wp-cloudflare-page-cache')}
            </h4>

            <p className="text-muted-foreground text-sm">
              {__('An error occurred while fetching the activity log. Please try again later.', 'wp-cloudflare-page-cache')}
            </p>
          </div>
        )}
      </CardContent>
    </Card >
  )
};

export default ActivityLog;