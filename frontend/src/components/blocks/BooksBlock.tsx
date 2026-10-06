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
      <section className="overflow-hidden bg-gold-light py-16 md:py-24 [&_.book-cat]:text-navy/80">
        <div className="container-site">
          <Reveal className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div className="max-w-3xl">
              {data.eyebrow && <Eyebrow className="mb-4 !text-navy/70">{data.eyebrow}</Eyebrow>}
              <h2 className="font-serif text-h2 text-navy [&_em]:text-navy">
                <Accent text={data.heading} />
              </h2>
              {data.intro && <p className="mt-4 text-lead text-navy/75">{data.intro}</p>}
            </div>
            {action}
          </Reveal>
          <div className="mt-12">
            <BookCarousel books={data.books} />
          </div>
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
