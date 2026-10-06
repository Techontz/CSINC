import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import { OrderStatusWatcher } from "@/components/cart/OrderStatusWatcher";
import { ButtonLink } from "@/components/ui/Button";
import { Check, Download } from "@/components/ui/Icons";
import { getOrder } from "@/lib/api";

type Props = {
  params: Promise<{ uuid: string }>;
  searchParams: Promise<{ token?: string; checkout?: string }>;
};

export const metadata: Metadata = { title: "Your order", robots: { index: false, follow: false } };

export default async function OrderPage({ params, searchParams }: Props) {
  const [{ uuid }, { token, checkout }] = await Promise.all([params, searchParams]);

  if (!token) {
    notFound();
  }

  const order = await getOrder(uuid, token);
  const paid = order.status === "paid";
  const pending = order.status === "pending";

  return (
    <section className="min-h-[70vh] bg-paper">
      <OrderStatusWatcher pending={pending} clearCart={checkout === "success" && (paid || pending)} />
      <div className="container-site max-w-4xl py-16 md:py-24">
        <p className="eyebrow text-gold-ink">Order {order.number}</p>
        <h1 className="mt-6 font-serif text-h1 text-navy">
          {paid && <>Thank you, {order.customer_name.split(" ")[0]}.</>}
          {pending && "Confirming your payment…"}
          {!paid && !pending && `Order ${order.status_label.toLowerCase()}`}
        </h1>
        <p className="mt-6 max-w-2xl text-lead text-muted">
          {paid && <>Your payment of {order.total} was received. Your guides are ready below, and a copy of these links has been emailed to {order.customer_email}.</>}
          {pending && "This usually takes a few seconds. This page refreshes automatically — please keep it open."}
          {!paid && !pending && "This order is not eligible for downloads. If you believe this is a mistake, please contact our support team."}
        </p>

        <ul className="mt-14 divide-y divide-line border-y border-line bg-white">
          {order.items.map((item) => (
            <li key={item.title} className="flex flex-col gap-5 p-6 sm:flex-row sm:items-center">
              <div className="relative h-24 w-16 shrink-0 overflow-hidden bg-mist">
                {item.cover && <Image src={item.cover} alt="" fill sizes="64px" className="object-cover" />}
              </div>
              <div className="flex-1">
                <p className="font-serif text-xl text-navy">{item.title}</p>
                <p className="mt-1 text-sm text-muted">{item.price}</p>
              </div>
              {item.download_url ? (
                <a href={item.download_url} className="inline-flex items-center justify-center gap-2 bg-navy px-5 py-3 text-[0.75rem] font-semibold uppercase tracking-[0.14em] text-white transition-colors hover:bg-navy-deep">
                  <Download size={16} /> Download PDF
                </a>
              ) : paid ? (
                <span className="text-sm text-muted">File being prepared — we will email you.</span>
              ) : null}
            </li>
          ))}
        </ul>

        {paid && (
          <ul className="mt-10 grid gap-3 text-sm text-ink/80 sm:grid-cols-2">
            {["Links on this page refresh each visit", "Download links in emails expire for security", "Bookmark this page to download again", "Practitioner support: reply to your receipt email"].map((note) => (
              <li key={note} className="flex items-center gap-2.5">
                <Check size={16} className="text-gold" /> {note}
              </li>
            ))}
          </ul>
        )}

        <div className="mt-14 flex flex-col gap-3 sm:flex-row">
          <ButtonLink href="/products" variant="outline">Continue browsing</ButtonLink>
          <ButtonLink href="/contact-us" variant="outline">Contact support</ButtonLink>
        </div>
        <p className="mt-10 text-sm text-muted">
          Lost this page? <Link href="/downloads" className="font-medium text-navy underline decoration-gold underline-offset-4">Request fresh download links</Link>.
        </p>
      </div>
    </section>
  );
}
