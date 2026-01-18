import BlackFridayBanner from "@/common/BlackFridayBanner";
import Separator from "@/common/Separator";
import { useNav } from "@/hooks/use-nav";
import Container from "@/layout/Container";
import Header from "@/layout/Header";
import PageWrap from "@/layout/PageWrap";
import Content from "@/pages/settings/components/Content";
import DesktopMenu from "@/pages/settings/components/DesktopMenu";
import MobileMenu from "@/pages/settings/components/MobileMenu";
import Sidebar from "@/pages/settings/components/Sidebar";
import { useEffect } from "@wordpress/element";

const Settings = () => {
  const { navItems, setActiveMenuItem } = useNav();

  useEffect(() => {
    if (window.location.hash && navItems.length > 0) {
      const hash = window.location.hash.replace('#', '');
      const activeMenuItem = navItems.find(item => item.id === hash);

      if (activeMenuItem) {
        setActiveMenuItem(activeMenuItem.id);
      }
    }
  }, []);

  return (
    <PageWrap className="relative isolate">
      <MobileMenu className="xl:hidden" />
      <Header />
      <DesktopMenu />

      <Container className="py-8 pb-16">
        <BlackFridayBanner />

        <div className="grid lg:grid-cols-12 gap-6">
          <Content className="lg:col-span-8 grid gap-6 lg:gap-10 items-start" />
          <Separator className="lg:hidden" />
          <Sidebar className="lg:col-span-4 space-y-6" />
        </div>

        <div className="spc-drawer-portal" />
      </Container>
    </PageWrap>
  );
};

export default Settings;