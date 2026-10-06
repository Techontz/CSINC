import type { Metadata } from "next";
import { CatalogView } from "@/components/books/CatalogView";
import { findPage, getBooks, getCategories, getSite } from "@/lib/api";
import { buildMetadata } from "@/lib/seo";

type Props = { searchParams: Promise<Record<string, string | string[] | undefined>> };

const first = (value: string | string[] | undefined) => (Array.isArray(value) ? value[0] : value);

export async function generateMetadata({ searchParams }: Props): Promise<Metadata> {
  const [site, page, query] = await Promise.all([getSite(), findPage("products"), searchParams]);
  const filtered = Boolean(first(query.search) || first(query.sort) || first(query.page));

  return buildMetadata(site, {
    title: page?.seo.title ?? "Products",
    description: page?.seo.description,
    path: "/products",
    image: page?.seo.image,
    noIndex: filtered,
  });
}

export default async function ProductsPage({ searchParams }: Props) {
  const query = await searchParams;
  const search = first(query.search)?.slice(0, 100) ?? "";
  const sort = first(query.sort) ?? "default";
  const page = Math.max(1, Number.parseInt(first(query.page) ?? "1", 10) || 1);

  const [cms, categories, books] = await Promise.all([
    findPage("products"),
    getCategories(),
    getBooks({ search, sort, page, per_page: 24 }),
  ]);
  const hero = cms?.blocks.find((block) => block.type === "hero");

  return (
    <CatalogView
      eyebrow={hero?.type === "hero" ? hero.data.eyebrow : cms?.eyebrow}
      heading={hero?.type === "hero" ? hero.data.heading : "Startup guides, workbooks & templates"}
      intro={hero?.type === "hero" ? hero.data.body : cms?.summary}
      categories={categories}
      books={books}
      search={search}
      sort={sort}
      basePath="/products"
      breadcrumbs={[{ label: "Products", href: "/products" }]}
    />
  );
}
