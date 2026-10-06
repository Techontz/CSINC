import type { Metadata, Viewport } from "next";
import { Inter, Newsreader } from "next/font/google";
import { CartProvider } from "@/components/cart/CartProvider";
import { SiteFooter } from "@/components/layout/SiteFooter";
import { SiteHeader } from "@/components/layout/SiteHeader";
import { JsonLd } from "@/components/ui/JsonLd";
import { getSite } from "@/lib/api";
import { organizationSchema } from "@/lib/seo";
import { SITE_URL } from "@/lib/site-url";
import "./globals.css";

const inter = Inter({ subsets: ["latin"], variable: "--font-inter", display: "swap" });
const newsreader = Newsreader({
  subsets: ["latin"],
  variable: "--font-newsreader",
  style: ["normal", "italic"],
  weight: ["400", "500"],
  display: "swap",
});

export const viewport: Viewport = {
  themeColor: "#00294c",
  width: "device-width",
  initialScale: 1,
};

export async function generateMetadata(): Promise<Metadata> {
  const site = await getSite();

  return {
    metadataBase: new URL(SITE_URL),
    title: { default: site.seo.default_title, template: site.seo.title_template },
    description: site.seo.default_description,
    applicationName: site.name,
    openGraph: {
      siteName: site.name,
      type: "website",
      locale: "en_US",
      images: site.seo.image ? [{ url: site.seo.image.url, alt: site.seo.image.alt }] : undefined,
    },
    twitter: { card: "summary_large_image" },
    formatDetection: { telephone: false },
  };
}

export default async function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  const site = await getSite();

  return (
    <html lang="en" className={`${inter.variable} ${newsreader.variable}`}>
      <body>
        <a href="#main" className="sr-only z-[100] bg-gold px-4 py-3 font-semibold text-navy-deep focus:not-sr-only focus:fixed focus:left-4 focus:top-4">
          Skip to content
        </a>
        <JsonLd data={organizationSchema(site)} />
        <CartProvider>
          <SiteHeader site={site} />
          <main id="main" className="min-h-[60vh] pt-[4.5rem] lg:pt-[4.6875rem]">
            {children}
          </main>
          <SiteFooter site={site} />
        </CartProvider>
      </body>
    </html>
  );
}
