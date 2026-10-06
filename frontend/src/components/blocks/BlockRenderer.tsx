import type { ConsultationOptions, PageBlock, SiteData } from "@/lib/types";
import type { Crumb } from "@/components/ui/Breadcrumbs";
import { BookCategoriesBlock } from "./BookCategoriesBlock";
import { BooksBlock } from "./BooksBlock";
import { ContactBlock } from "./ContactBlock";
import { CtaBand } from "./CtaBand";
import { Faq } from "./Faq";
import { Hero } from "./Hero";
import { HeroSlider } from "./HeroSlider";
import { Pillars } from "./Pillars";
import { Results } from "./Results";
import { ServiceLinks } from "./ServiceLinks";
import { RichTextBlock } from "./RichTextBlock";
import { Services } from "./Services";
import { Split } from "./Split";
import { Stats } from "./Stats";
import { Statement } from "./Statement";
import { Values } from "./Values";

type Context = { site: SiteData; options?: ConsultationOptions; breadcrumbs?: Crumb[]; aside?: React.ReactNode };

/** Maps CMS page blocks onto section components, in the order editors arranged them. */
export function BlockRenderer({ blocks, context }: { blocks: PageBlock[]; context: Context }) {
  return (
    <>
      {blocks.map((block, index) => {
        switch (block.type) {
          case "hero_slider":
            return <HeroSlider key={block.id} data={block.data} />;
          case "hero":
            return <Hero key={block.id} data={block.data} isFirst={index === 0} breadcrumbs={index === 0 ? context.breadcrumbs : undefined} />;
          case "statement":
            return <Statement key={block.id} data={block.data} />;
          case "pillars":
            return <Pillars key={block.id} data={block.data} />;
          case "service_links":
            return <ServiceLinks key={block.id} data={block.data} />;
          case "results":
            return <Results key={block.id} data={block.data} />;
          case "services":
            return <Services key={block.id} data={block.data} />;
          case "books":
            return <BooksBlock key={block.id} data={block.data} />;
          case "book_categories":
            return <BookCategoriesBlock key={block.id} data={block.data} />;
          case "split":
            return <Split key={block.id} data={block.data} />;
          case "stats":
            return <Stats key={block.id} data={block.data} />;
          case "values":
            return <Values key={block.id} data={block.data} />;
          case "faq":
            return <Faq key={block.id} data={block.data} />;
          case "cta_band":
            return <CtaBand key={block.id} data={block.data} />;
          case "rich_text":
            return <RichTextBlock key={block.id} data={block.data} aside={context.aside} />;
          case "contact":
            return context.options ? <ContactBlock key={block.id} data={block.data} site={context.site} options={context.options} /> : null;
          default:
            return null;
        }
      })}
    </>
  );
}
