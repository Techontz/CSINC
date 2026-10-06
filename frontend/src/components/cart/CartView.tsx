"use client";

import Image from "next/image";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { Suspense, useState, type FormEvent } from "react";
import type { NavigationLink } from "@/lib/types";
import { TextField } from "@/components/forms/Field";
import { buttonClasses } from "@/components/ui/Button";
import { ArrowUpRight, Close } from "@/components/ui/Icons";
import { formatMoney, useCart } from "./CartProvider";

type Props = { checkoutEnabled: boolean; checkoutNote: string | null; legalLinks: NavigationLink[]; supportEmail: string };

export function CartView(props: Props) {
  return (
    <Suspense fallback={<div className="mt-12 h-64 animate-pulse bg-mist" />}>
      <CartContent {...props} />
    </Suspense>
  );
}

function CartContent({ checkoutEnabled, checkoutNote, legalLinks, supportEmail }: Props) {
  const { items, ready, remove, totalCents } = useCart();
  const cancelled = useSearchParams().get("checkout") === "cancelled";
  const [status, setStatus] = useState<"idle" | "submitting" | "error">("idle");
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [message, setMessage] = useState("");

  if (!ready) {
    return <div className="mt-12 h-64 animate-pulse bg-mist" aria-label="Loading cart" />;
  }

  if (items.length === 0) {
    return (
      <div className="mt-12 border border-line bg-paper p-10 md:p-16">
        <p className="font-serif text-h3 text-navy">Your cart is empty.</p>
        <p className="mt-4 max-w-lg text-muted">Browse step-by-step startup blueprints for healthcare, residential care and general business.</p>
        <Link href="/products" className={buttonClasses("solid", "mt-8")}>
          <span>Browse products</span>
          <ArrowUpRight size={15} className="arrow-shift" />
        </Link>
      </div>
    );
  }

  const currency = items[0]?.currency ?? "USD";

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setStatus("submitting");
    setErrors({});

    try {
      const response = await fetch("/api/checkout", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({
          items: items.map((item) => item.slug),
          name: form.get("name"),
          email: form.get("email"),
          accept_terms: form.get("accept_terms") === "on",
          website: form.get("website"),
        }),
      });
      const body = await response.json().catch(() => ({}));

      if (response.ok && body.data?.checkout_url) {
        window.location.assign(body.data.checkout_url);
        return;
      }

      if (response.status === 422 && body.errors) {
        const mapped: Record<string, string> = {};
        Object.entries(body.errors as Record<string, string[]>).forEach(([key, value]) => {
          mapped[key.split(".")[0]] ??= value[0];
        });
        setErrors(mapped);
        setMessage(mapped.items ?? "Please review the highlighted fields.");
      } else {
        setMessage(body.message ?? "We could not start checkout. Please try again.");
      }
      setStatus("error");
    } catch {
      setStatus("error");
      setMessage("We could not reach the server. Please check your connection and try again.");
    }
  }

  return (
    <div className="mt-12 grid gap-14 lg:grid-cols-12">
      <div className="lg:col-span-7">
        {cancelled && (
          <p role="status" className="mb-8 border-l-2 border-gold bg-paper px-5 py-4 text-sm text-navy">
            Checkout was cancelled — your cart has been saved.
          </p>
        )}
        <ul className="divide-y divide-line border-y border-line">
          {items.map((item) => (
            <li key={item.slug} className="flex items-center gap-5 py-6">
              <Link href={`/products/${item.slug}`} className="relative h-24 w-16 shrink-0 overflow-hidden bg-paper">
                {item.cover && <Image src={item.cover} alt="" fill sizes="64px" className="object-cover" />}
              </Link>
              <div className="min-w-0 flex-1">
                <Link href={`/products/${item.slug}`} className="font-serif text-xl leading-snug text-navy hover:text-gold">{item.title}</Link>
                <p className="mt-1 text-sm text-muted">PDF download · single-user licence</p>
              </div>
              <p className="shrink-0 font-semibold text-navy">{item.formatted_price}</p>
              <button
                type="button"
                onClick={() => remove(item.slug)}
                className="flex h-10 w-10 shrink-0 items-center justify-center text-muted transition-colors hover:text-red-700"
                aria-label={`Remove ${item.title}`}
              >
                <Close size={18} />
              </button>
            </li>
          ))}
        </ul>
        <div className="mt-6 flex items-center justify-between">
          <Link href="/products" className="text-sm font-medium text-navy underline decoration-gold underline-offset-4">Continue browsing</Link>
          <p className="text-lg">
            <span className="text-muted">Total </span>
            <span className="font-serif text-3xl text-navy">{formatMoney(totalCents, currency)}</span>
          </p>
        </div>
      </div>

      <div className="lg:col-span-5">
        <div className="bg-paper p-8 md:p-10 lg:sticky lg:top-[6.5rem]">
          <h2 className="font-serif text-h3 text-navy">Checkout</h2>
          {checkoutEnabled ? (
            <form onSubmit={onSubmit} noValidate className="mt-8 space-y-8">
              <TextField label="Full name" name="name" autoComplete="name" required error={errors.name} />
              <TextField label="Email" name="email" type="email" autoComplete="email" required error={errors.email} hint="Your download links are sent here." />
              <label className="flex cursor-pointer items-start gap-3 text-sm leading-relaxed text-muted">
                <input type="checkbox" name="accept_terms" className="mt-1 h-5 w-5 shrink-0 accent-navy" aria-invalid={Boolean(errors.accept_terms)} />
                <span>
                  I have read and accept the{" "}
                  {legalLinks.map((link, index) => (
                    <span key={link.url}>
                      {index > 0 && " and "}
                      <Link href={link.url} target="_blank" className="font-medium text-navy underline decoration-gold underline-offset-4">{link.label}</Link>
                    </span>
                  ))}
                  , including that digital products are final sale.
                  {errors.accept_terms && <span role="alert" className="mt-1 block text-red-700">{errors.accept_terms}</span>}
                </span>
              </label>
              <div aria-hidden="true" className="absolute -left-[9999px] h-px w-px overflow-hidden">
                <input name="website" tabIndex={-1} autoComplete="off" />
              </div>
              {status === "error" && (
                <p role="alert" className="border-l-2 border-red-700 bg-white px-4 py-3 text-sm text-red-800">{message}</p>
              )}
              <button type="submit" disabled={status === "submitting"} className={buttonClasses("solid", "w-full")}>
                <span>{status === "submitting" ? "Redirecting to secure payment…" : `Pay ${formatMoney(totalCents, currency)}`}</span>
                {status !== "submitting" && <ArrowUpRight size={15} className="arrow-shift" />}
              </button>
              <p className="text-xs leading-relaxed text-muted">
                Payment is processed securely by Stripe. {checkoutNote}
              </p>
            </form>
          ) : (
            <div className="mt-6 space-y-4 text-muted">
              <p>Online checkout is temporarily unavailable.</p>
              <p>
                Email <a href={`mailto:${supportEmail}`} className="font-medium text-navy underline decoration-gold underline-offset-4">{supportEmail}</a> and we will complete your order personally.
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
