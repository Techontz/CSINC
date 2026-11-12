import Link from "next/link";
import type { Book } from "@/lib/types";
import { ArrowRight } from "@/components/ui/Icons";
import { BookCover } from "./BookCover";
import { PriceTag } from "./PriceTag";

/** Product card: 3D product box on a soft-lit stage, bold two-line title, price and arrow. */
export function BookCard({ book }: { book: Book; preload?: boolean }) {
  return (
    <article className="group relative flex h-full flex-col">
      <div className="relative isolate overflow-hidden rounded-2xl bg-[radial-gradient(120%_90%_at_50%_0%,#ffffff_0%,var(--color-sky)_55%,var(--color-sky-deep)_100%)] px-[6%] pb-[2%] pt-[8%] transition-colors duration-500">
        <BookCover title={book.title} category={book.categories[0]?.name} />
      </div>
      <div className="flex flex-1 flex-col pt-4">
        {book.categories[0] && <p className="book-cat text-xs font-medium text-muted">{book.categories[0].name}</p>}
        <h3 className="mb-3 mt-1 line-clamp-2 text-[0.9875rem] font-bold leading-snug tracking-[-0.01em] text-navy md:text-base">
          <Link href={`/products/${book.slug}`} className="after:absolute after:inset-0 after:content-['']">
            {book.title}
          </Link>
        </h3>
        <div className="mt-auto flex items-center justify-between gap-3">
          <PriceTag price={book.price} />
          <span aria-hidden="true" className="flex h-8 w-8 items-center justify-center rounded-full text-gold transition-colors group-hover:bg-gold group-hover:text-white">
            <ArrowRight size={16} strokeWidth={2.25} className="arrow-shift-x" />
          </span>
        </div>
      </div>
    </article>
  );
}
