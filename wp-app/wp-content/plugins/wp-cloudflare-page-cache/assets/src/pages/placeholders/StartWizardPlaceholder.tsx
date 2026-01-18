import PageWrap from "@/layout/PageWrap";
import HeaderPlaceholder from "./HeaderPlaceholder";

const StartWizardPlaceholder = () => {
  return (
    <PageWrap>
      <HeaderPlaceholder />

      {/* Main Content */}
      <div className="bg-muted">
        <div className="max-w-5xl mx-auto px-6 py-8">
          {/* Onboarding Card */}
          <div className="bg-card rounded-lg overflow-hidden border shadow-sm">
            {/* Header Section */}
            <div className="bg-orange-50 border-b border-orange-200 p-6 dark:bg-orange-800/30 dark:border-orange-700/50">
              <div className="flex items-center justify-between">
                <div className="flex items-center">
                  <div className="size-10 bg-orange-100 dark:bg-orange-900/50 border border-orange-100 dark:border-orange-700/50 rounded-lg animate-pulse mr-4"></div>
                  <div>
                    <div className="w-64 h-6 bg-muted-foreground/50 rounded mb-2 animate-pulse"></div>
                    <div className="w-80 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                  </div>
                </div>
                <div className="flex flex-col items-center space-y-2">
                  <div className="w-48 h-10 bg-muted-foreground/50 rounded animate-pulse"></div>
                  <div className="w-20 h-3 bg-muted-foreground/50 rounded animate-pulse"></div>
                  <div className="w-32 h-3 bg-muted-foreground/50 rounded animate-pulse"></div>
                </div>
              </div>
            </div>

            {/* Content Section */}
            <div className="p-6">
              {/* Performance Improvements Section */}
              <div className="mb-8">
                <div className="flex items-center mb-6">
                  <div className="w-5 h-5 bg-muted-foreground/50 rounded mr-2 animate-pulse"></div>
                  <div className="w-48 h-6 bg-muted-foreground/50 rounded animate-pulse"></div>
                </div>
                <div className="w-96 h-4 bg-muted-foreground/50 rounded mb-4 animate-pulse"></div>

                <div className="grid grid-cols-2 lg:grid-cols-4 gap-6">
                  {[...Array(4)].map((_, i) => (
                    <div key={i} className="text-center p-4 bg-muted rounded-lg">
                      <div className="w-20 h-4 bg-muted-foreground/50 rounded mb-3 animate-pulse mx-auto"></div>
                      <div className="space-y-2">
                        <div className="w-12 h-4 bg-muted-foreground/50 rounded animate-pulse mx-auto"></div>
                        <div className="w-4 h-4 bg-muted-foreground/50 rounded animate-pulse mx-auto"></div>
                        <div className="w-16 h-6 bg-muted-foreground/50 rounded animate-pulse mx-auto"></div>
                      </div>
                      <div className="w-12 h-4 bg-muted-foreground/50 rounded animate-pulse mx-auto mt-2"></div>
                    </div>
                  ))}
                </div>
              </div>

              {/* What Gets Activated Section */}
              <div>
                <div className="flex items-center mb-6">
                  <div className="w-5 h-5 bg-muted-foreground/50 rounded mr-2 animate-pulse"></div>
                  <div className="w-64 h-6 bg-muted-foreground/50 rounded animate-pulse"></div>
                </div>

                <div className="grid md:grid-cols-2 gap-4 mb-6">
                  {[...Array(6)].map((_, i) => (
                    <div key={i} className="flex items-start p-3 bg-muted rounded-lg">
                      <div className="w-5 h-5 bg-muted-foreground/50 rounded mr-3 mt-0.5 animate-pulse"></div>
                      <div className="flex-1">
                        <div className="w-24 h-4 bg-muted-foreground/50 rounded mb-2 animate-pulse"></div>
                        <div className="w-32 h-3 bg-muted-foreground/50 rounded animate-pulse"></div>
                      </div>
                    </div>
                  ))}
                </div>

                {/* Info Box */}
                <div className="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-200 dark:border-orange-700/50">
                  <div className="flex items-start">
                    <div className="w-5 h-5 bg-muted-foreground/50 rounded mr-3 animate-pulse"></div>
                    <div className="flex-1">
                      <div className="w-24 h-4 bg-muted-foreground/50 rounded mb-2 animate-pulse"></div>
                      <div className="w-full h-4 bg-muted-foreground/50 rounded mb-1 animate-pulse"></div>
                      <div className="w-3/4 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </PageWrap>
  );
};

export default StartWizardPlaceholder;
