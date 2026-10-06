import type { Book } from "@/lib/types";
import { Reveal } from "@/components/ui/Reveal";
import { BookCard } from "./BookCard";

export function BookGrid({ books, columns = 4 }: { books: Book[]; columns?: 3 | 4 }) {
  return (
    <ul className={columns === 4 ? "grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 sm:gap-x-6 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6" : "grid grid-cols-2 gap-x-4 gap-y-12 sm:gap-x-6 md:grid-cols-3 md:gap-x-8"}>
      {books.map((book, index) => (
        <Reveal as="li" key={book.id} delay={(index % columns) * 80}>
          <BookCard book={book} preload={index < 2} />
        </Reveal>
      ))}
    </ul>
  );
}
