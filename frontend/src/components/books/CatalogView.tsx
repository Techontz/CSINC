import Link from "next/link";
import { Suspense } from "react";
import type { Book, BookCategory, Paginated } from "@/lib/types";
import { cn } from "@/lib/cn";
import { Accent } from "@/components/ui/Accent";
import { BreadcrumbBar, type Crumb } from "@/components/ui/Breadcrumbs";
import { ButtonLink } from "@/components/ui/Button";
import { ArrowRight, Search } from "@/components/ui/Icons";
import { JsonLd } from "@/components/ui/JsonLd";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { absoluteUrl } from "@/lib/site-url";
import { BookGrid } from "./BookGrid";
import { SortSelect } from "./SortSelect";

type Props = {
  eyebrow?: string | null;
  heading: string;
  intro?: string | null;
  categories: BookCategory[];
  activeCategory?: string;
  books: Paginated<Book>;
  search: string;
  sort: string;
  basePath: string;
  breadcrumbs: Crumb[];
};

function pageHref(basePath: string, params: { search?: string; sort?: string; page?: number }): string {
  const query = new URLSearchParams();
  if (params.search) query.set("search", params.search);
  if (params.sort && params.sort !== "default") query.set("sort", params.sort);
  if (params.page && params.page > 1) query.set("page", String(params.page));
  const qs = query.toString();
  return qs ? `${basePath}?${qs}` : basePath;
}

