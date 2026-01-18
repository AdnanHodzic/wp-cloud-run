import LicenseCard from "@/common/LicenseCard";
import TransitionWrapper from "@/common/TransitionWrapper";
import Button from "@/components/Button";
import Card, { CardContent, CardHeader } from "@/components/Card";
import Notice from "@/components/Notice";
import Container from "@/layout/Container";
import Header from "@/layout/Header";
import PageWrap from "@/layout/PageWrap";
import { spcApi } from "@/lib/api";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { ArrowDown, BarChart3, CheckCircle, Info, Play, Rocket, Shield } from "lucide-react";
import { toast } from "sonner";

const OnboardingCard = () => {
  const { i18n, isPro } = window.SPCDash;
  const { lockAsync, asyncLocked, setShowWizard, validLicense } = useAppStore();
  const { updateSettings } = useSettingsStore();

  const performanceImprovements = [
    {
      title: __('Load Time', 'wp-cloudflare-page-cache'),
      from: '3.2s',
      to: '0.8s',
      percentage: '+75%'
    },
    {
      title: __('Server Response', 'wp-cloudflare-page-cache'),
      from: '850ms',
      to: '124ms',
      percentage: '+85%'
    },
    {
      title: __('Bandwidth', 'wp-cloudflare-page-cache'),
      from: '2.1GB',
      to: '0.9GB',
      percentage: '+57%'
    },
    {
      title: __('Page Speed Score', 'wp-cloudflare-page-cache'),
      from: '42',
      to: '94',
      percentage: '+124%'
    }
  ];

  const whatGetsActivated = [
    {
      title: __('Page Caching', 'wp-cloudflare-page-cache'),
      description: __('Static HTML generation', 'wp-cloudflare-page-cache')
    },
    {
      title: __('Browser Caching', 'wp-cloudflare-page-cache'),
      description: __('Client-side resource caching', 'wp-cloudflare-page-cache')
    },
    {
      title: __('GZIP Compression', 'wp-cloudflare-page-cache'),
      description: __('Automatic file compression', 'wp-cloudflare-page-cache')
    },
    {
      title: __('Cache Preloading', 'wp-cloudflare-page-cache'),
      description: __('Automatic cache warming', 'wp-cloudflare-page-cache')
    },
    {
      title: __('Mobile Optimization', 'wp-cloudflare-page-cache'),
      description: __('Device-specific caching', 'wp-cloudflare-page-cache')
    },
    {
      title: __('CDN Integration', 'wp-cloudflare-page-cache'),
      description: __('Global content delivery', 'wp-cloudflare-page-cache')
    }
  ];

  const [loading, setLoading] = useState(false);

  const handleClick = async () => {
    lockAsync(true);
    setLoading(true);

    const response = await spcApi.enablePageCache();

    if (!response.success) {
      lockAsync(false);
      setLoading(false);

      toast.error(i18n.error, {
        description: response.message || i18n.genericError,
      });

      return;
    }

    toast.success(response.message);

    updateSettings(response.data.settings);

    setLoading(false);
    lockAsync(false);
    setShowWizard(false);
  }

  return (
    <Card>
      <CardHeader className="p-6 bg-orange-50 border-orange-200 dark:bg-orange-800/30 dark:border-orange-700/50">
        <div className="flex items-center justify-between">
          <div className="flex items-center">
            <div className="size-10 text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/50 border border-orange-100 dark:border-orange-700/50 rounded-lg flex items-center justify-center mr-4">
              <Rocket className="size-5" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-foreground">
                {__('Ready to Speed Up Your Website?', 'wp-cloudflare-page-cache')}
              </h2>
              <p className="text-sm text-foreground/70 mt-1">
                {__('Activate intelligent caching to improve your site\'s performance automatically', 'wp-cloudflare-page-cache')}
              </p>
            </div>
          </div>

          {(!isPro || validLicense) && (
            <div className="flex flex-col items-end space-y-2">
              <Button
                variant="cta"
                onClick={handleClick}
                disabled={loading || asyncLocked}
                loader={loading}
                icon={Play}
              >
                {__('Enable Caching', 'wp-cloudflare-page-cache')}
              </Button>
              <p className="text-xs text-gray-500 text-center max-w-48">
                <span className="inline-flex items-center text-green-600 dark:text-green-400">
                  <CheckCircle className="size-3 mr-1" />
                  {__('Free forever', 'wp-cloudflare-page-cache')}
                </span>
                <span className="inline-flex items-center text-green-600 dark:text-green-400">
                  <CheckCircle className="size-3 mr-1" />
                  {__('No setup required', 'wp-cloudflare-page-cache')}
                </span>
              </p>
            </div>
          )}
        </div>
      </CardHeader>
      <CardContent>
        {(isPro && !validLicense) && <LicenseCard />}

        <div className="mb-8">
          <h3 className="text-lg font-semibold text-foreground mb-2 flex items-center">
            <BarChart3 className="size-5 mr-2 text-orange-600" />
            {__('Expected Performance Improvements', 'wp-cloudflare-page-cache')}
          </h3>
          <p className="text-sm text-muted-foreground mb-4">
            {__('Based on average results from our users. Individual results may vary depending on your site\'s content and configuration.', 'wp-cloudflare-page-cache')}
          </p>

          <div className="grid grid-cols-2 lg:grid-cols-4 gap-6">
            {performanceImprovements.map((improvement, index) => (
              <div key={index} className="text-center p-4 bg-muted rounded-lg">
                <div className="text-sm font-medium text-foreground/70 mb-3">
                  {improvement.title}
                </div>
                <div className="space-y-2">
                  <div className="text-sm text-muted-foreground/70 line-through">
                    {improvement.from}
                  </div>
                  <ArrowDown className="size-4 text-muted-foreground/70 mx-auto" />
                  <div className="text-xl font-bold text-foreground/80">
                    {improvement.to}
                  </div>
                </div>

                <div className="text-xs border border-transparent dark:border-green-800/50 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 font-medium mt-2 px-2 py-1 rounded">
                  {improvement.percentage}
                </div>
              </div>
            ))}
          </div>
        </div>


        <h3 className="text-lg font-semibold text-foreground mb-4 flex items-center">
          <Shield className="size-5 mr-2 text-orange-600" />
          {__('What Gets Activated (Zero Configuration Required)', 'wp-cloudflare-page-cache')}
        </h3>

        <div className="grid md:grid-cols-2 gap-4">
          {whatGetsActivated.map((item, index) => (
            <div key={index} className="flex items-start p-3 bg-muted rounded-lg">
              <CheckCircle className="size-5 text-green-500 mr-3 mt-0.5" />
              <div>
                <div className="text-sm font-medium text-foreground">{item.title}</div>
                <div className="text-xs text-muted-foreground">{item.description}</div>
              </div>
            </div>
          ))}
        </div>

        <Notice
          type="orange"
          icon={Info}
          className="mt-6"
          title={__('Safe & Reliable', 'wp-cloudflare-page-cache')}
          description={__('Our caching system is designed to work automatically with smart defaults. You can customize settings anytime or disable with a single click if needed.', 'wp-cloudflare-page-cache')}
        />

      </CardContent>
    </Card>
  )
}

const StartWizard = () => {
  return (
    <PageWrap>
      <Header backButton={false} />

      <Container className="max-w-5xl py-8">
        <TransitionWrapper from="bottom">
          <OnboardingCard />
        </TransitionWrapper>
      </Container>
    </PageWrap>
  )
}

export default StartWizard;