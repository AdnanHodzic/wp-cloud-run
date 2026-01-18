import Card, { CardContent, CardFooter, CardHeader } from "@/components/Card";
import Container from "@/layout/Container";
import PageWrap from "@/layout/PageWrap";
import SettingCardPlaceholder from "./SettingCardPlaceholder";
import HeaderPlaceholder from "./HeaderPlaceholder";

const SettingsPlaceholder = () => {
  return (
    <PageWrap className="relative isolate">
      <HeaderPlaceholder />

      {/* Desktop Menu */}
      <div className="hidden xl:block bg-background border-b">
        <Container>
          <div className="flex items-center space-x-6">
            {[...Array(6)].map((_, i) => (
              <div key={i} className="flex gap-2 px-4 py-3">
                <div className="size-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                <div className="flex flex-col gap-2">
                  <div className="w-10 h-3 bg-muted-foreground/50 rounded animate-pulse"></div>
                  <div className="w-20 h-2 bg-muted-foreground/50 rounded animate-pulse"></div>
                </div>
              </div>
            ))}
          </div>
        </Container>
      </div>

      {/* Main Content */}
      <div className="bg-muted">
        <Container className="py-8">
          <div className="grid lg:grid-cols-12 gap-6">
            <SettingCardPlaceholder />

            <div className="lg:hidden w-full h-px bg-muted-foreground/50"></div>

            <div className="lg:col-span-4 space-y-6">
              <Card>
                <CardHeader className="bg-muted py-5">
                  <div className="w-28 h-5 bg-muted-foreground/50 rounded animate-pulse"></div>
                </CardHeader>
                <CardContent className="grid gap-3">
                  <div className="h-10 bg-orange-500/50 rounded-md animate-pulse w-full"></div>
                  <div className="h-10 bg-muted-foreground/50 rounded-md animate-pulse w-full"></div>
                </CardContent>
                <CardFooter>
                  <div className="w-full h-3 bg-muted-foreground/50 rounded animate-pulse"></div>
                </CardFooter>
              </Card>

              {/* Help Card */}
              <div className="bg-background rounded-lg border p-6">
                <div className="w-20 h-5 bg-muted-foreground/50 rounded mb-4 animate-pulse"></div>
                <div className="space-y-3">
                  {[...Array(3)].map((_, i) => (
                    <div key={i} className="flex items-center p-3">
                      <div className="w-5 h-5 bg-muted-foreground/50 rounded mr-3 animate-pulse"></div>
                      <div className="w-28 h-4 bg-muted-foreground/50 rounded animate-pulse"></div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </Container>
      </div >
    </PageWrap >
  );
};

export default SettingsPlaceholder; 