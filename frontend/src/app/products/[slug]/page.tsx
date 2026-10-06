import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { BookCover } from "@/components/books/BookCover";
import { BookGrid } from "@/components/books/BookGrid";
import { PriceTag } from "@/components/books/PriceTag";
import { AddToCartButton } from "@/components/cart/AddToCartButton";
import { BreadcrumbBar } from "@/components/ui/Breadcrumbs";
import { ArrowLink, ButtonLink } from "@/components/ui/Button";
import { Check, Download } from "@/components/ui/Icons";
import { JsonLd } from "@/components/ui/JsonLd";
import { Reveal } from "@/components/ui/Reveal";
import { RichText } from "@/components/ui/RichText";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { getBook, getBooks, getSite } from "@/lib/api";
import { buildMetadata } from "@/lib/seo";
import { absoluteUrl } from "@/lib/site-url";
import type { BookDetail, SiteData } from "@/lib/types";

type Props = { params: Promise<{ slug: string }> };

export const dynamicParams = true;

export async function generateStaticParams() {
  try {
    const books = await getBooks({ per_page: 48 });
    return books.data.map((book) => ({ slug: book.slug }));
  } catch {
    return [];
  }
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const [site, { data: book }] = await Promise.all([getSite(), getBook(slug)]);

  return buildMetadata(site, {
    title: book.seo.title,
    description: book.seo.description,
    path: `/products/${book.slug}`,
    image: book.seo.image,
  });
}

function productSchema(book: BookDetail, site: SiteData): Record<string, unknown> {
  return {
    "@context": "https://schema.org",
    "@type": ["Product", "Book"],
    "@id": absoluteUrl(`/products/${book.slug}#product`),
    name: book.title,
    description: book.seo.description ?? book.short_description ?? undefined,
    image: book.cover?.url,
    sku: book.sku ?? undefined,
    isbn: book.isbn ?? undefined,
    inLanguage: book.language ?? "en",
    bookFormat: "https://schema.org/EBook",
    numberOfPages: book.page_count ?? undefined,
    author: { "@type": "Organization", name: book.author ?? site.name },
    publisher: { "@type": "Organization", name: book.publisher ?? site.name },
    datePublished: book.publication_date ?? undefined,
    brand: { "@type": "Brand", name: site.name },
    category: book.categories.map((category) => category.name).join(", ") || undefined,
    url: absoluteUrl(`/products/${book.slug}`),
    offers: book.price
      ? {
          "@type": "Offer",
          price: (book.price.amount_cents / 100).toFixed(2),
          priceCurrency: book.price.currency,
          availability: book.is_purchasable ? "https://schema.org/InStock" : "https://schema.org/PreOrder",
          url: absoluteUrl(`/products/${book.slug}`),
          seller: { "@type": "Organization", name: site.name },
        }
      : undefined,
  };
}

