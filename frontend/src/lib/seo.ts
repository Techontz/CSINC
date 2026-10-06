import type { Metadata } from "next";
import { stripAccent } from "@/components/ui/Accent";
import { absoluteUrl } from "./site-url";
import type { MediaImage, SiteData } from "./types";

type SeoInput = {
  title: string;
  description?: string | null;
  path: string;
  image?: MediaImage | null;
  type?: "website" | "article";
  noIndex?: boolean;
};

/** Builds consistent page metadata: title, description, canonical, Open Graph and X cards. */
export function buildMetadata(site: SiteData, { title, description, path, image, type = "website", noIndex }: SeoInput): Metadata {
  const cleanTitle = stripAccent(title);
  const desc = description ?? site.seo.default_description;
  const ogImage = image ?? site.seo.image;
  const images = ogImage ? [{ url: ogImage.url, width: ogImage.width ?? undefined, height: ogImage.height ?? undefined, alt: ogImage.alt || cleanTitle }] : undefined;

  return {
    title: cleanTitle,
    description: desc,
    alternates: { canonical: absoluteUrl(path) },
    openGraph: {
      type,
      url: absoluteUrl(path),
      title: cleanTitle,
      description: desc,
      siteName: site.name,
      locale: "en_US",
      images,
    },
    twitter: {
      card: ogImage ? "summary_large_image" : "summary",
      title: cleanTitle,
      description: desc,
      images: ogImage ? [ogImage.url] : undefined,
    },
    robots: noIndex ? { index: false, follow: true } : undefined,
  };
}

export function organizationSchema(site: SiteData): Record<string, unknown> {
  const { address } = site.contact;

  return {
    "@context": "https://schema.org",
    "@type": "ProfessionalService",
    "@id": absoluteUrl("/#organization"),
    name: site.name,
    legalName: site.legal_name,
    description: site.description,
    url: absoluteUrl("/"),
    logo: site.logo?.url ?? absoluteUrl("/brand/logo.png"),
    image: site.seo.image?.url,
    email: site.contact.email,
    telephone: site.contact.phone ?? undefined,
    address: {
      "@type": "PostalAddress",
      streetAddress: [address.line_1, address.line_2].filter(Boolean).join(", "),
      addressLocality: address.city,
      addressRegion: address.region,
      postalCode: address.postal_code,
      addressCountry: address.country,
    },
    sameAs: site.social.map((link) => link.url),
  };
}
