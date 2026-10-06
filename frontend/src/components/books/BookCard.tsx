import Link from "next/link";
import type { Book } from "@/lib/types";
import { ArrowUpRight } from "@/components/ui/Icons";
import { BookCover } from "./BookCover";
import { PriceTag } from "./PriceTag";

export function BookCard({ book, preload = false }: { book: Book; preload?: boolean }) {
  return (
    <article className="group relative flex h-full flex-col">
      <BookCover cover={book.cover} title={book.title} sizes="(min-width: 1280px) 15vw, (min-width: 768px) 22vw, 45vw" preload={preload} />
      <div className="flex flex-1 flex-col pt-4">
        {book.categories[0] && <p className="book-cat text-[0.6875rem] font-bold uppercase tracking-[0.14em] text-gold-ink">{book.categories[0].name}</p>}
        <h3 className="mb-3 mt-1.5 line-clamp-2 text-[0.9375rem] font-medium leading-snug text-navy md:text-base">
          <Link href={`/products/${book.slug}`} className="after:absolute after:inset-0 after:content-['']">
            {book.title}
          </Link>
        </h3>
        <div className="mt-auto flex items-center justify-between gap-3 border-t border-navy/15 pt-3">
          <PriceTag price={book.price} />
          <span className="inline-flex items-center gap-1.5 text-[0.6875rem] font-bold uppercase tracking-[0.14em] text-navy transition-colors group-hover:text-gold-ink">
            View
            <ArrowUpRight size={14} strokeWidth={2.25} className="arrow-shift" />
          </span>
        </div>
      </div>
    </article>
  );
}
