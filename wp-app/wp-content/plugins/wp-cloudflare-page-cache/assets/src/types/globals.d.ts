type SettingValueType = string | any[] | number;

type LocalizedSetting = {
  type: 'bool' | 'int' | 'text' | 'number' | 'textarea' | 'array';
  default: string | number;
  value: SettingValueType;
}

type SettingType = Record<string, SettingValueType>;

declare global {
  interface Window {
    SPCDash: {
      displayWizard: boolean;
      isPro: boolean;
      version: string;
      upsellURL: string;
      logoURL: string;
      api: string;
      nonce: string;
      optimoleData: Record<string, any>;
      licenseData: Record<string, any> | null;
      i18n: Record<string, any>;
      settings: Record<string, LocalizedSetting>;
      wpConfigWritable: boolean;
      wpContentWritable: boolean;
      thirdPartyIntegrations: Record<string, boolean>;
      thirdPartyVisible: boolean;
      conflicts: string[];
      cronjobURL: string;
      cronjobPurgeURL: string;
      databaseOptimizationScheduleOptions: Record<string, string>;
      wordpressMenus: Record<number, string>;
      wordpressRoles: Record<string, string>;
      logViewURL: string;
      logDownloadURL: string;
      configExportURL: string;
      zoneIdList: Record<string, string>;
      rootPagePrefix: string;
      help: Record<string, Record<string, any>[]>;
      directSupportURL: string;
      mainPageURL: string;
      ruleNeedsRepair: boolean;
      hasOverdueJobs: boolean;
      homeURL: string;
      pluginsPageURL: string;
      metrics: {
        'cache.files': {
          html_files: number | 'n/a';
        };
        'cache.size': {
          size: number | 'n/a';
          free: number | 'n/a';
        };
        'cache.hitmiss': {
          hits: number | 'n/a';
          misses: number | 'n/a';
          ratio: number | 'n/a';
        };
        'cache.ttfb': {
          ttfb_ms: number | 'n/a';
        }
      };
    };
    SPCBlackFridayBanner?: string;
  }
}

export { LocalizedSetting, SettingType, SettingValueType };
