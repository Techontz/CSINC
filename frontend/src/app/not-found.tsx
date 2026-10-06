import Link from "next/link";
import { ButtonLink } from "@/components/ui/Button";

export default function NotFound() {
  return (
    <section className="relative overflow-hidden bg-navy text-white">
      <div className="container-site flex min-h-[70vh] flex-col justify-center py-24">
        <p className="eyebrow text-gold-light">Error 404</p>
        <h1 className="mt-6 max-w-3xl font-serif text-display">
          This page has <em className="accent text-gold-light">moved on</em>.
        </h1>
        <p className="mt-8 max-w-xl text-lead text-white/75">The page you are looking for may have been renamed or no longer exists. Here are a few good places to continue.</p>
        <div className="mt-12 flex flex-col gap-3 sm:flex-row">
          <ButtonLink href="/" variant="gold">Back to home</ButtonLink>
          <ButtonLink href="/products" variant="outline-light">Browse products</ButtonLink>
        </div>
        <p className="mt-10 text-sm text-white/60">
          Need help? <Link href="/contact-us" className="text-white underline decoration-gold-light underline-offset-4">Contact our team</Link>.
        </p>
      </div>
      <span aria-hidden="true" className="pointer-events-none absolute -right-40 top-1/2 hidden h-[40rem] w-[40rem] -translate-y-1/2 rounded-full border border-white/10 md:block" />
      <span aria-hidden="true" className="pointer-events-none absolute -right-20 top-1/2 hidden h-[26rem] w-[26rem] -translate-y-1/2 rounded-full border border-gold-light/20 md:block" />
    </section>
  );
}
