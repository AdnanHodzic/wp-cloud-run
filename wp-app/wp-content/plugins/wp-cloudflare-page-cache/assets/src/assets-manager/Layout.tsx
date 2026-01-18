import { Toaster } from "sonner";
import { useAssetManagerStore } from "@/store/assetManagerStore";
import { lazy, Suspense } from "@wordpress/element";
import AssetManagerSkeleton from "./AssetManagerSkeleton";
import TransitionWrapper from "@/common/TransitionWrapper";
import { CheckCircle, CircleAlert, Info, TriangleAlert } from "lucide-react";

const Legend = lazy(() => import('./Legend'));
const Header = lazy(() => import('./Header'));
const List = lazy(() => import('./List'));
const ActionControl = lazy(() => import('./ActionControl'));

const Layout = () => {
  const { modalOpen } = useAssetManagerStore();

  if (!modalOpen) {
    return null;
  }

  return (
    <div id="spc-assets-manager" className="antialiased fixed inset-0 bg-black/75 z-[1000000]">
      <TransitionWrapper className="w-full h-full grow flex items-center justify-center p-4">
        <div className="flex flex-col text-foreground rounded-xl shadow-2xl w-full max-w-[90vw] lg:max-w-[80vw] xl:max-w-[70vw] 2xl:max-w-[60vw] max-h-[90vh] overflow-hidden">
          <Suspense fallback={<AssetManagerSkeleton />}>
            <Header />
            <List />
            <Legend />
            <ActionControl />
          </Suspense>
        </div>

      </TransitionWrapper>

      <Toaster
        theme="dark"
        richColors={true}
        closeButton
        icons={{
          success: <CheckCircle className="size-5" />,
          error: <CircleAlert className="size-5" />,
          warning: <TriangleAlert className="size-5" />,
          info: <Info className="size-5" />,
        }}
      />
    </div>
  )
};

export default Layout;