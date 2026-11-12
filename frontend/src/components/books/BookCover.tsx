import Image from "next/image";
import { cn } from "@/lib/cn";
import type { MediaImage } from "@/lib/types";

type Props = {
  title: string;
  category?: string | null;
  /** Kept for API compatibility. Every product uses the same designed box so the range looks consistent. */
  cover?: MediaImage | null;
  sizes?: string;
  preload?: boolean;
  className?: string;
  /** `box`: 3D product box. `flat`: front face only (thumbnails). */
  variant?: "box" | "flat";
};

/**
 * One consistent CSinc91 product box for the whole range: only the title and
 * category change. Built in CSS (no image downloads) and sized with container
 * query units, so it stays crisp from a card thumbnail to the product page.
 */
export function BookCover({ title, category, className, variant = "box" }: Props) {
  if (variant === "flat") {
    return (
      <div className={cn("@container relative aspect-[5/7] overflow-hidden", className)}>
        <Front title={title} category={category} />
      </div>
    );
  }

  return (
    <div className={cn("@container relative w-full", className)} role="img" aria-label={`${title} product box`}>
      <div className="product-scene">
        <div className="product-box">
          <div className="product-face product-front">
            <Front title={title} category={category} />
          </div>
          <div className="product-face product-spine">
            <span className="product-spine-mark" />
            <span className="product-spine-title">{title}</span>
            <span className="product-spine-brand">CSINC91</span>
          </div>
          <div className="product-face product-top" />
        </div>
        <div aria-hidden="true" className="product-shadow" />
      </div>
    </div>
  );
}

function Front({ title, category }: { title: string; category?: string | null }) {
  return (
    <div aria-hidden="true" className="product-front-art absolute inset-0 flex flex-col overflow-hidden text-white">
      {/* Brand arcs and texture */}
      <span className="absolute -right-[38%] -top-[22%] aspect-square w-[105%] rounded-full border-[0.5cqw] border-gold-light/25" />
      <span className="absolute -right-[18%] -top-[6%] aspect-square w-[62%] rounded-full border-[0.5cqw] border-gold-light/35" />
      <span className="absolute -right-[4%] top-[6%] aspect-square w-[30%] rounded-full bg-gold/25 blur-[3cqw]" />
      <span
        className="absolute bottom-[14%] left-0 h-[34%] w-[60%] opacity-30 [mask-image:linear-gradient(to_top_right,black,transparent_70%)]"
        style={{ backgroundImage: "radial-gradient(rgba(231,207,159,0.9) 0.35cqw, transparent 0.45cqw)", backgroundSize: "3.2cqw 3.2cqw" }}
      />

      {/* Header */}
      <div className="relative flex items-center justify-between px-[8%] pt-[8%]">
        <Image src="/brand/csinc91-wordmark-inverse.svg" alt="" width={356} height={96} unoptimized className="h-auto w-[42%]" />
        <span className="rounded-full border-[0.3cqw] border-gold-light/60 px-[2.4cqw] py-[0.9cqw] text-[2.4cqw] font-bold uppercase leading-none tracking-[0.12em] text-gold-light">PDF</span>
      </div>

      {/* Title block */}
      <div className="relative mt-auto px-[8%] pb-[9%]">
        <p className="text-[3.1cqw] font-bold uppercase leading-none tracking-[0.2em] text-gold-light">{category ?? "Startup guide"}</p>
        <span className="mt-[3.5cqw] block h-[0.9cqw] w-[13cqw] rounded-full bg-gold" />
        <p className="mt-[3.5cqw] line-clamp-5 text-[9.4cqw] font-extrabold leading-[1.02] tracking-[-0.03em] text-balance [overflow-wrap:anywhere]">{title}</p>
      </div>

      {/* Gold band */}
      <div className="relative flex items-center justify-between bg-gradient-to-r from-gold via-[#cf9d45] to-gold px-[8%] py-[3.6cqw] text-navy-deep">
        <span className="text-[2.7cqw] font-extrabold uppercase leading-none tracking-[0.14em]">Step-by-step blueprint</span>
        <span className="text-[2.5cqw] font-semibold leading-none">csinc91.com</span>
      </div>
    </div>
  );
}
