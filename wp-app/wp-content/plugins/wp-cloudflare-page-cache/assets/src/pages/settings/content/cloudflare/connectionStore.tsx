import { createContext, useContext, useReducer } from "@wordpress/element";

type ConnectionState = {
  errorMessage: string;
  isConnecting: boolean;
  alreadyOnCloudflare: boolean;
  cloudflareCheckDone: boolean;
}

type ConnectionAction =
  | { type: 'SET_ERROR_MESSAGE'; payload: { errorMessage: string } }
  | { type: 'CLEAR_ERROR_MESSAGE' }
  | { type: 'SET_IS_CONNECTING'; payload: { isConnecting: boolean } }
  | { type: 'SET_ALREADY_ON_CLOUDFLARE'; payload: { alreadyOnCloudflare: boolean } }
  | { type: 'SET_CLOUDFLARE_CHECK_DONE'; payload: { cloudflareCheckDone: boolean } };

const initialState: ConnectionState = {
  errorMessage: '',
  isConnecting: false,
  alreadyOnCloudflare: false,
  cloudflareCheckDone: false,
}

const connectionReducer = (state: ConnectionState, action: ConnectionAction): ConnectionState => {
  switch (action.type) {
  case 'SET_ERROR_MESSAGE':
    return {
      ...state,
      errorMessage: action.payload.errorMessage
    };
  case 'CLEAR_ERROR_MESSAGE':
    return {
      ...state,
      errorMessage: ''
    };
  case 'SET_IS_CONNECTING':
    return {
      ...state,
      isConnecting: action.payload.isConnecting
    };
  case 'SET_ALREADY_ON_CLOUDFLARE':
    return {
      ...state,
      alreadyOnCloudflare: action.payload.alreadyOnCloudflare
    };
  case 'SET_CLOUDFLARE_CHECK_DONE':
    return {
      ...state,
      cloudflareCheckDone: action.payload.cloudflareCheckDone
    };
  default:
    return state;
  }
};

const ConnectionContext = createContext<{
  state: ConnectionState;
  dispatch: React.Dispatch<ConnectionAction>;
}>(undefined);

const ConnectionProvider = ({ children }: { children: React.ReactNode }) => {
  const [state, dispatch] = useReducer(connectionReducer, initialState);

  return (
    <ConnectionContext.Provider value={{ state, dispatch }}>
      {children}
    </ConnectionContext.Provider>
  );
}

const useConnectionStore = () => {
  const context = useContext(ConnectionContext);
  if (context === undefined) {
    throw new Error('useConnectionStore must be used within a ConnectionProvider');
  }

  const { state, dispatch } = context;

  return {
    errorMessage: state.errorMessage,
    setErrorMessage: (errorMessage: string) => dispatch({ type: 'SET_ERROR_MESSAGE', payload: { errorMessage } }),
    clearErrorMessage: () => dispatch({ type: 'CLEAR_ERROR_MESSAGE' }),
    isConnecting: state.isConnecting,
    setIsConnecting: (isConnecting: boolean) => dispatch({ type: 'SET_IS_CONNECTING', payload: { isConnecting } }),
    alreadyOnCloudflare: state.alreadyOnCloudflare,
    setAlreadyOnCloudflare: (alreadyOnCloudflare: boolean) => dispatch({ type: 'SET_ALREADY_ON_CLOUDFLARE', payload: { alreadyOnCloudflare } }),
    cloudflareCheckDone: state.cloudflareCheckDone,
    setCloudflareCheckDone: (cloudflareCheckDone: boolean) => dispatch({ type: 'SET_CLOUDFLARE_CHECK_DONE', payload: { cloudflareCheckDone } }),
  };
}

export { useConnectionStore, ConnectionProvider };
