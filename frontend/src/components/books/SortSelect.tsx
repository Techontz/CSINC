"use client";

import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { useTransition } from "react";

const OPTIONS = [
  { value: "default", label: "Featured order" },
  { value: "price_asc", label: "Price: low to high" },
  { value: "price_desc", label: "Price: high to low" },
  { value: "title", label: "Title A–Z" },
  { value: "newest", label: "Newest" },
];

export function SortSelect({ value }: { value: string }) {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [pending, startTransition] = useTransition();

  return (
    <div className="flex items-center gap-3">
      <label htmlFor="catalog-sort" className="shrink-0 text-[0.72rem] font-semibold uppercase tracking-[0.14em] text-muted">
        Sort
      </label>
      <select
        id="catalog-sort"
        value={value}
        aria-busy={pending}
        onChange={(event) => {
          const params = new URLSearchParams(searchParams.toString());
          if (event.target.value === "default") {
            params.delete("sort");
          } else {
            params.set("sort", event.target.value);
          }
          params.delete("page");
          startTransition(() => router.push(params.size ? `${pathname}?${params}` : pathname, { scroll: false }));
        }}
        className="cursor-pointer border-0 border-b border-navy/20 bg-transparent py-2.5 pl-0 pr-8 text-[0.95rem] text-navy focus:border-navy focus:outline-none focus:ring-0"
      >
        {OPTIONS.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
    </div>
  );
}
