import type { Metadata } from "next";
import { CatalogView } from "@/components/books/CatalogView";
import { getBooks, getCategories, getCategory, getSite } from "@/lib/api";
import { buildMetadata } from "@/lib/seo";

type Props = {
  params: Promise<{ slug: string }>;
  searchParams: Promise<Record<string, string | string[] | undefined>>;
};

const first = (value: string | string[] | undefined) => (Array.isArray(value) ? value[0] : value);

export async function generateMetadata({ params, searchParams }: Props): Promise<Metadata> {
  const [{ slug }, query] = await Promise.all([params, searchParams]);
  const [site, category] = await Promise.all([getSite(), getCategory(slug)]);

  return buildMetadata(site, {
    title: category.seo.title === category.name ? `${category.headline ?? category.name}` : category.seo.title,
    description: category.seo.description,
    path: `/product-category/${slug}`,
    image: category.image,
    noIndex: Boolean(first(query.search) || first(query.sort) || first(query.page)),
  });
}

export default async function ProductCategoryPage({ params, searchParams }: Props) {
  const [{ slug }, query] = await Promise.all([params, searchParams]);
  const search = first(query.search)?.slice(0, 100) ?? "";
  const sort = first(query.sort) ?? "default";
  const page = Math.max(1, Number.parseInt(first(query.page) ?? "1", 10) || 1);

  const [category, categories, books] = await Promise.all([
    getCategory(slug),
    getCategories(),
    getBooks({ category: slug, search, sort, page, per_page: 24 }),
  ]);

  return (
    <CatalogView
      eyebrow={`${category.name} · ${category.books_count ?? books.meta.total} products`}
      heading={category.headline ?? category.name}
      intro={category.description}
      categories={categories}
      activeCategory={slug}
      books={books}
      search={search}
      sort={sort}
      basePath={`/product-category/${slug}`}
      breadcrumbs={[
        { label: "Products", href: "/products" },
        { label: category.name, href: `/product-category/${slug}` },
      ]}
    />
  );
}
