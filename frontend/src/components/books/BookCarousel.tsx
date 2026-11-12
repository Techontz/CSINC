"use client";

import { useCallback, useEffect, useRef, useState, type ReactNode } from "react";
import type { Book } from "@/lib/types";
import { ArrowRight } from "@/components/ui/Icons";
import { BookCard } from "./BookCard";

/**
 * Horizontally scrolling shelf of product cards. The optional `header` sits
 * on the left of a row whose right side holds round previous/next controls.
 */
export function BookCarousel({ books, header }: { books: Book[]; header?: ReactNode }) {
  const track = useRef<HTMLUListElement>(null);
  const [edges, setEdges] = useState({ start: true, end: false });

  const update = useCallback(() => {
    const element = track.current;
    if (!element) {
      return;
    }
    setEdges({
      start: element.scrollLeft <= 4,
      end: element.scrollLeft + element.clientWidth >= element.scrollWidth - 4,
    });
  }, []);

  useEffect(() => {
    update();
    window.addEventListener("resize", update);
    return () => window.removeEventListener("resize", update);
  }, [update]);

  const scroll = (direction: 1 | -1) => {
    track.current?.scrollBy({ left: direction * track.current.clientWidth * 0.8, behavior: "smooth" });
  };

  return (
    <div>
      <div className="flex items-end justify-between gap-6">
        <div className="min-w-0 flex-1">{header}</div>
        <div className="hidden shrink-0 gap-3 sm:flex">
          <button type="button" onClick={() => scroll(-1)} disabled={edges.start} aria-label="Previous products" className="icon-btn">
            <ArrowRight size={18} className="rotate-180" />
          </button>
          <button type="button" onClick={() => scroll(1)} disabled={edges.end} aria-label="More products" className="icon-btn">
            <ArrowRight size={18} />
          </button>
        </div>
      </div>
      <ul
        ref={track}
        onScroll={update}
        className="-mx-[clamp(1.5625rem,3.5vw,3.125rem)] mt-10 flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-px-[clamp(1.5625rem,3.5vw,3.125rem)] px-[clamp(1.5625rem,3.5vw,3.125rem)] pb-4 [scrollbar-width:none] md:gap-6 [&::-webkit-scrollbar]:hidden"
      >
        {books.map((book, index) => (
          <li key={book.id} className="w-[46%] shrink-0 snap-start sm:w-[30%] md:w-[23%] lg:w-[18.5%] xl:w-[15.5%]">
            <BookCard book={book} preload={index < 2} />
          </li>
        ))}
      </ul>
    </div>
  );
}
