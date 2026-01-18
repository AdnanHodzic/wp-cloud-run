import Card, { CardContent } from "@/components/Card";
import { cn, getBlackFridayBannerMarkup } from "@/lib/utils";
import TransitionWrapper from "./TransitionWrapper";

const BlackFridayBanner = ({ className }: { className?: string }) => {
  const markup = getBlackFridayBannerMarkup();

  if (!markup) {
    return null;
  }

  return (
    <TransitionWrapper from="fade" className={cn("delay-300 mb-6", className)}>
      <Card className="shadow-xl shadow-orange-500/10 border-orange-500/30">
        <CardContent>
          <div dangerouslySetInnerHTML={{ __html: markup }} />
        </CardContent>
      </Card>
    </TransitionWrapper>
  );
}

export default BlackFridayBanner;