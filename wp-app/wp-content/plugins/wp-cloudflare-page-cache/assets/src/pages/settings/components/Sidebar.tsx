
import TransitionWrapper from "@/common/TransitionWrapper";
import SidebarHelpCard from "./SidebarHelpCard";
import SidebarQuickActions from "./SidebarQuickActions";
import SidebarUpsellCard from "./SidebarUpsellCard";

const { isPro } = window.SPCDash;

const Sidebar = ({ className = "" }: { className?: string }) => {

  return (

    <div className={className}>
      <TransitionWrapper from="right" className="delay-300">
        <SidebarQuickActions />
      </TransitionWrapper>

      {!isPro && (
        <TransitionWrapper from="right" className="delay-400">
          <SidebarUpsellCard />
        </TransitionWrapper>
      )}

      <TransitionWrapper from="right" className="delay-500">
        <SidebarHelpCard />
      </TransitionWrapper>
    </div>
  )
}

export default Sidebar;