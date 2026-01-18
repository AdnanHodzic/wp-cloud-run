import { NAV_ITEMS_IDS, ROOT_PAGES } from '@/lib/constants';
import { updateCoreDashboardNav } from '@/lib/navUtils';
import { createContext, useContext, useReducer } from '@wordpress/element';

const { licenseData, rootPagePrefix } = window.SPCDash;
// State interface
interface AppState {
  rootPage: string;
  darkMode?: boolean;
  asyncLocked: boolean;
  sidebarOpen: boolean;
  activeMenuItem: typeof NAV_ITEMS_IDS[keyof typeof NAV_ITEMS_IDS];
  licenseData: Record<string, any> | null;
  drawer: false | 'nginx' | 'cached';
  showWizard: boolean;
}

// Action types
type AppAction =
  | { type: 'TOGGLE_SIDEBAR' }
  | { type: 'TOGGLE_DARK_MODE' }
  | { type: 'SET_SIDEBAR'; payload: boolean }
  | { type: 'LOCK_ASYNC'; payload: boolean }
  | { type: 'SET_LICENSE'; payload: Record<string, any> | null }
  | { type: 'SET_ACTIVE_MENU_ITEM'; payload: typeof NAV_ITEMS_IDS[keyof typeof NAV_ITEMS_IDS] }
  | { type: 'SET_DRAWER'; payload: false | 'nginx' | 'cached' }
  | { type: 'SET_ROOT_PAGE'; payload: string }
  | { type: 'SET_SHOW_WIZARD'; payload: boolean };

const userDarkModePreference = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
const darkmodeInitialStatus = localStorage.getItem('spc-dash-darkmode') === null ? userDarkModePreference : localStorage.getItem('spc-dash-darkmode') === 'true';

let rootPageInitialStatus = ROOT_PAGES.DASHBOARD;

if (window.location.search.includes('page=')) {
  const initialURL = new URL(window.location.href);
  const initialPage = initialURL.searchParams.get('page');

  rootPageInitialStatus = initialPage.replace(rootPagePrefix, '').toLowerCase();

  if (initialPage === 'super-page-cache' || !initialPage) {
    rootPageInitialStatus = ROOT_PAGES.DASHBOARD;
  }
}

// Initial state
const initialState: AppState = {
  rootPage: rootPageInitialStatus,
  sidebarOpen: false,
  asyncLocked: false,
  activeMenuItem: NAV_ITEMS_IDS.GENERAL,
  licenseData: licenseData || null,
  darkMode: darkmodeInitialStatus,
  drawer: false,
  showWizard: !!window.SPCDash.displayWizard
};

// Reducer
const appReducer = (state: AppState, action: AppAction): AppState => {
  switch (action.type) {
  case 'TOGGLE_SIDEBAR':
    return { ...state, sidebarOpen: !state.sidebarOpen };
  case 'TOGGLE_DARK_MODE':
    localStorage.setItem('spc-dash-darkmode', !state.darkMode ? 'true' : 'false');
    return { ...state, darkMode: !state.darkMode };
  case 'SET_SIDEBAR':
    return { ...state, sidebarOpen: action.payload };
  case 'SET_ACTIVE_MENU_ITEM':
    if (action.payload && action.payload !== NAV_ITEMS_IDS.GENERAL) {
      window.location.hash = action.payload;
    } else {
      window.location.hash = '';
    }
    return { ...state, activeMenuItem: action.payload };
  case 'LOCK_ASYNC':
    return { ...state, asyncLocked: action.payload };
  case 'SET_LICENSE':
    return { ...state, licenseData: action.payload };
  case 'SET_DRAWER':
    return { ...state, drawer: action.payload };
  case 'SET_ROOT_PAGE':
    if (state.showWizard) {
      if (![ROOT_PAGES.DASHBOARD, ROOT_PAGES.HELP].includes(action.payload)) {
        return state;
      }
    }

    if (!window.SPCDash.isPro) {
      if (action.payload === ROOT_PAGES.LICENSE) {
        return state;
      }
    }

    if (!Object.values(ROOT_PAGES).includes(action.payload)) {
      return state;
    }
    updateCoreDashboardNav(action.payload);

    return { ...state, rootPage: action.payload };
  case 'SET_SHOW_WIZARD':
    return { ...state, showWizard: action.payload };
  default:
    return state;
  }
};

// Context
const AppContext = createContext<{
  state: AppState;
  dispatch: React.Dispatch<AppAction>;
}>(undefined);

// Provider component
const AppProvider = ({ children }: { children: React.ReactNode }) => {
  const [state, dispatch] = useReducer(appReducer, initialState);

  return (
    <AppContext.Provider value={{ state, dispatch }}>
      {children}
    </AppContext.Provider>
  );
};

// Main hook with all functionality
const useAppStore = () => {
  const context = useContext(AppContext);

  if (context === undefined) {
    throw new Error('useAppStore must be used within an AppProvider');
  }

  const { state, dispatch } = context;

  const validLicense = state.licenseData && state.licenseData.key && state.licenseData.license === 'valid';

  return {
    // State
    ...state,

    // Sidebar actions
    toggleSidebar: () => dispatch({ type: 'TOGGLE_SIDEBAR' }),
    setSidebar: (open: boolean) => dispatch({ type: 'SET_SIDEBAR', payload: open }),

    // Navigation actions
    setActiveMenuItem: (itemId: string | null) => dispatch({ type: 'SET_ACTIVE_MENU_ITEM', payload: itemId }),

    // Theme actions
    toggleDarkMode: () => dispatch({ type: 'TOGGLE_DARK_MODE' }),
    lockAsync: (locked: boolean) => dispatch({ type: 'LOCK_ASYNC', payload: locked }),

    setLicenseData: (data: Record<string, any> | null) => dispatch({ type: 'SET_LICENSE', payload: data }),

    validLicense: validLicense,
    validPro: window.SPCDash.isPro && validLicense,
    unlicensedPro: window.SPCDash.isPro && !validLicense,

    showNginxDrawer: () => dispatch({ type: 'SET_DRAWER', payload: 'nginx' }),
    showCachedDrawer: () => dispatch({ type: 'SET_DRAWER', payload: 'cached' }),
    closeDrawer: () => dispatch({ type: 'SET_DRAWER', payload: false }),

    setRootPage: (page: string) => dispatch({ type: 'SET_ROOT_PAGE', payload: page }),

    setShowWizard: (show: boolean) => dispatch({ type: 'SET_SHOW_WIZARD', payload: show }),
  };
};

export { AppProvider, useAppStore };
