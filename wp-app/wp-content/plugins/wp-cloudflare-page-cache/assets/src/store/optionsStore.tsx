import { SettingType, SettingValueType } from "@/types/globals";
import { createContext, useContext, useReducer } from "@wordpress/element";

const transformSettings = (settings: Record<string, any>, typeSource = window.SPCDash.settings) => {
  return Object.entries(settings).reduce((acc, setting) => {
    const [key, settingData] = setting;

    // Handle case where setting data has both value and type (like window.SPCDash.settings)
    const value = settingData?.value !== undefined ? settingData.value : settingData;
    const type = settingData?.type || typeSource[key]?.type;

    if (type === 'textarea' && Array.isArray(value)) {
      acc[key] = value.join('\n');
      return acc;
    }

    if (type === 'bool') {
      acc[key] = Boolean(parseInt(value as string));
      return acc;
    }

    acc[key] = value;
    return acc;
  }, {});
};

const localizedSettings = transformSettings(window.SPCDash.settings);
interface SettingsState {
  settings: SettingType;
}

type SettingsAction =
  | { type: 'UPDATE_SETTING'; payload: { key: string; value: SettingValueType } }
  | { type: 'UPDATE_SETTINGS'; payload: { settings: SettingType } };

const initialState: SettingsState = {
  settings: localizedSettings
}

const settingsReducer = (state: SettingsState, action: SettingsAction): SettingsState => {
  switch (action.type) {
  case 'UPDATE_SETTING':
    return {
      ...state,
      settings: {
        ...state.settings,
        [action.payload.key]: action.payload.value
      }
    };
  case 'UPDATE_SETTINGS':
    return {
      ...state,
      settings: {
        ...state.settings,
        ...transformSettings(action.payload.settings)
      }
    };
  default:
    return state;
  }
}

const SettingsContext = createContext<{
  state: SettingsState;
  dispatch: React.Dispatch<SettingsAction>;
}>(undefined);

const SettingsProvider = ({ children }: { children: React.ReactNode }) => {
  const [state, dispatch] = useReducer(settingsReducer, initialState);

  return (
    <SettingsContext.Provider value={{ state, dispatch }}>
      {children}
    </SettingsContext.Provider>
  );
}

const useSettingsStore = () => {
  const context = useContext(SettingsContext);
  if (context === undefined) {
    throw new Error('useSettingsStore must be used within a SettingsProvider');
  }

  const { state, dispatch } = context;

  return {
    settings: state.settings,
    pageCacheOn: Boolean(state.settings.cf_fallback_cache),
    updateSetting: (key: string, value: SettingValueType) => dispatch({ type: 'UPDATE_SETTING', payload: { key, value } }),
    updateSettings: (settings: SettingType) => dispatch({ type: 'UPDATE_SETTINGS', payload: { settings } }),
    cloudflareConnected: Boolean(state.settings.cf_zoneid),
    isToggleOn: (key: string) => {
      if (
        !state.settings[key] ||
        !window.SPCDash.settings[key]
      ) return false;

      return Boolean(state.settings[key]);
    },
    isValueSelected: (key: string, value: string) => {
      if (
        !state.settings[key] ||
        !window.SPCDash.settings[key]
      ) return false;
      return state.settings[key] === value;
    }
  };
}

export { SettingsProvider, useSettingsStore };
