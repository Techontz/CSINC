import type { Metadata } from "next";
import { redirect } from "next/navigation";
import { BlockRenderer } from "@/components/blocks/BlockRenderer";
import { stripAccent } from "@/components/ui/Accent";
import { getConsultationOptions, getPage, getSitemapIndex, getSite } from "@/lib/api";
import { buildMetadata } from "@/lib/seo";

type Props = { params: Promise<{ slug: string }> };

export const dynamicParams = true;

export async function generateStaticParams() {
  try {
    const { pages } = await getSitemapIndex();
    return pages.filter((page) => page.slug !== "home" && page.slug !== "products").map((page) => ({ slug: page.slug }));
  } catch {
    return [];
  }
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const [site, page] = await Promise.all([getSite(), getPage(slug)]);

  return buildMetadata(site, { title: page.seo.title, description: page.seo.description, path: `/${slug}`, image: page.seo.image });
}

export default async function CmsPage({ params }: Props) {
  const { slug } = await params;

  if (slug === "home") {
    redirect("/");
  }

  const [site, page] = await Promise.all([getSite(), getPage(slug)]);
  const options = page.blocks.some((block) => block.type === "contact") ? await getConsultationOptions() : undefined;
  const isDocument = page.blocks.some((block) => block.type === "rich_text");

  const aside = isDocument ? (
    <div className="border-t-2 border-gold bg-paper p-8 lg:sticky lg:top-[6.5rem]">
      <p className="eyebrow text-navy">Questions?</p>
      <p className="mt-4 text-sm leading-relaxed text-muted">
        Contact our support team at{" "}
        <a className="font-medium text-navy underline decoration-gold underline-offset-4" href={`mailto:${site.contact.support_email ?? site.contact.email}`}>
          {site.contact.support_email ?? site.contact.email}
        </a>
        .
      </p>
      {page.updated_at && (
        <p className="mt-6 text-xs uppercase tracking-[0.14em] text-muted">
          Last updated {new Date(page.updated_at).toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" })}
        </p>
      )}
    </div>
  ) : undefined;

  return (
    <BlockRenderer
      blocks={page.blocks}
      context={{ site, options, aside, breadcrumbs: [{ label: stripAccent(page.title), href: `/${slug}` }] }}
    />
  );
}
