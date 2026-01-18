import Card, { CardContent, CardHeader } from "@/components/Card";
import { cn } from "@/lib/utils";
import { useSettingsStore } from "@/store/optionsStore";
import { __ } from "@wordpress/i18n";

const SidebarSystemStatus = () => {
  const { hasOverdueJobs } = window.SPCDash;
  const { settings, cloudflareConnected } = useSettingsStore();

  const data = [
    {
      label: __('Cache Engine', 'wp-cloudflare-page-cache'),
      value: settings.cf_fallback_cache ?
        __('Enabled', 'wp-cloudflare-page-cache') :
        __('Disabled', 'wp-cloudflare-page-cache'),
      color: settings.cf_fallback_cache ?
        'text-green-600 dark:text-green-400' :
        'text-destructive'
    },
    {
      label: __('Background Tasks', 'wp-cloudflare-page-cache'),
      value: hasOverdueJobs ?
        __('Overdue', 'wp-cloudflare-page-cache') :
        __('Running', 'wp-cloudflare-page-cache'),
      color: hasOverdueJobs ?
        'text-destructive' :
        'text-green-600 dark:text-green-400'
    },
    {
      label: __('Cloudflare Caching', 'wp-cloudflare-page-cache'),
      value: cloudflareConnected ?
        __('Active', 'wp-cloudflare-page-cache') :
        __('Inactive', 'wp-cloudflare-page-cache'),
      color: cloudflareConnected ? 'text-green-600 dark:text-green-400' : 'text-destructive'
    }
  ];
  return (
    <Card>
      <CardHeader>
        <h3 className="font-semibold text-base">
          {__('System', 'wp-cloudflare-page-cache')}
        </h3>
      </CardHeader>
      <CardContent className="space-y-3">

        {data.map((item) => (
          <div
            key={item.label}
            className="flex items-center justify-between text-sm"
          >
            <span className="text-foreground/80">
              {item.label}
            </span>
            <span className={cn('font-medium', item.color)}>
              {item.value}
            </span>
          </div>
        ))}

      </CardContent>
    </Card>
  )
}

export default SidebarSystemStatus;