export function CatalogView({ eyebrow, heading, intro, categories, activeCategory, books, search, sort, basePath, breadcrumbs }: Props) {
  const { meta } = books;
  const total = categories.reduce((sum, category) => sum + (category.books_count ?? 0), 0);

  return (
    <>
      <JsonLd
        data={{
          "@context": "https://schema.org",
          "@type": "ItemList",
          name: heading.replace(/\*/g, ""),
          numberOfItems: meta.total,
          itemListElement: books.data.map((book, index) => ({
            "@type": "ListItem",
            position: (meta.from ?? 1) + index,
            url: absoluteUrl(`/products/${book.slug}`),
            name: book.title,
          })),
        }}
      />

      <BreadcrumbBar items={breadcrumbs} />
      <section className="bg-white">
        <div className="container-site pb-12 pt-12 md:pb-16 md:pt-16">
          <div className="max-w-4xl">
            {eyebrow && <Eyebrow edge className="mb-5">{eyebrow}</Eyebrow>}
            <h1 className="font-serif text-h1 text-navy">
              <Accent text={heading} />
            </h1>
            {intro && <p className="mt-6 max-w-2xl text-lead text-muted">{intro}</p>}
          </div>
        </div>
      </section>

      <div className="border-y border-line bg-white">
        <div className="container-site flex flex-col gap-4 py-4 lg:flex-row lg:items-center lg:justify-between">
          <nav aria-label="Product categories" className="-mx-1 overflow-x-auto">
            <ul className="flex min-w-max gap-1 px-1">
              <CategoryTab href={pageHref("/products", { search, sort })} active={!activeCategory} label="All" count={total} />
              {categories.map((category) => (
                <CategoryTab
                  key={category.slug}
                  href={pageHref(`/product-category/${category.slug}`, { search, sort })}
                  active={activeCategory === category.slug}
                  label={category.name}
                  count={category.books_count}
                />
              ))}
            </ul>
          </nav>
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
            <form action={basePath} method="get" role="search" className="relative flex-1 sm:w-80 sm:flex-none">
              <label htmlFor="catalog-search" className="sr-only">Search products</label>
              <Search className="pointer-events-none absolute left-0 top-1/2 -translate-y-1/2 text-navy/50" />
              <input
                id="catalog-search"
                type="search"
                name="search"
                defaultValue={search}
                placeholder="Search guides, e.g. “group home”"
                maxLength={100}
                className="w-full border-0 border-b border-navy/20 bg-transparent py-2.5 pl-8 pr-2 text-[0.95rem] focus:border-navy focus:outline-none focus:ring-0"
              />
              {sort !== "default" && <input type="hidden" name="sort" value={sort} />}
            </form>
            <Suspense fallback={null}>
              <SortSelect value={sort} />
            </Suspense>
          </div>
        </div>
      </div>

      <section className="section-y !pt-12 md:!pt-16">
        <div className="container-site">
          <p className="mb-10 text-sm text-muted" aria-live="polite">
            {meta.total === 0
              ? "No products found"
              : `Showing ${meta.from}–${meta.to} of ${meta.total} product${meta.total === 1 ? "" : "s"}`}
            {search && (
              <>
                {" "}for “<span className="font-medium text-navy">{search}</span>” ·{" "}
                <Link href={pageHref(basePath, { sort })} className="font-medium text-navy underline decoration-gold underline-offset-4">Clear search</Link>
              </>
            )}
          </p>

          <h2 className="sr-only">Products</h2>
          {books.data.length > 0 ? (
            <BookGrid books={books.data} />
          ) : (
            <div className="flex flex-col items-start border border-line bg-paper p-10 md:p-16">
              <p className="font-serif text-h3 text-navy">We couldn’t find a match.</p>
              <p className="mt-4 max-w-lg text-muted">Try a different keyword, browse all products, or tell us what you are looking for and we will point you to the right resource.</p>
              <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                <ButtonLink href="/products">Browse all products</ButtonLink>
                <ButtonLink href="/contact-us" variant="outline">Ask our team</ButtonLink>
              </div>
            </div>
          )}

          {meta.last_page > 1 && (
            <nav aria-label="Pagination" className="mt-20 flex items-center justify-between border-t border-line pt-8">
              <PageLink href={meta.current_page > 1 ? pageHref(basePath, { search, sort, page: meta.current_page - 1 }) : null} label="Previous" />
              <ol className="flex gap-1">
                {Array.from({ length: meta.last_page }, (_, i) => i + 1).map((number) => (
                  <li key={number}>
                    <Link
                      href={pageHref(basePath, { search, sort, page: number })}
                      aria-current={number === meta.current_page ? "page" : undefined}
                      className={cn(
                        "flex h-10 w-10 items-center justify-center text-sm font-medium transition-colors",
                        number === meta.current_page ? "bg-navy text-white" : "text-navy hover:bg-mist",
                      )}
                    >
                      {number}
                    </Link>
                  </li>
                ))}
              </ol>
              <PageLink href={meta.current_page < meta.last_page ? pageHref(basePath, { search, sort, page: meta.current_page + 1 }) : null} label="Next" next />
            </nav>
          )}
        </div>
      </section>
    </>
  );
}

function CategoryTab({ href, active, label, count }: { href: string; active: boolean; label: string; count?: number }) {
  return (
    <li>
      <Link
        href={href}
        aria-current={active ? "page" : undefined}
        className={cn(
          "inline-flex items-center gap-2 px-4 py-2.5 text-[0.75rem] font-semibold uppercase tracking-[0.14em] transition-colors",
          active ? "bg-navy text-white" : "text-navy hover:bg-mist",
        )}
      >
        {label}
        {typeof count === "number" && <span className={cn("text-[0.7rem]", active ? "text-gold-light" : "text-muted")}>{count}</span>}
      </Link>
    </li>
  );
}

function PageLink({ href, label, next = false }: { href: string | null; label: string; next?: boolean }) {
  const content = (
    <span className={cn("inline-flex items-center gap-2 text-[0.75rem] font-semibold uppercase tracking-[0.14em]", next ? "" : "flex-row-reverse")}>
      {label}
      <ArrowRight size={15} className={cn("arrow-shift-x", !next && "rotate-180")} />
    </span>
  );

  return href ? (
    <Link href={href} className="group text-navy" rel={next ? "next" : "prev"}>{content}</Link>
  ) : (
    <span className="text-navy/30" aria-disabled="true">{content}</span>
  );
}
