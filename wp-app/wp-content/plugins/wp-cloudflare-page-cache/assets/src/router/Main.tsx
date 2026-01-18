import PageWrap from "@/layout/PageWrap";
import { ROOT_PAGES } from "@/lib/constants";
import Dashboard from "@/pages/dashboard/Dashboard";
import HelpPlaceholder from "@/pages/placeholders/HelpPlaceholder";
import ImportExportPlaceholder from "@/pages/placeholders/ImportExportPlaceholder";
import LicensePlaceholder from "@/pages/placeholders/LicensePlaceholder";
import SettingsPlaceholder from "@/pages/placeholders/SettingsPlaceholder";
import { useAppStore } from "@/store/store";
import { lazy, Suspense, useEffect } from "@wordpress/element";
import { Loader2 } from "lucide-react";

const Settings = lazy(() => import("@/pages/settings/Settings"));
const License = lazy(() => import("@/pages/license/License"));
const Help = lazy(() => import("@/pages/help/Help"));
const ImportExport = lazy(() => import("@/pages/import-export/ImportExport"));

const Main = () => {
  const { rootPage, setRootPage, showWizard } = useAppStore();
  const { rootPagePrefix } = window.SPCDash;

  const getPageFromUrl = (url: string) => {
    const searchParams = new URL(url, window.location.href).searchParams;
    const pageParam = searchParams.get('page');
    if (!pageParam || pageParam === 'super-page-cache') return ROOT_PAGES.DASHBOARD;

    const page = pageParam.replace(rootPagePrefix, '').toLowerCase();
    return ROOT_PAGES[page.replace('-', '_').toUpperCase()] || ROOT_PAGES.DASHBOARD;
  }

  const listenToInternalPages = (e: MouseEvent) => {
    e.preventDefault();
    let target = e.target as HTMLElement;

    if (target.tagName !== 'A') {
      target = target.closest('a');
    }

    const href = target.getAttribute('href');

    if (!href) return;

    setRootPage(getPageFromUrl(href));
  }

  useEffect(() => {
    const menuWrapper = document.querySelectorAll('.toplevel_page_super-page-cache a[href*="page=super-page-cache"]');

    menuWrapper.forEach((wrapper) => {
      wrapper.addEventListener('click', listenToInternalPages);
    });

    return () => {
      menuWrapper.forEach((wrapper) => {
        wrapper.removeEventListener('click', listenToInternalPages);
      });
    }
  }, []);


  useEffect(() => {
    if (!showWizard) {
      return;
    }

    if (![ROOT_PAGES.DASHBOARD, ROOT_PAGES.HELP].includes(rootPage)) {
      setRootPage(ROOT_PAGES.DASHBOARD);
    }
  }, []);

  const getPlaceholder = (page: string) => {
    switch (page) {
    case ROOT_PAGES.SETTINGS:
      return <SettingsPlaceholder />;
    case ROOT_PAGES.IMPORT_EXPORT:
      return <ImportExportPlaceholder />;
    case ROOT_PAGES.LICENSE:
      return <LicensePlaceholder />;
    case ROOT_PAGES.HELP:
      return <HelpPlaceholder />;
    default:
      return (
        <PageWrap className="py-10 text-muted-foreground flex items-center justify-center grow">
          <Loader2 className="size-10 animate-spin mx-auto" />
        </PageWrap>
      );
    }
  };

  const getPage = (page: string) => {
    switch (page) {
    case ROOT_PAGES.SETTINGS:
      return <Settings />;
    case ROOT_PAGES.IMPORT_EXPORT:
      return <ImportExport />;
    case ROOT_PAGES.LICENSE:
      return <License />;
    case ROOT_PAGES.HELP:
      return <Help />;
    default:
      return <Dashboard />;
    }
  };

  if (rootPage === ROOT_PAGES.DASHBOARD) {
    return (
      <Dashboard />
    );
  }

  return (
    <Suspense fallback={getPlaceholder(rootPage)}>
      {getPage(rootPage)}
    </Suspense>
  );
}

export default Main;