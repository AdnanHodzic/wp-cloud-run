/* eslint-disable no-case-declarations */

import { createContext, useContext, useReducer } from "@wordpress/element";
import { Asset } from "@/assets-manager/globals";

const { assets, existingRules, availableContexts } = window.SPCAssetManager;

interface AssetManagerState {
  modalOpen: boolean;
  buttonIsDisabled?: boolean;
  activeTab: 'css' | 'js';
  assets: Asset[];
  searchQuery: string;
  asyncLocked: boolean;
  changesToSave: Record<string, Record<string, any>>;
}

type AssetManagerAction =
  | { type: 'SWITCH_TAB'; payload: { tabId: 'css' | 'js' } }
  | { type: 'UPDATE_ASSETS'; payload: { assets: Asset[] } }
  | { type: 'CLOSE_MODAL' }
  | { type: 'DISABLE_BUTTON' }
  | { type: 'RESET_ALL' }
  | { type: 'SEARCH_QUERY'; payload: { query: string } }
  | { type: 'LOCK_ASYNC', payload: { locked: boolean } }
  | { type: 'GATHER_CHANGES', payload: { assetKey: string, changes: Record<string, boolean> } };

const initialAssets = assets.map(singleAsset => {
  const normalizedAsset = {
    ...singleAsset,
    locationContexts: [],
    userStateContexts: [],
  };

  Object.entries(existingRules).forEach(([assetHash, rules]) => {
    if (assetHash !== singleAsset.asset_hash) {
      return;
    }


    rules.forEach((rule) => {
      if (rule.includes('is_logged_in')) {
        normalizedAsset.userStateContexts.push(rule);

        return;
      }

      normalizedAsset.locationContexts.push(rule);
    });
  });

  return normalizedAsset;
});

const initialState: AssetManagerState = {
  modalOpen: true,
  activeTab: 'css',
  assets: initialAssets,
  buttonIsDisabled: false,
  searchQuery: '',
  asyncLocked: false,
  changesToSave: {},
};

const getUrlWithoutSearchParams = () => {
  const url = new URL(window.location.href);
  url.search = '';
  return url.href;
}

const assetManagerReducer = (state: AssetManagerState, action: AssetManagerAction): AssetManagerState => {
  switch (action.type) {
  case 'UPDATE_ASSETS': 
    return {
      ...state,
      assets: action.payload.assets,
    };
  case 'GATHER_CHANGES':
    const { assetKey, changes } = action.payload;
    const newChanges = { ...state.changesToSave, [assetKey]: { ...(state.changesToSave[assetKey] || {}), ...changes } };

    return ({
      ...state,
      changesToSave: newChanges,
    });
  case 'CLOSE_MODAL': 
    window.history.replaceState({}, '', getUrlWithoutSearchParams());
    return { ...state, modalOpen: !state.modalOpen };
  case 'DISABLE_BUTTON':
    return { ...state, buttonIsDisabled: !state.buttonIsDisabled };
  case 'RESET_ALL':
    const nextChanges = { ...state.changesToSave };
    const upcomingAssets = [...state.assets];
    const upcoming = upcomingAssets.map(singleAsset => {
      const { locationContexts, userStateContexts, asset_hash } = singleAsset;

      locationContexts.forEach(assetCtx => {
        if (!nextChanges[asset_hash]) nextChanges[asset_hash] = {};

        const contextKey = availableContexts.locationContexts.find(context => context.saveAs === assetCtx)?.key;

        if (!contextKey) return;

        nextChanges[asset_hash] = {
          ...nextChanges[asset_hash],
          [contextKey]: true,
        };
      });

      userStateContexts.forEach(assetCtx => {
        if (!nextChanges[asset_hash]) nextChanges[asset_hash] = {};

        const contextKey = availableContexts.userStateContexts.find(context => context.saveAs === assetCtx)?.key;

        if (!contextKey) return;

        nextChanges[asset_hash] = {
          ...nextChanges[asset_hash],
          [contextKey]: true,
        };
      });

      return {
        ...singleAsset,
        locationContexts: [],
        userStateContexts: [],
      };
    });
    
    console.log(upcoming);
    
    return {
      ...state,
      assets: upcoming,
      changesToSave: nextChanges,
      searchQuery: '',
    };
  case 'SEARCH_QUERY':
    return {
      ...state,
      searchQuery: action.payload.query,
    }
  case 'LOCK_ASYNC':
    return {
      ...state,
      asyncLocked: action.payload.locked,
    }

  case 'SWITCH_TAB':
    return {
      ...state,
      activeTab: action.payload.tabId,
    }
  default:
    return state;
  }
}

const AssetManagerContext = createContext<{
  state: AssetManagerState;
  dispatch: React.Dispatch<AssetManagerAction>;
}>(undefined);

const AssetManagerProvider = ({ children }: { children: React.ReactNode }) => {
  const [state, dispatch] = useReducer(assetManagerReducer, initialState);

  return (
    <AssetManagerContext.Provider value={{ state, dispatch }}>
      {children}
    </AssetManagerContext.Provider>
  );
}

const useAssetManagerStore = () => {
  const context = useContext(AssetManagerContext);

  if (context === undefined) {
    throw new Error('useAssetManagerStore must be used within a AssetManagerProvider');
  }

  const { state, dispatch } = context;

  return {
    ...state,

    // Tab actions
    switchTab: (tabId: 'css' | 'js') => dispatch({ type: 'SWITCH_TAB', payload: { tabId } }),

    // Asset actions
    resetAssets: () => dispatch({ type: 'RESET_ALL' }),
    setSearchQuery: (query: string) => dispatch({ type: 'SEARCH_QUERY', payload: { query } }),

    // Modal actions
    setButtonIsDisabled: () => dispatch({ type: 'DISABLE_BUTTON'}),
    closeModal: () => dispatch({ type: 'CLOSE_MODAL' }),
    lockAsync: (locked: boolean) => dispatch({ type: 'LOCK_ASYNC', payload: { locked } }),
    updateAssets: (assets: Asset[]) => dispatch({ type: 'UPDATE_ASSETS', payload: { assets } }),

    markChange: (assetKey: string, changes: Record<string, boolean>) => dispatch({ type: 'GATHER_CHANGES', payload: { assetKey, changes } }),
  }
}

export { useAssetManagerStore, AssetManagerProvider };