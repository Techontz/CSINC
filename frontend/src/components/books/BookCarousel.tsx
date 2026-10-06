"use client";

import { useRef } from "react";
import type { Book } from "@/lib/types";
import { ArrowRight } from "@/components/ui/Icons";
import { BookCard } from "./BookCard";

/** Horizontally scrolling shelf of compact product cards with arrow controls. */
export function BookCarousel({ books }: { books: Book[] }) {
  const track = useRef<HTMLUListElement>(null);

  const scroll = (direction: 1 | -1) => {
    const element = track.current;
    if (!element) {
      return;
    }
    element.scrollBy({ left: direction * element.clientWidth * 0.8, behavior: "smooth" });
  };

  return (
    <div>
      <ul
        ref={track}
        className="-mx-[clamp(1.5625rem,3.5vw,3.125rem)] flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-px-[clamp(1.5625rem,3.5vw,3.125rem)] px-[clamp(1.5625rem,3.5vw,3.125rem)] pb-4 [scrollbar-width:none] md:gap-7 [&::-webkit-scrollbar]:hidden"
      >
        {books.map((book, index) => (
          <li key={book.id} className="w-[42%] shrink-0 snap-start sm:w-[28%] md:w-[22%] lg:w-[17%] xl:w-[14.5%]">
            <BookCard book={book} preload={index < 2} />
          </li>
        ))}
      </ul>
      <div className="mt-6 flex gap-3">
        <button type="button" onClick={() => scroll(-1)} aria-label="Previous products" className="flex h-11 w-11 items-center justify-center rounded-full border border-navy/30 text-navy transition-colors hover:bg-navy hover:text-white">
          <ArrowRight size={18} className="rotate-180" />
        </button>
        <button type="button" onClick={() => scroll(1)} aria-label="More products" className="flex h-11 w-11 items-center justify-center rounded-full border border-navy/30 text-navy transition-colors hover:bg-navy hover:text-white">
          <ArrowRight size={18} />
        </button>
      </div>
    </div>
  );
}
