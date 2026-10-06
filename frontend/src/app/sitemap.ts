import type { MetadataRoute } from "next";
import { getSitemapIndex } from "@/lib/api";
import { absoluteUrl } from "@/lib/site-url";

export const revalidate = 3600;

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const index = await getSitemapIndex();
  const date = (value: string | null) => (value ? new Date(value) : new Date());

  return [
    ...index.pages.map((page) => ({
      url: absoluteUrl(page.slug === "home" ? "/" : `/${page.slug}`),
      lastModified: date(page.updated_at),
      changeFrequency: "monthly" as const,
      priority: page.slug === "home" ? 1 : page.slug === "products" ? 0.9 : 0.7,
    })),
    ...index.categories.map((category) => ({
      url: absoluteUrl(`/product-category/${category.slug}`),
      lastModified: date(category.updated_at),
      changeFrequency: "weekly" as const,
      priority: 0.8,
    })),
    ...index.books.map((book) => ({
      url: absoluteUrl(`/products/${book.slug}`),
      lastModified: date(book.updated_at),
      changeFrequency: "weekly" as const,
      priority: 0.8,
    })),
  ];
}
