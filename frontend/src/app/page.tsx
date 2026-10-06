import type { Metadata } from "next";
import { BlockRenderer } from "@/components/blocks/BlockRenderer";
import { getConsultationOptions, getPage, getSite } from "@/lib/api";
import { buildMetadata } from "@/lib/seo";

export async function generateMetadata(): Promise<Metadata> {
  const [site, page] = await Promise.all([getSite(), getPage("home")]);
  const metadata = buildMetadata(site, { title: page.seo.title, description: page.seo.description, path: "/", image: page.seo.image });

  return { ...metadata, title: { absolute: page.seo.title } };
}

export default async function HomePage() {
  const [site, page] = await Promise.all([getSite(), getPage("home")]);
  const needsOptions = page.blocks.some((block) => block.type === "contact");
  const options = needsOptions ? await getConsultationOptions() : undefined;

  return <BlockRenderer blocks={page.blocks} context={{ site, options }} />;
}
