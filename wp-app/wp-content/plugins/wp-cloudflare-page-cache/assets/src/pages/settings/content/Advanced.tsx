import PageContent from "@/layout/PageContent";

import AdvancedCache from "./advanced/AdvancedCache";
import AdvancedCloudflare from "./advanced/AdvancedCloudflare";
import AdvancedLog from "./advanced/AdvancedLogs";
import AdvancedOthers from "./advanced/AdvancedOthers";
import AdvancedPreloader from "./advanced/AdvancedPreloader";
import AdvancedVarnish from "./advanced/AdvancedVarnish";

const Advanced = () => {
  return (
    <PageContent>
      <AdvancedCache />
      <AdvancedCloudflare />
      <AdvancedPreloader />
      <AdvancedVarnish />
      <AdvancedLog />
      <AdvancedOthers />
    </PageContent>
  )
}

export default Advanced;
