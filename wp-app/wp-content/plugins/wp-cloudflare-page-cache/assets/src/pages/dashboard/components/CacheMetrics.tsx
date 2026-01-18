import ViewCachedPages from "@/common/CachedPagesDrawer";
import TransitionWrapper from "@/common/TransitionWrapper";
import Card, { CardContent } from "@/components/Card";
import { formatBytes } from "@/lib/utils";
import { useDashboardStore } from "@/store/dashboardStore";

import { useSettingsStore } from "@/store/optionsStore";
import { __ } from "@wordpress/i18n";
import { Clock, Database, HardDrive, LucideIcon, TrendingUp } from "lucide-react";

const CacheMetrics = () => {
  const { analyticsData, analyticsAvailable, loadingAnalytics } = useDashboardStore();

  const { size } = window.SPCDash.metrics['cache.size'];
  const { ratio } = window.SPCDash.metrics['cache.hitmiss'];
  const { ttfb_ms } = window.SPCDash.metrics['cache.ttfb'];
  const { html_files } = window.SPCDash.metrics['cache.files'];


  const hitRateData = {
    title: __('Disk Cache Hit Rate', 'wp-cloudflare-page-cache'),
    value: ratio !== 'n/a' ? ratio + '%' : 'n/a',
    icon: TrendingUp,
    className: "delay-100"
  };

  if (analyticsAvailable && analyticsData) {
    hitRateData.title = __('Cloudflare Percent Cached', 'wp-cloudflare-page-cache');
    hitRateData.value = analyticsData?.bytes > 0 
      ? (analyticsData?.cachedBytes / analyticsData?.bytes * 100).toFixed(2) + '%' 
      : 'n/a';
  }

  const data = {
    hitRate: hitRateData,
    cachedObjects: {
      title: __('Cached Objects', 'wp-cloudflare-page-cache'),
      value: html_files !== 'n/a' ? html_files : 'n/a',
      icon: Database,
      className: "delay-200"
    },
    cacheSize: {
      title: __('Cache Size', 'wp-cloudflare-page-cache'),
      value: size !== 'n/a' ? formatBytes(size) : 'n/a',
      icon: HardDrive,
      className: "delay-300"
    },
    ttfb: {
      title: __('Avg Response', 'wp-cloudflare-page-cache'),
      value: ttfb_ms !== 'n/a' ? ttfb_ms + 'ms' : 'n/a',
      icon: Clock,
      className: "delay-400"
    }
  }

  return (
    <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      {Object.entries(data).map(([key, item]) => {
        return (
          <CacheMetricsBox 
            loading={loadingAnalytics && key === 'hitRate'}
            key={key}
            id={key}
            title={item.title} 
            value={item.value.toString()} 
            icon={item.icon}
            className={item.className} 
          />
        );
      })}
    </div>
  )
};

const CacheMetricsBoxSkeleton = () => {
  return (
    <div className="bg-background rounded-lg border p-4">
      <div className="flex items-center justify-between mb-3">
        <div className="w-16 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
        <div className="w-6 h-6 bg-muted-foreground/50 rounded animate-pulse"></div>
      </div>
      <div className="w-20 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
    </div>
  )
}

type CacheMetricsBoxProps = {
  id: string;
  title: string;
  value: string;
  icon: LucideIcon;
  className?: string;
  loading?: boolean;
}

const CacheMetricsBox = ({id, title, value, icon, className = '', loading = false}: CacheMetricsBoxProps) => {
  const { isToggleOn } = useSettingsStore();

  const Icon = icon;

  if (loading) {
    return <CacheMetricsBoxSkeleton />;
  }

  return (
    <TransitionWrapper from="fade" className={className}>
      <Card>
        <CardContent>
          <div className="flex items-center justify-between mb-2">
            <span className="text-xs font-medium text-foreground/80 uppercase tracking-wide">{title}</span>
            <Icon className="w-4 h-4 text-muted-foreground shrink-0" />
          </div>
          <div className="flex items-end justify-between">
            <span className="text-xl font-bold text-foreground">{value}</span>

            {isToggleOn('cf_purge_only_html') && id === 'cachedObjects' && (
              <ViewCachedPages/>
            )} 
          </div>
        </CardContent>
      </Card>
    </TransitionWrapper>
  )
}

export default CacheMetrics;