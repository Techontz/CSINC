"use client";

import Link from "next/link";
import { useState } from "react";
import type { Book } from "@/lib/types";
import { buttonClasses } from "@/components/ui/Button";
import { ArrowUpRight, Check } from "@/components/ui/Icons";
import { useCart } from "./CartProvider";

export function AddToCartButton({ book }: { book: Book }) {
  const { add, has, ready } = useCart();
  const [justAdded, setJustAdded] = useState(false);
  const inCart = ready && has(book.slug);

  if (!book.price) {
    return null;
  }

  if (inCart) {
    return (
      <div className="flex flex-col gap-3 sm:flex-row">
        <Link href="/cart" className={buttonClasses("solid", "flex-1")}>
          <span>{justAdded ? "Added — view cart" : "In your cart — checkout"}</span>
          <ArrowUpRight size={15} className="arrow-shift" />
        </Link>
        {justAdded && (
          <p role="status" className="flex items-center gap-2 text-sm text-muted">
            <Check className="text-gold" /> Added to cart
          </p>
        )}
      </div>
    );
  }

  return (
    <button
      type="button"
      disabled={!ready}
      className={buttonClasses("solid", "w-full sm:w-auto")}
      onClick={() => {
        add({
          slug: book.slug,
          title: book.title,
          price_cents: book.price!.amount_cents,
          currency: book.price!.currency,
          formatted_price: book.price!.formatted,
          cover: book.cover?.url ?? null,
        });
        setJustAdded(true);
      }}
    >
      <span>Add to cart — {book.price.formatted}</span>
    </button>
  );
}
