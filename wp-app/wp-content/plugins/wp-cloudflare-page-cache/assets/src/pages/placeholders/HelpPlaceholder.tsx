import PageWrap from "@/layout/PageWrap";
import HeaderPlaceholder from "./HeaderPlaceholder";

const HelpPlaceholder = () => {
  return (
    <PageWrap>
      <HeaderPlaceholder />

      {/* Main Content */}
      <div className="bg-muted">
        <div className="max-w-5xl mx-auto px-6 py-8">
          {/* Hero Section */}
          <div className="text-center mb-12">
            <div className="w-16 h-16 bg-muted-foreground/50 rounded-full mx-auto mb-6 animate-pulse"></div>
            <div className="w-64 h-8 bg-muted-foreground/50 rounded mx-auto mb-3 animate-pulse"></div>
            <div className="w-80 h-5 bg-muted-foreground/50 rounded mx-auto animate-pulse"></div>
          </div>

          {/* Popular Articles Section */}
          <div className="mb-12">
            <div className="flex items-center mb-6">
              <div className="w-5 h-5 bg-muted-foreground/50 rounded mr-2 animate-pulse"></div>
              <div className="w-32 h-6 bg-muted-foreground/50 rounded animate-pulse"></div>
            </div>
            <div className="grid md:grid-cols-2 gap-4">
              {[...Array(4)].map((_, i) => (
                <div key={i} className="bg-background rounded-lg border p-5">
                  <div className="w-3/4 h-5 bg-muted-foreground/50 rounded mb-2 animate-pulse"></div>
                  <div className="w-full h-4 bg-muted-foreground/50 rounded mb-3 animate-pulse"></div>
                  <div className="flex items-center justify-between">
                    <div className="w-16 h-3 bg-muted-foreground/50 rounded animate-pulse"></div>
                    <div className="w-4 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Documentation Categories */}
          <div className="mb-12">
            <div className="w-48 h-6 bg-muted-foreground/50 rounded mb-6 animate-pulse"></div>
            <div className="grid md:grid-cols-3 gap-4">
              {[...Array(6)].map((_, i) => (
                <div key={i} className="bg-background rounded-lg border p-5">
                  <div className="w-10 h-10 bg-muted-foreground/50 rounded-lg mb-4 animate-pulse"></div>
                  <div className="w-3/4 h-5 bg-muted-foreground/50 rounded mb-2 animate-pulse"></div>
                  <div className="w-full h-4 bg-muted-foreground/50 rounded mb-3 animate-pulse"></div>
                  <div className="flex items-center justify-between">
                    <div className="w-16 h-3 bg-muted-foreground/50 rounded animate-pulse"></div>
                    <div className="w-4 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Get Support Section */}
          <div className="mb-12">
            <div className="w-24 h-6 bg-muted-foreground/50 rounded mb-6 animate-pulse"></div>
            <div className="grid md:grid-cols-2 gap-4">
              {[...Array(4)].map((_, i) => (
                <div key={i} className="border rounded-lg p-6">
                  <div className="flex items-center mb-4">
                    <div className="w-6 h-6 bg-muted-foreground/50 rounded mr-3 animate-pulse"></div>
                    <div className="grid gap-1">
                      <div className="w-24 h-5 bg-muted-foreground/50 rounded animate-pulse"></div>
                      <div className="w-32 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                    </div>
                  </div>
                  <div className="flex gap-1 items-center">
                    <div className="w-28 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                    <div className="w-3 h-3 bg-muted-foreground/50 rounded animate-pulse"></div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Contact Support CTA */}
          <div className="bg-muted-background rounded-lg border border-muted-foreground/10 p-8 text-center">
            <div className="w-12 h-12 bg-muted-foreground rounded mx-auto mb-4 animate-pulse"></div>
            <div className="w-32 h-6 bg-muted-foreground rounded mx-auto mb-2 animate-pulse"></div>
            <div className="w-80 h-4 bg-muted-foreground rounded mx-auto mb-6 animate-pulse"></div>
            <div className="grid items-center justify-center gap-4">
              <div className="w-40 h-12 bg-muted-foreground rounded-lg mx-auto animate-pulse"></div>
              <div className="w-48 h-4 bg-muted-foreground rounded mx-auto animate-pulse"></div>
            </div>
          </div>
        </div>
      </div>
    </PageWrap>
  );
};

export default HelpPlaceholder;