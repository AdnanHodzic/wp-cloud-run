import Button from "@/components/Button";
import LogoWordmark from "@/common/LogoWordmark";
import TransitionWrapper from "@/common/TransitionWrapper";
import { useNav } from "@/hooks/use-nav";
import { cn } from "@/lib/utils";
import { useAppStore } from "@/store/store";
import { useEffect, useRef } from "@wordpress/element";
import { X } from "lucide-react";
import { __ } from "@wordpress/i18n";

const MobileMenu = ({ className = "" }: { className?: string }) => {
  const { sidebarOpen, toggleSidebar } = useAppStore();
  const { navItems, activeMenuItem, setActiveMenuItem } = useNav();

  const asideRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (!sidebarOpen) return;

      if (asideRef.current && !asideRef.current.contains(event.target as Node)) {
        toggleSidebar();
      }
    };

    const handleKeyDown = (event: KeyboardEvent) => {
      if (!sidebarOpen) return;
      if (event.key === 'Escape' && sidebarOpen) {
        toggleSidebar();
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    document.addEventListener('keydown', handleKeyDown);

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
      document.removeEventListener('keydown', handleKeyDown);
    };

  }, [sidebarOpen, asideRef]);

  return (
    <div className={className}>
      <div className={cn("fixed z-100 inset-0 bg-black/50 xl:hidden transition-opacity duration-300", {
        "opacity-100": sidebarOpen,
        "opacity-0 pointer-events-none": !sidebarOpen,
      })} />
      <aside id="spc-dashboard-menu" ref={asideRef} className={cn(
        "fixed bottom-0 shadow bg-background z-100 w-[300px] border-r border-muted transition-transform duration-300 xl:translate-x-0 animate-in fade-in-from-left-5",
        !sidebarOpen && "-translate-x-full"
      )}>
        <div className="flex h-full flex-col bg-muted/30">
          <div className="px-4 py-2 border-b bg-background flex items-center">
            <LogoWordmark />
            <Button
              variant="ghost"
              size="icon"
              className="ml-auto"
              onClick={toggleSidebar}
              icon={X}
            >
              <span className="sr-only">
                {__('Close menu', 'wp-cloudflare-page-cache')}
              </span>
            </Button>
          </div>

          <TransitionWrapper from="left">
            <nav className="grid gap-1 p-4">
              {
                navItems.map(({ icon: Icon, label, id, description }) => (
                  <Button
                    key={id}
                    variant={activeMenuItem === id ? "default" : "ghost"}
                    className="w-full justify-start items-start h-auto gap-2"
                    onClick={() => {
                      toggleSidebar();
                      setActiveMenuItem(id)
                    }}
                  >
                    <Icon className="mt-0.5" />
                    <div className="flex flex-col justify-start items-start">
                      {label}

                      {description && (
                        <span className={
                          cn("text-xs", {
                            "text-primary-foreground/75": activeMenuItem === id,
                            "text-muted-foreground": activeMenuItem !== id,
                          })}>
                          {description}
                        </span>
                      )}
                    </div>
                  </Button>
                ))
              }
            </nav>
          </TransitionWrapper>
        </div>
      </aside>
    </div>
  )
}

export default MobileMenu;