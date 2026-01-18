import Container from "@/layout/Container";
import Header from "@/layout/Header";
import PageWrap from "@/layout/PageWrap";
import ImportCard from "./components/ImportCard";
import ExportCard from "./components/ExportCard";
import DetailsCard from "./components/DetailsCard";
import TransitionWrapper from "@/common/TransitionWrapper";
import BlackFridayBanner from "@/common/BlackFridayBanner";

const ImportExport = () => {
  return (
    <PageWrap>
      <Header />

      <Container className="py-8">
        <BlackFridayBanner />
        <div className="flex flex-col lg:flex-row items-start gap-8">
          <TransitionWrapper className="delay-300" from="top">
            <ExportCard />
          </TransitionWrapper>
          <TransitionWrapper className="delay-400" from="top">
            <ImportCard />
          </TransitionWrapper>
        </div>

        <TransitionWrapper className="mt-8 delay-600" from="bottom">
          <DetailsCard />
        </TransitionWrapper>
      </Container>

    </PageWrap>
  );
};

export default ImportExport;