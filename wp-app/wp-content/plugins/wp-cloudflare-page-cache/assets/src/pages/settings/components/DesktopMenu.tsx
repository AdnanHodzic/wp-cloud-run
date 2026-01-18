import TransitionWrapper from "@/common/TransitionWrapper";
import { useNav } from "@/hooks/use-nav";
import Container from "@/layout/Container";
import { cn } from "@/lib/utils";

const DesktopMenu = () => {
  const { navItems, activeMenuItem, setActiveMenuItem } = useNav();

  return (
    <div className="hidden xl:block bg-background border-b">
      <Container>
        <nav className="flex -mb-px justify-start list-none">
          {navItems.map(({ icon: Icon, label, id, description }) => (
            <TransitionWrapper from="top" key={id} className='duration-300'>
              <a
                href={`#${id}`}
                className={cn(
                  "flex items-start px-4 py-3 text-sm font-medium border-b-2 transition-colors relative", {
                    'border-orange-500 text-orange-600 bg-orange-50 dark:bg-orange-950/20 dark:text-orange-400': activeMenuItem === id,
                    'border-transparent text-muted-foreground hover:text-foreground hover:border-border': activeMenuItem !== id,
                  })}
                onClick={() => setActiveMenuItem(id)}
              >
                <Icon className="size-4 mr-2 mt-0.5" />

                <div className="flex flex-col items-start">
                  <span className="leading-tight">{label}</span>
                  <div className="flex items-center">
                    <span className="text-xs text-muted-foreground leading-tight">{description}</span>
                  </div>
                </div>

              </a>
            </TransitionWrapper>
          ))}
        </nav>
      </Container>
    </div>
  );
};






<button className="">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="lucide lucide-database w-4 h-4 mr-2">
    <ellipse cx="12" cy="5" rx="9" ry="3">
    </ellipse>
    <path d="M3 5V19A9 3 0 0 0 21 19V5">
    </path>
    <path d="M3 12A9 3 0 0 0 21 12">
    </path>
  </svg>
  <div className="flex flex-col items-start">
    <span className="leading-tight">General</span>
    <div className="flex items-center">
      <span className="text-xs text-gray-500 leading-tight ">Core Configuration</span>
    </div>
  </div>
</button>



export default DesktopMenu;