import TransitionWrapper from "@/common/TransitionWrapper";

type PageContentProps = {
  children: React.ReactNode;
}

const PageContent = ({ children }: PageContentProps) => {
  return (

    <TransitionWrapper className="delay-100 grid gap-5">
      {children}
    </TransitionWrapper>

  );
}

export default PageContent;