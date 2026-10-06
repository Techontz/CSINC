"use client";

import Link from "next/link";
import { useEffect } from "react";
import { buttonClasses } from "@/components/ui/Button";

export default function Error({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <section className="bg-paper">
      <div className="container-site flex min-h-[60vh] flex-col justify-center py-24">
        <p className="eyebrow text-gold-ink">Something went wrong</p>
        <h1 className="mt-6 max-w-2xl font-serif text-h1 text-navy">We couldn’t load this page.</h1>
        <p className="mt-6 max-w-xl text-lead text-muted">This is usually temporary. Please try again in a moment.</p>
        <div className="mt-10 flex gap-3">
          <button type="button" onClick={reset} className={buttonClasses("solid")}>Try again</button>
          <Link href="/" className={buttonClasses("outline")}>Go home</Link>
        </div>
        {error.digest && <p className="mt-8 text-xs text-muted">Reference: {error.digest}</p>}
      </div>
    </section>
  );
}
