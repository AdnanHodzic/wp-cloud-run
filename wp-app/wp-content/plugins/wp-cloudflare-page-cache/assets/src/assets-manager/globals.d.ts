type availableContexts = {
  locationContexts: Array<any>;
  userStateContexts: Array<any>
}

declare global {
  interface Window {
    SPCAssetManager: {
      api: string;
      nonce: string;
      assets: Record<string, any>;
      currentContext: Record<string, any>;
      availableContexts: availableContexts;
      existingRules: Record<string, any>;
      cssURL: string;
      otherExclusions: Record<string, Array<{
        url: string;
        label: string;
      }>>;
    };
  }
}

type Asset = {
  handle: string;
  asset_hash: string;
  name: string;
  asset_type: string;
  origin_type: 'css' | 'js';
  asset_url: string;
  size: string;
  is_inline: boolean;
  parent_handle: string | null;
  content: string | null;
  dependencies: string[];
  version: boolean;
  category: string;
  locationContexts?: string[];
  userStateContexts?: string[];
};

export { Asset };