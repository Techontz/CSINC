"use client";

import { useRouter } from "next/navigation";
import { useEffect } from "react";
import { useCart } from "./CartProvider";

/** Clears the cart after a successful checkout and polls while payment confirms. */
export function OrderStatusWatcher({ pending, clearCart }: { pending: boolean; clearCart: boolean }) {
  const router = useRouter();
  const { clear, ready, count } = useCart();

  useEffect(() => {
    if (clearCart && ready && count > 0) {
      clear();
    }
  }, [clearCart, ready, count, clear]);

  useEffect(() => {
    if (!pending) {
      return;
    }
    let attempts = 0;
    const timer = setInterval(() => {
      attempts += 1;
      router.refresh();
      if (attempts >= 40) {
        clearInterval(timer);
      }
    }, 3000);
    return () => clearInterval(timer);
  }, [pending, router]);

  return null;
}
