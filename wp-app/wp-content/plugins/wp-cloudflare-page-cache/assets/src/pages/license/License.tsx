import Container from "@/layout/Container";
import Header from "@/layout/Header";
import PageWrap from "@/layout/PageWrap";
import LicenseCard from "./components/LicenseCard";
import PurchaseCard from "./components/PurchaseCard";
import BlackFridayBanner from "@/common/BlackFridayBanner";
import TransitionWrapper from "@/common/TransitionWrapper";
import { useAppStore } from "@/store/store";

const License = () => {
  const { validLicense } = useAppStore();
  return (
    <PageWrap>
      <Header />
      <Container className="pt-8">
        <BlackFridayBanner className="mb-0" />
      </Container>
      <Container className="max-w-2xl px-6 py-8 space-y-6">
        <TransitionWrapper className="delay-300">
          <LicenseCard />
        </TransitionWrapper>
        {!validLicense && (
          <TransitionWrapper className="delay-400">
            <PurchaseCard />
          </TransitionWrapper>
        )}
      </Container>
    </PageWrap>
  )
};

export default License;