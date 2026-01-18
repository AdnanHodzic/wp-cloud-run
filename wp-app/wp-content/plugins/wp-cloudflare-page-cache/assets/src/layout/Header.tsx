import DarkModeToggle from "@/common/DarkModeToggle";
import Button from "@/components/Button";
import LogoWordmark from "@/common/LogoWordmark";
import Separator from "@/common/Separator";
import TransitionWrapper from "@/common/TransitionWrapper";
import Badge from "@/components/Badge";
import Container from "@/layout/Container";
import { ROOT_PAGES } from "@/lib/constants";
import { useAppStore } from "@/store/store";
import { Children, isValidElement } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { ArrowLeft, HelpCircle, KeyRound, Menu, Settings, TriangleAlert } from "lucide-react";

// Slot components that can be used as children of Header
export const HeaderLeft = ({ children }: { children: React.ReactNode }) => {
  return <>{children}</>;
}

export const HeaderRight = ({ children }: { children: React.ReactNode }) => {
  return <>{children}</>;
}

const Header = ({ backButton = true, title = null, children }: {
  backButton?: boolean,
  title?: string,
  children?: React.ReactNode,
}) => {
  const { isPro, version } = window.SPCDash;
  const { setRootPage, validLicense, rootPage } = useAppStore();
  const { toggleSidebar, showWizard } = useAppStore();

  const handleBack = () => {
    setRootPage(ROOT_PAGES.DASHBOARD);
  }

  // Extract slot content from children
  let headerLeftContent = null;
  let headerRightContent = null;

  Children.forEach(children, (child) => {
    if (isValidElement(child)) {
      const element = child;
      if (element.type === HeaderLeft) {
        headerLeftContent = element.props.children;
      } else if (element.type === HeaderRight) {
        headerRightContent = element.props.children;
      }
    }
  });

  return (
    <div className="bg-background border-b">
      <Container>
        <div className="flex items-center justify-between h-14">
          <div className="flex items-center space-x-3">
            {backButton && (
              <TransitionWrapper from="right" className="hidden xl:block">
                <Button
                  variant="ghost"
                  size="icon"
                  className="text-muted-foreground"
                  onClick={handleBack}
                  icon={ArrowLeft}
                >
                  <span className="sr-only">
                    {__('Back to dashboard', 'wp-cloudflare-page-cache')}
                  </span>
                </Button>
              </TransitionWrapper>
            )}

            {rootPage === ROOT_PAGES.SETTINGS && (
              <Button
                variant="ghost"
                size="icon"
                className='xl:hidden'
                onClick={toggleSidebar}
                icon={Menu}
              >
                <span className="sr-only">
                  {__('Toggle settings menu', 'wp-cloudflare-page-cache')}
                </span>
              </Button>
            )}

            <div className="flex items-center space-x-2">
              <LogoWordmark text={title} className="hidden xl:flex" />
              <LogoWordmark hideText className="xl:hidden" />

              <span className="text-xs text-gray-400 font-mono">v{version}</span>

              {!isPro && <Badge>{__('Free', 'wp-cloudflare-page-cache')}</Badge>}

              {isPro && (
                <Badge variant={validLicense ? 'success' : 'warning'}>
                  {!validLicense && <TriangleAlert />}
                  {__('Pro', 'wp-cloudflare-page-cache') +
                    (validLicense ? '' : ` (${__('Unlicensed', 'wp-cloudflare-page-cache')})`)
                  }
                </Badge>
              )}

              {headerLeftContent && (
                <>
                  <Separator orientation="vertical" />
                  {headerLeftContent}
                </>
              )}
            </div>
          </div>

          <div className="flex items-center gap-2">
            {headerRightContent && (
              <>
                {headerRightContent}
                <Separator orientation="vertical" />
              </>
            )}

            {backButton && (
              <TransitionWrapper from="right" className="xl:hidden">
                <Button
                  variant="ghost"
                  size="icon"
                  className="text-muted-foreground"
                  onClick={handleBack}
                  icon={ArrowLeft}
                >
                  <span className="sr-only">
                    {__('Back to dashboard', 'wp-cloudflare-page-cache')}
                  </span>
                </Button>
              </TransitionWrapper>
            )}

            {(isPro && rootPage !== ROOT_PAGES.LICENSE && !showWizard) && (
              <Button
                title={__('License', 'wp-cloudflare-page-cache')}
                onClick={() => setRootPage(ROOT_PAGES.LICENSE)}
                className="text-muted-foreground"
                variant="ghost"
                size="icon"
                icon={KeyRound}
              >
                <span className="sr-only">
                  {__('License', 'wp-cloudflare-page-cache')}
                </span>
              </Button>
            )}
            {rootPage !== ROOT_PAGES.HELP && (
              <Button
                title={__('Help Center', 'wp-cloudflare-page-cache')}
                onClick={() => setRootPage(ROOT_PAGES.HELP)}
                className="text-muted-foreground"
                variant="ghost"
                size="icon"
                icon={HelpCircle}
              >
                <span className="sr-only">
                  {__('Help Center', 'wp-cloudflare-page-cache')}
                </span>
              </Button>
            )}
            {(rootPage !== ROOT_PAGES.SETTINGS && !showWizard) && (
              <Button
                title={__('Settings', 'wp-cloudflare-page-cache')}
                className="text-muted-foreground"
                variant="ghost"
                size="icon"
                onClick={() => setRootPage(ROOT_PAGES.SETTINGS)}
                icon={Settings}
              >
                <span className="sr-only">
                  {__('Settings', 'wp-cloudflare-page-cache')}
                </span>
              </Button>
            )}
            <DarkModeToggle />
          </div>
        </div>
      </Container>
    </div>
  );
}

export default Header;