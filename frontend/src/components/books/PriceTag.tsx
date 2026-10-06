import { cn } from "@/lib/cn";
import type { Price } from "@/lib/types";

export function PriceTag({ price, size = "sm" }: { price: Price | null; size?: "sm" | "lg" }) {
  if (!price) {
    return <span className="text-sm font-medium text-muted">Enquire for pricing</span>;
  }

  return (
    <span className={cn("inline-flex items-baseline gap-2.5", size === "lg" ? "text-3xl" : "text-[0.9375rem]")}>
      <span className={cn("font-semibold text-navy", size === "lg" && "font-serif font-medium")}>{price.is_free ? "Free" : price.formatted}</span>
      {price.on_sale && <s className={cn("text-muted", size === "lg" ? "text-lg" : "text-sm")}>{price.formatted_regular}</s>}
    </span>
  );
}
