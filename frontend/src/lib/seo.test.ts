import { describe, expect, it } from "vitest";
import { buildMetadata, organizationSchema } from "./seo";
import type { SiteData } from "./types";

const site = {
  name: "CSinc91",
  legal_name: "CSinc91",
  tagline: "",
  description: "Consulting",
  logo: null,
  logo_inverse: null,
  contact: {
    email: "Info@CSinc91.com",
    support_email: null,
    phone: null,
    address: { line_1: "8735 Dunwoody Place, Ste N", line_2: null, city: "Atlanta", region: "GA", postal_code: "30350", country: "USA" },
    hours: null,
    consultation_url: null,
  },
  social: [{ platform: "linkedin", url: "https://linkedin.com/company/csinc91" }],
  seo: { title_template: "%s — CSinc91", default_title: "CSinc91", default_description: "Default description", image: null },
  footer: { statement: null },
  commerce: { enabled: true, currency: "USD", checkout_note: null },
  navigation: { header: [], footer: [], legal: [] },
} satisfies SiteData;

describe("buildMetadata", () => {
  it("builds canonical, Open Graph and Twitter metadata", () => {
    const metadata = buildMetadata(site, {
      title: "Group Home *Startup*",
      description: "Licensed group home guide",
      path: "/products/group-home-startup",
      image: { url: "https://api.example.com/storage/cover.jpg", alt: "Cover", width: 1152, height: 1728 },
    });

    expect(metadata.title).toBe("Group Home Startup");
    expect(metadata.alternates?.canonical).toMatch(/\/products\/group-home-startup$/);
    expect(metadata.openGraph).toMatchObject({ title: "Group Home Startup", description: "Licensed group home guide" });
    expect(metadata.twitter).toMatchObject({ card: "summary_large_image" });
  });

  it("falls back to the site description and supports noindex", () => {
    const metadata = buildMetadata(site, { title: "Cart", path: "/cart", noIndex: true });

    expect(metadata.description).toBe("Default description");
    expect(metadata.robots).toEqual({ index: false, follow: true });
  });
});

describe("organizationSchema", () => {
  it("describes the organisation with its address and profiles", () => {
    const schema = organizationSchema(site);

    expect(schema).toMatchObject({ name: "CSinc91", legalName: "CSinc91", sameAs: ["https://linkedin.com/company/csinc91"] });
    expect(schema.address).toMatchObject({ addressLocality: "Atlanta", postalCode: "30350" });
  });
});
