import Link from "next/link";
import type { BlockMap, BookCategory } from "@/lib/types";
import { ArrowUpRight } from "@/components/ui/Icons";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";

export function CategoryTiles({ categories }: { categories: BookCategory[] }) {
  return (
    <ul className="grid gap-5 md:grid-cols-2">
      {categories.map((category, index) => (
        <Reveal as="li" key={category.slug} delay={index * 120}>
          <Link
            href={`/product-category/${category.slug}`}
            className="group relative flex h-full min-h-[22rem] flex-col justify-between overflow-hidden bg-navy p-8 text-white transition-colors duration-500 hover:bg-navy-deep md:p-12"
          >
            <div className="flex items-start justify-between gap-6">
              <p className="eyebrow text-gold-light">{category.name}</p>
              <span className="flex h-12 w-12 shrink-0 items-center justify-center border border-white/25 transition-colors group-hover:border-gold-light group-hover:text-gold-light">
                <ArrowUpRight className="arrow-shift" />
              </span>
            </div>
            <div>
              {typeof category.books_count === "number" && (
                <p className="font-serif text-[clamp(4.5rem,3.5rem+3vw,7rem)] leading-none tracking-[-0.04em] text-white/95">{category.books_count}</p>
              )}
              <p className="mt-5 max-w-md font-serif text-[1.6rem] leading-tight">{category.headline ?? category.name}</p>
              {category.description && <p className="mt-3 max-w-md text-sm leading-relaxed text-white/65">{category.description}</p>}
            </div>
            <span aria-hidden="true" className="pointer-events-none absolute -bottom-28 -right-28 h-72 w-72 rounded-full border border-white/10 transition-transform duration-700 group-hover:scale-110" />
            <span aria-hidden="true" className="pointer-events-none absolute -bottom-16 -right-16 h-48 w-48 rounded-full border border-gold-light/20 transition-transform duration-700 group-hover:scale-110" />
          </Link>
        </Reveal>
      ))}
    </ul>
  );
}

export function BookCategoriesBlock({ data }: { data: BlockMap["book_categories"] }) {
  if (data.categories.length === 0) {
    return null;
  }

  return (
    <section className="section-y bg-paper">
      <div className="container-site">
        <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
        <div className="mt-16">
          <CategoryTiles categories={data.categories} />
        </div>
      </div>
    </section>
  );
}
