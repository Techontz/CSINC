import Link from "next/link";
import { absoluteUrl } from "@/lib/site-url";
import { cn } from "@/lib/cn";
import { JsonLd } from "./JsonLd";

export type Crumb = { label: string; href: string };

/** Full-width light strip under the header, as on the reference inner pages. */
export function BreadcrumbBar({ items }: { items: Crumb[] }) {
  return (
    <div className="bg-[#eef3f9]">
      <Breadcrumbs items={items} className="container-site py-2.5" compact />
    </div>
  );
}

export function Breadcrumbs({ items, tone = "dark", className, compact = false }: { items: Crumb[]; tone?: "dark" | "light"; className?: string; compact?: boolean }) {
  const all = [{ label: "Home", href: "/" }, ...items];

  return (
    <nav aria-label="Breadcrumb" className={className}>
      <JsonLd
        data={{
          "@context": "https://schema.org",
          "@type": "BreadcrumbList",
          itemListElement: all.map((item, index) => ({
            "@type": "ListItem",
            position: index + 1,
            name: item.label,
            item: absoluteUrl(item.href),
          })),
        }}
      />
      <ol
        className={cn(
          "flex flex-wrap items-center gap-x-2 gap-y-1",
          compact ? "text-[0.8125rem] font-medium" : "text-xs font-medium uppercase tracking-[0.14em]",
          tone === "dark" ? (compact ? "text-[#1a56a0]" : "text-muted") : "text-white/60",
        )}
      >
        {all.map((item, index) => (
          <li key={item.href} className="flex items-center gap-2">
            {index > 0 && (compact ? (
              <svg aria-hidden="true" width="7" height="11" viewBox="0 0 7 11" className="text-navy/60"><path d="m1 1 4.5 4.5L1 10" fill="none" stroke="currentColor" strokeWidth="1.4" /></svg>
            ) : (
              <span aria-hidden="true" className="text-gold">/</span>
            ))}
            {index === all.length - 1 ? (
              <span aria-current="page" className={tone === "dark" ? "text-navy" : "text-white"}>{item.label}</span>
            ) : (
              <Link href={item.href} className="transition-colors hover:text-navy hover:underline">{item.label}</Link>
            )}
          </li>
        ))}
      </ol>
    </nav>
  );
}
