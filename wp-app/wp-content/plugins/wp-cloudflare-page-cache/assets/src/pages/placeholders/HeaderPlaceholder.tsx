import Container from "@/layout/Container";

const HeaderPlaceholder = () => {
  return (
    <div className="bg-background border-b">
      <style dangerouslySetInnerHTML={{
        __html: `#adminmenuwrap { position: fixed !important; }`
      }} />
      <Container>
        <div className="flex items-center justify-between h-14">
          <div className="flex items-center space-x-3">
            <div className="w-9 h-9 bg-muted-foreground/50 rounded animate-pulse" />
            <div className="flex items-center space-x-2">
              <div className="hidden xl:flex items-center gap-2">
                <div className="w-24 h-6 bg-muted-foreground/50 rounded animate-pulse" />
              </div>
              <div className="flex items-center gap-2 xl:hidden">
                <div className="w-8 h-8 bg-muted-foreground/50 rounded animate-pulse" />
              </div>
              <div className="w-12 h-5 bg-muted-foreground/50 rounded-full animate-pulse" />
              <div className="w-10 h-5 bg-muted-foreground/50 rounded-full animate-pulse" />
            </div>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-9 h-9 bg-muted-foreground/50 rounded animate-pulse" />
            <div className="w-9 h-9 bg-muted-foreground/50 rounded animate-pulse" />
            <div className="w-9 h-9 bg-muted-foreground/50 rounded animate-pulse" />
            <div className="w-9 h-9 bg-muted-foreground/50 rounded animate-pulse" />
          </div>
        </div>
      </Container>
    </div>
  );
};

export default HeaderPlaceholder;
