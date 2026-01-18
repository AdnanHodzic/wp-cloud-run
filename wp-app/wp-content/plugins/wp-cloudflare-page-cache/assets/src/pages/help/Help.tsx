import Button from "@/components/Button";
import TransitionWrapper from "@/common/TransitionWrapper";
import Container from "@/layout/Container";
import Header, { HeaderRight } from "@/layout/Header";
import PageWrap from "@/layout/PageWrap";
import { LINKS } from "@/lib/constants";
import { __ } from "@wordpress/i18n";
import { ExternalLink } from "lucide-react";
import { DocumentationCategories, GetHelp, Hero, PopularArticles, SupportChannels } from "./components";
import BlackFridayBanner from "@/common/BlackFridayBanner";

const Help = () => {
  return (
    <PageWrap>
      <Header>
        <HeaderRight>
          <Button href={LINKS.DOCS} target="_blank" variant="link" size="sm" className="text-orange-600 hover:text-orange-700 dark:text-orange-500 dark:hover:text-orange-400">
            <ExternalLink />
            {__('Full Documentation', 'wp-cloudflare-page-cache')}
          </Button>
        </HeaderRight>
      </Header>

      <Container className="max-w-5xl py-8">
        <TransitionWrapper from="top">
          <Hero />
        </TransitionWrapper>

        <BlackFridayBanner />

        <TransitionWrapper from="bottom">
          <PopularArticles />
        </TransitionWrapper>
        <TransitionWrapper from="bottom" className="delay-200">
          <DocumentationCategories />
          <SupportChannels />
          <GetHelp />
        </TransitionWrapper>
      </Container>

    </PageWrap >
  );
};

export default Help;