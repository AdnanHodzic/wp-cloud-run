import Container from "@/layout/Container";
import Header, { HeaderLeft } from "@/layout/Header";
import PageWrap from "@/layout/PageWrap";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import ActivityLog from "./ActivityLog";
import CacheMetrics from "./CacheMetrics";
import CloudflareHeaderAnalytics from "./CloudflareHeaderAnalytics";
import LicenseNotice from "./LicenseNotice";
import SidebarActions from "./SidebarActions";
import SidebarSystemStatus from "./SidebarSystemStatus";
import SidebarUpsellCard from "./SidebarUpsellCard";
import TransitionWrapper from "@/common/TransitionWrapper";
import BlackFridayBanner from "@/common/BlackFridayBanner";
import PluginConflictsNotice from "./PluginConflictsNotice";
import { DashboardProvider } from "@/store/dashboardStore";

const DashboardContent = () => {
  const { isPro } = window.SPCDash;
  const { unlicensedPro } = useAppStore();
  const { cloudflareConnected } = useSettingsStore();

  return (
    <DashboardProvider>
      <PageWrap>
        <Header backButton={false}>
          {cloudflareConnected && (
            <HeaderLeft>
              <CloudflareHeaderAnalytics />
            </HeaderLeft>
          )}
        </Header>


        <Container className="py-8">
          <BlackFridayBanner />

          {unlicensedPro && (
            <TransitionWrapper from="top">
              <LicenseNotice />
            </TransitionWrapper>
          )}

          {window.SPCDash.conflicts.length > 0 && (
            <TransitionWrapper from="top" className="delay-100">
              <PluginConflictsNotice />
            </TransitionWrapper>
          )}

          <CacheMetrics />

          <div className="grid gap-6 lg:hidden mb-6">
            <TransitionWrapper from="right" className="delay-300">
              <SidebarActions />
            </TransitionWrapper>

            <TransitionWrapper from="right" className="delay-400">
              <SidebarSystemStatus />
            </TransitionWrapper>

            {!isPro && (
              <TransitionWrapper from="right" className="delay-500">
                <SidebarUpsellCard />
              </TransitionWrapper>
            )}
          </div>

          <div className="grid lg:grid-cols-12 gap-6">

            <div className="lg:col-span-8">
              <TransitionWrapper from="bottom" className="delay-300">
                <ActivityLog />
              </TransitionWrapper>
            </div>

            <div className="hidden lg:block lg:col-span-4 space-y-6">
              <TransitionWrapper from="right" className="delay-300">
                <SidebarActions />
              </TransitionWrapper>

              <TransitionWrapper from="right" className="delay-400">
                <SidebarSystemStatus />
              </TransitionWrapper>

              {!isPro && (
                <TransitionWrapper from="right" className="delay-500">
                  <SidebarUpsellCard />
                </TransitionWrapper>
              )}
            </div>
          </div>

        </Container>
      </PageWrap>
    </DashboardProvider>
  );
};

export default DashboardContent;