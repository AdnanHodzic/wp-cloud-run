import DashboardPlaceholder from "@/pages/placeholders/DashboardPlaceholder";
import StartWizardPlaceholder from "@/pages/placeholders/StartWizardPlaceholder";
import { useAppStore } from "@/store/store";
import { lazy, Suspense } from "@wordpress/element";

const StartWizard = lazy(() => import("@/pages/dashboard/components/StartWizard"));
const DashboardContent = lazy(() => import("@/pages/dashboard/components/DashboardContent"));

const Dashboard = () => {
  const { showWizard } = useAppStore();

  if (showWizard) {
    return (
      <Suspense fallback={<StartWizardPlaceholder />}>
        <StartWizard />
      </Suspense>
    );
  }

  return (
    <Suspense fallback={<DashboardPlaceholder />}>
      <DashboardContent />
    </Suspense>
  );
};

export default Dashboard; 