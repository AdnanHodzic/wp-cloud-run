import Layout from './assets-manager/Layout';
import { AssetManagerProvider } from './store/assetManagerStore';

const AssetsManagerApp = () => {
  return (
    <AssetManagerProvider>
      <Layout />
    </AssetManagerProvider>
  );
}  

export default AssetsManagerApp;