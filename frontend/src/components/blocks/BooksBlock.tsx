import type { BlockMap } from "@/lib/types";
import { BookCarousel } from "@/components/books/BookCarousel";
import { BookGrid } from "@/components/books/BookGrid";
import { Accent } from "@/components/ui/Accent";
import { ArrowLink } from "@/components/ui/Button";
import { Eyebrow, SectionHeading } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";

export function BooksBlock({ data }: { data: BlockMap["books"] }) {
  if (data.books.length === 0) {
    return null;
  }

  const action = data.link_url && data.link_label ? <ArrowLink href={data.link_url}>{data.link_label}</ArrowLink> : undefined;

  if (data.layout === "carousel") {
    return (
      <section className="section-y overflow-hidden bg-white">
        <div className="container-site max-w-[84rem]">
          <BookCarousel
            books={data.books}
            header={
              <Reveal className="max-w-3xl">
                {data.eyebrow && <Eyebrow className="mb-5">{data.eyebrow}</Eyebrow>}
                <h2 className="font-serif text-h2 text-balance text-navy">
                  <Accent text={data.heading} />
                </h2>
                {data.intro && <p className="mt-4 text-lead text-muted">{data.intro}</p>}
                {action && <div className="mt-6">{action}</div>}
              </Reveal>
            }
          />
        </div>
      </section>
    );
  }

  return (
    <section className="section-y bg-white">
      <div className="container-site">
        <SectionHeading eyebrow={data.eyebrow} heading={data.heading} intro={data.intro} size="sm" action={action} />
        <div className="mt-12">
          <BookGrid books={data.books} />
        </div>
      </div>
    </section>
  );
}
