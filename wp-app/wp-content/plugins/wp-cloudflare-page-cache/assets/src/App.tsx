import Main from '@/router/Main';
import CoreDashboardMenuHandler from '@/components/CoreDashboardMenuHandler';
import { AppProvider } from '@/store/store';
import { SettingsProvider } from './store/optionsStore';
import { Toaster } from '@/components/ui/sonner';

const App = () => {
  return (
    <AppProvider>
      <SettingsProvider>
        <CoreDashboardMenuHandler />
        <Main />
        <Toaster />
      </SettingsProvider>
    </AppProvider>
  )
};

export default App;