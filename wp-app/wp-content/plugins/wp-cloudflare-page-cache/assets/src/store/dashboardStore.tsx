import { createContext, useContext, useEffect, useReducer } from "@wordpress/element";
import { spcApi } from "@/lib/api";
import { useSettingsStore } from "@/store/optionsStore";

export type CloudflareAnalyticsData = {
  requests: number;
  bytes: number;
  cachedBytes: number;
};

type DashboardState = {
  analyticsAvailable: boolean;
  analyticsData: CloudflareAnalyticsData | null;
  loadingAnalytics: boolean;
};

type DashboardAction =
  | { type: "SET_ANALYTICS_AVAILABLE"; payload: boolean }
  | { type: "SET_ANALYTICS_DATA"; payload: CloudflareAnalyticsData | null }
  | { type: "SET_LOADING_ANALYTICS"; payload: boolean };

const initialState: DashboardState = {
  analyticsAvailable: true,
  analyticsData: null,
  loadingAnalytics: false,
};

const dashboardReducer = (state: DashboardState, action: DashboardAction): DashboardState => {
  switch (action.type) {
  case "SET_ANALYTICS_AVAILABLE":
    return { ...state, analyticsAvailable: action.payload };
  case "SET_ANALYTICS_DATA":
    return { ...state, analyticsData: action.payload };
  case "SET_LOADING_ANALYTICS":
    return { ...state, loadingAnalytics: action.payload };
  default:
    return state;
  }
};

const DashboardContext = createContext<{
  state: DashboardState;
  dispatch: React.Dispatch<DashboardAction>;
} | undefined>(undefined);

const DashboardProvider = ({ children }: { children: React.ReactNode }) => {
  const { cloudflareConnected } = useSettingsStore();
  const [state, dispatch] = useReducer(dashboardReducer, initialState);

  useEffect(() => {
    if (!cloudflareConnected) return;

    dispatch({ type: "SET_LOADING_ANALYTICS", payload: true });
    
    spcApi.getCloudflareAnalytics()
      .then((response) => {
        if (
          response.success &&
          typeof response.data?.requests === 'number' &&
          typeof response.data?.bytes === 'number' &&
          typeof response.data?.cachedBytes === 'number'
        ) {
          dispatch({ type: "SET_ANALYTICS_DATA", payload: response.data as CloudflareAnalyticsData });
        }
      })
      .catch(() => {
        dispatch({ type: "SET_ANALYTICS_AVAILABLE", payload: false });
      })
      .finally(() => {
        dispatch({ type: "SET_LOADING_ANALYTICS", payload: false });
      });
  }, [cloudflareConnected, dispatch]);

  return (
    <DashboardContext.Provider value={{ state, dispatch }}>
      {children}
    </DashboardContext.Provider>
  );
};

const useDashboardStore = () => {
  const context = useContext(DashboardContext);
  if (context === undefined) {
    throw new Error("useDashboardStore must be used within a DashboardProvider");
  }
  const { state } = context;
  return {
    analyticsAvailable: state.analyticsAvailable,
    analyticsData: state.analyticsData,
    loadingAnalytics: state.loadingAnalytics,
  };
};

export { useDashboardStore, DashboardProvider }; 