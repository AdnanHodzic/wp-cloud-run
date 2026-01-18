import Select from "@/common/Select";
import TransitionWrapper from "@/common/TransitionWrapper";
import Button from "@/components/Button";
import Card, { CardContent, CardHeader } from "@/components/Card";
import Notice from "@/components/Notice";
import PageContent from "@/layout/PageContent";
import { CF_AUTH_MODES, LINKS } from "@/lib/constants";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";

import { ExternalLinkIcon, Globe, Wifi, WifiOff, Wrench } from "lucide-react";

import ApiKeyForm from "./cloudflare/ApiKeyForm";
import ApiTokenForm from "./cloudflare/ApiTokenForm";
import ZoneIdConnect from "./cloudflare/ZoneIdConnect";
import { useConnectionStore } from "./cloudflare/connectionStore";

import { cn } from "@/lib/utils";
import { createInterpolateElement, useEffect, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import ControlsGroup from "../controls/ControlsGroup";
import Separator from "@/common/Separator";
import ExternalLink from "@/common/ExternalLink";
import { spcApi } from "@/lib/api";
import { toast } from "sonner";

const CF_AUTH_MODE_OPTIONS = [
  {
    label: __('API Key', 'wp-cloudflare-page-cache'),
    value: CF_AUTH_MODES.API_KEY.toString()
  },
  {
    label: `${__('API Token', 'wp-cloudflare-page-cache')} (${__('Recommended', 'wp-cloudflare-page-cache')})`,
    value: CF_AUTH_MODES.API_TOKEN.toString()
  }
];

const ConnectionError = () => {
  const { errorMessage } = useConnectionStore();

  if (!errorMessage) {
    return null;
  }

  const errAsArray = errorMessage.split(':');
  let errorHeader = __('Connection Failed:', 'wp-cloudflare-page-cache');

  // Use whatever is after the first `:` but include subsequent text.
  let displayMessage = <>{errAsArray.slice(1).join(':').trim()}</>;

  if (errorMessage === 'permission_error') {
    errorHeader = __('Permission Error:', 'wp-cloudflare-page-cache');
    displayMessage = createInterpolateElement(__('Please make sure you grant the token permissions as outlined in <a>this documentation</a>.', 'wp-cloudflare-page-cache'), {
      a: <ExternalLink url="https://docs.themeisle.com/article/2077-super-page-cache-cloudflare-permissions" className="underline text-red-700 font-semibold" />
    })
  }

  return (
    <TransitionWrapper from="top">
      <Notice type="error" className="rounded-none border-b border-x-0 border-t-0">
        <strong className="mr-1">
          {errorHeader}
        </strong>
        <span>
          {displayMessage}
        </span>
      </Notice>
    </TransitionWrapper>
  )
}

const Cloudflare = () => {
  const { homeURL, ruleNeedsRepair, i18n } = window.SPCDash;

  const {
    alreadyOnCloudflare,
    setAlreadyOnCloudflare,
    cloudflareCheckDone,
    setCloudflareCheckDone,
  } = useConnectionStore();

  const {
    asyncLocked,
    lockAsync,
  } = useAppStore();

  const {
    cloudflareConnected,
    settings
  } = useSettingsStore();

  const [authMode, setAuthMode] = useState(parseInt(settings.cf_auth_mode as string));

  const checkCloudflare = async () => {
    const response = await fetch(`${homeURL}/cdn-cgi/trace`);

    setCloudflareCheckDone(true);

    if (!response.ok || response.status !== 200) {
      return;
    }

    const text = await response.text();

    if (text.includes('h=') && text.includes('ip=')) {
      setAlreadyOnCloudflare(true);

      return;
    }
  };

  useEffect(() => {
    if (cloudflareCheckDone) {
      return;
    }

    checkCloudflare();
  }, []);

  const displayAccountCreationNotice = !alreadyOnCloudflare && !cloudflareConnected && !settings.cf_zoneid_list;
  const hasZoneIDList = !!settings.cf_zoneid_list && Object.keys(settings.cf_zoneid_list).length > 0;

  const [isRepairingRule, setIsRepairingRule] = useState(false);

  const fixCacheRule = async () => {
    lockAsync(true);
    setIsRepairingRule(true);

    const response = await spcApi.repairCloudflareRule();

    if (response.success) {
      toast.success(response.message, {
        description: __('Page will reload in a few seconds.', 'wp-cloudflare-page-cache'),
      });

      setTimeout(() => {
        window.location.reload();
      }, 3000);

      return;
    }

    setIsRepairingRule(false);
    lockAsync(false);
    toast.error(response.message, {
      description: __('Something went wrong. Please reload the page and try again. If the issue persists, disconnect and reconnect your Cloudflare account.', 'wp-cloudflare-page-cache'),
    });
  }

  return (
    <PageContent>

      {ruleNeedsRepair && (
        <Notice type="warning"
          title={i18n.ruleFixTitle}
          description={i18n.ruleFixDescription}
        >
          <Button variant="orange" size="sm" className="rounded-sm" onClick={fixCacheRule} disabled={asyncLocked} loader={isRepairingRule} icon={Wrench}>
            {isRepairingRule ? __('Fixing Rule', 'wp-cloudflare-page-cache') + '...' : __('Fix Rule', 'wp-cloudflare-page-cache')}
          </Button>
        </Notice>
      )}

      <Card>

        <CardHeader className="bg-blue-50 border-blue-200 dark:bg-blue-950 dark:border-blue-800">
          <div className="flex items-center">
            <div className={cn("size-8 rounded-lg flex items-center justify-center mr-3", {
              'bg-green-100 dark:bg-green-900/50': cloudflareConnected,
              'bg-muted': !cloudflareConnected,
            })}>
              {cloudflareConnected ?
                <Wifi className="size-4 text-green-600 dark:text-green-400" /> :
                <WifiOff className="size-4 text-gray-400 dark:text-gray-500" />
              }
            </div>
            <div>
              <h3 className="font-semibold text-base text-foreground">{__('Cloudflare Connection', 'wp-cloudflare-page-cache')}</h3>
              <p className="text-sm text-muted-foreground">{__('Connect your Cloudflare account to enable CDN features', 'wp-cloudflare-page-cache')}</p>
            </div>
          </div>
        </CardHeader>

        <ConnectionError />

        <CardContent className="p-0">
          {!hasZoneIDList && (
            <div className="p-4">

              {displayAccountCreationNotice && (
                <Notice
                  type="info"
                  icon={Globe}
                  className="mb-6"
                  title={__('Don\'t have a Cloudflare account?', 'wp-cloudflare-page-cache')}
                  description={__('Cloudflare significantly speeds up your website by leveraging a global network of servers to deliver content faster to your visitors.', 'wp-cloudflare-page-cache')}
                >
                  <Button
                    href={LINKS.CF_SIGNUP}
                    size="sm"
                    className="rounded-sm"
                    variant="blue"
                    target="_blank"
                  >
                    <ExternalLinkIcon className="size-3" />
                    <span>{__('Sign up for free', 'wp-cloudflare-page-cache')}</span>
                  </Button>
                </Notice>
              )}

              <div>
                <label htmlFor="auth-mode" className="block text-sm font-medium text-foreground/80 mb-2">{__('Authentication Mode', 'wp-cloudflare-page-cache')}</label>
                <Select
                  id="auth-mode"
                  className="w-full max-w-full h-10"
                  disabled={asyncLocked}
                  value={authMode.toString()}
                  onChange={(v) => {
                    setAuthMode(parseInt(v));
                  }}
                  options={CF_AUTH_MODE_OPTIONS}
                />
                <p className="text-xs text-muted-foreground mt-1.5">
                  {__('API Tokens are more secure and provide better control over permissions', 'wp-cloudflare-page-cache')}
                </p>
              </div>

              <div className="mt-6">
                {authMode === CF_AUTH_MODES.API_KEY && (
                  <ApiKeyForm />
                )}

                {authMode === CF_AUTH_MODES.API_TOKEN && (
                  <ApiTokenForm />
                )}
              </div>

            </div>
          )}

          {hasZoneIDList && (
            <TransitionWrapper>
              <ZoneIdConnect />
            </TransitionWrapper>
          )}

          <Separator />

          {(cloudflareConnected && <ControlsGroup controls={[
            {
              id: 'enable_cache_rule',
              type: 'toggle',
              label: __('Enable Cloudflare CDN & Caching', 'wp-cloudflare-page-cache'),
              description: __('Serve cached files from Cloudflare using Cache Rule.', 'wp-cloudflare-page-cache'),
              hide: !cloudflareConnected,
            },
          ]} />)}

        </CardContent>

      </Card>
    </PageContent >
  )
}

export default Cloudflare;