export default async function ProductPage({ params }: Props) {
  const { slug } = await params;
  const [site, { data: book, related }] = await Promise.all([getSite(), getBook(slug)]);
  const category = book.categories[0];
  const canBuy = book.is_purchasable && site.commerce.enabled;

  const details = [
    ["SKU", book.sku],
    ["Format", book.format],
    ["Language", book.language],
    ["Pages", book.page_count ? String(book.page_count) : null],
    ["Edition", book.edition],
    ["Author", [book.author, ...book.co_authors].filter(Boolean).join(", ") || null],
    ["Publisher", book.publisher],
    ["Published", book.publication_date ? new Date(book.publication_date).toLocaleDateString("en-US", { month: "long", year: "numeric" }) : null],
    ["ISBN", book.isbn],
  ].filter((row): row is [string, string] => Boolean(row[1]));

  return (
    <>
      <JsonLd data={productSchema(book, site)} />

      <BreadcrumbBar
            items={[
              { label: "Products", href: "/products" },
              ...(category ? [{ label: category.name, href: `/product-category/${category.slug}` }] : []),
              { label: book.title, href: `/products/${book.slug}` },
            ]}
          />
      <section className="bg-white">

        <div className="container-site grid gap-12 pb-20 pt-10 lg:grid-cols-12 lg:gap-16 lg:pb-28 lg:pt-14">
          <div className="lg:col-span-6">
            <div className="lg:sticky lg:top-[6.5rem]">
              <Reveal variant="mask" immediate>
                <div className="flex justify-center bg-paper px-[14%] py-10 sm:py-12">
                  <BookCover cover={book.cover} title={book.title} sizes="(min-width: 1024px) 34vw, 76vw" preload className="w-full max-w-[20rem]" />
                </div>
              </Reveal>
              {book.gallery.length > 0 && (
                <ul className="mt-4 grid grid-cols-4 gap-3">
                  {book.gallery.map((image) => (
                    <li key={image.url} className="relative aspect-square overflow-hidden bg-paper">
                      <Image src={image.url} alt={image.alt} fill sizes="(min-width: 1024px) 10vw, 25vw" className="object-cover" />
                    </li>
                  ))}
                </ul>
              )}
            </div>
          </div>

          <div className="lg:col-span-6 lg:pt-6">
            <Reveal immediate>
              {category && <p className="eyebrow text-gold-ink">{category.name}</p>}
              <h1 className="mt-4 font-serif text-h1 text-navy">{book.title}</h1>
              {book.subtitle && <p className="mt-4 font-serif text-2xl italic text-navy/70">{book.subtitle}</p>}
              {book.short_description && <p className="mt-7 text-lead text-muted">{book.short_description}</p>}
            </Reveal>

            <Reveal immediate className="mt-10 border-y border-line py-8">
              <PriceTag price={book.price} size="lg" />
              <div className="mt-7">
                {book.external_purchase_url ? (
                  <ButtonLink href={book.external_purchase_url}>Buy from retailer</ButtonLink>
                ) : canBuy ? (
                  <AddToCartButton book={book} />
                ) : (
                  <div className="space-y-4">
                    <ButtonLink href={`/contact-us?product=${encodeURIComponent(book.title)}`}>Enquire about this guide</ButtonLink>
                    <p className="text-sm text-muted">Online purchase is not available for this guide right now. Our team will help you get started.</p>
                  </div>
                )}
              </div>
              <ul className="mt-8 grid gap-3 text-sm text-ink/80 sm:grid-cols-2">
                {["Instant PDF download", "Practitioner support available", "Single-user business licence", ...(canBuy ? ["Secure checkout by Stripe"] : [])].map((item) => (
                  <li key={item} className="flex items-center gap-2.5">
                    <Check size={16} className="shrink-0 text-gold" />
                    {item}
                  </li>
                ))}
              </ul>
            </Reveal>

            {book.sample_url && (
              <a href={book.sample_url} target="_blank" rel="noopener" className="group mt-8 inline-flex items-center gap-3 text-[0.78rem] font-semibold uppercase tracking-[0.14em] text-navy">
                <Download className="text-gold" />
                <span className="link-underline">Download a free preview</span>
              </a>
            )}

            {book.description && (
              <Reveal className="mt-14">
                <h2 className="eyebrow text-navy">About this guide</h2>
                <RichText html={book.description} className="mt-6" />
              </Reveal>
            )}

            {details.length > 0 && (
              <Reveal className="mt-14">
                <h2 className="eyebrow text-navy">Details</h2>
                <dl className="mt-6 divide-y divide-line border-y border-line">
                  {details.map(([label, value]) => (
                    <div key={label} className="grid grid-cols-3 gap-4 py-4 text-sm">
                      <dt className="text-muted">{label}</dt>
                      <dd className="col-span-2 font-medium text-navy">{value}</dd>
                    </div>
                  ))}
                </dl>
              </Reveal>
            )}

            {book.tags.length > 0 && (
              <ul className="mt-8 flex flex-wrap gap-2">
                {book.tags.map((tag) => (
                  <li key={tag.slug}>
                    <Link href={`/products?search=${encodeURIComponent(tag.name)}`} className="inline-block border border-navy/15 px-3 py-1.5 text-xs text-navy transition-colors hover:border-navy">
                      {tag.name}
                    </Link>
                  </li>
                ))}
              </ul>
            )}

            <div className="mt-14 bg-paper p-8">
              <p className="font-serif text-xl text-navy">Questions before you buy?</p>
              <p className="mt-2 text-sm leading-relaxed text-muted">Our practitioners can help you decide which guide fits your plans.</p>
              <div className="mt-5">
                <ArrowLink href={`/contact-us?product=${encodeURIComponent(book.title)}`}>Talk to our team</ArrowLink>
              </div>
            </div>
          </div>
        </div>
      </section>

      {related.length > 0 && (
        <section className="section-y bg-paper">
          <div className="container-site">
            <SectionHeading
              eyebrow="You may also need"
              heading="Related *startup guides*"
              action={category ? <ArrowLink href={`/product-category/${category.slug}`}>All {category.name.toLowerCase()} guides</ArrowLink> : undefined}
            />
            <div className="mt-14">
              <BookGrid books={related} />
            </div>
          </div>
        </section>
      )}
    </>
  );
}
