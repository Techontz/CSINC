import type { Metadata } from "next";
import { CartView } from "@/components/cart/CartView";
import { BreadcrumbBar } from "@/components/ui/Breadcrumbs";
import { getSite } from "@/lib/api";
import { buildMetadata } from "@/lib/seo";

export async function generateMetadata(): Promise<Metadata> {
  const site = await getSite();
  return buildMetadata(site, { title: "Your cart", path: "/cart", noIndex: true });
}

export default async function CartPage() {
  const site = await getSite();
  const terms = site.navigation.legal;

  return (
    <>
    <BreadcrumbBar items={[{ label: "Cart", href: "/cart" }]} />
    <section className="min-h-[70vh] bg-white">
      <div className="container-site pb-24 pt-12 md:pt-16">
        <h1 className="font-serif text-h1 text-navy">Your cart</h1>
        <CartView
          checkoutEnabled={site.commerce.enabled}
          checkoutNote={site.commerce.checkout_note}
          legalLinks={terms}
          supportEmail={site.contact.support_email ?? site.contact.email}
        />
      </div>
    </section>
    </>
  );
}
